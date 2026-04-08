<?php
// app/Http/Controllers/RoomController.php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\TimetableSlot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function __construct()
    {
        $this->middleware('permission:View rooms', ['only' => ['index', 'show', 'checkAvailability']]);
        $this->middleware('permission:Create rooms', ['only' => ['store']]);
        $this->middleware('permission:Edit rooms', ['only' => ['update']]);
        $this->middleware('permission:Delete rooms', ['only' => ['destroy']]);
        $this->middleware('permission:Manage room bookings', ['only' => ['book', 'cancelBooking']]);
    }

    public function index()
    {
        $pagetitle = 'Room Management';
        $rooms = Room::orderBy('room_name')->paginate(15);
        $roomTypes = ['classroom', 'laboratory', 'auditorium', 'library', 'sports', 'other'];

        return view('rooms.index', compact('pagetitle', 'rooms', 'roomTypes'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_code' => 'required|string|max:50|unique:rooms',
            'room_name' => 'required|string|max:100',
            'type' => 'required|in:classroom,laboratory,auditorium,library,sports,other',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|array',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        try {
            $room = Room::create($validated);
            return response()->json(['success' => true, 'room' => $room, 'message' => 'Room created successfully']);
        } catch (\Exception $e) {
            Log::error('Room creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create room'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $room = Room::with(['bookings' => function($q) {
            $q->where('date', '>=', now())->orderBy('date');
        }])->findOrFail($id);

        // Get current week's timetable for this room
        $currentWeekSlots = TimetableSlot::where('room_id', $id)
            ->with(['period', 'subject', 'teacher', 'setting.schoolclass'])
            ->get();

        return response()->json([
            'success' => true,
            'room' => $room,
            'current_bookings' => $currentWeekSlots
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'room_code' => 'required|string|max:50|unique:rooms,room_code,' . $id,
            'room_name' => 'required|string|max:100',
            'type' => 'required|in:classroom,laboratory,auditorium,library,sports,other',
            'capacity' => 'required|integer|min:1',
            'facilities' => 'nullable|array',
            'building' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $room->update($validated);
            return response()->json(['success' => true, 'room' => $room, 'message' => 'Room updated successfully']);
        } catch (\Exception $e) {
            Log::error('Room update failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update room'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $room = Room::findOrFail($id);

        // Check if room is being used
        $isUsed = TimetableSlot::where('room_id', $id)->exists();
        if ($isUsed) {
            return response()->json(['success' => false, 'message' => 'Cannot delete room that is currently in use'], 422);
        }

        $room->delete();
        return response()->json(['success' => true, 'message' => 'Room deleted successfully']);
    }

    public function book(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:500',
            'recurring_type' => 'in:none,weekly,biweekly',
        ]);

        // Check availability
        $isAvailable = $this->checkRoomAvailability(
            $validated['room_id'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time']
        );

        if (!$isAvailable) {
            return response()->json(['success' => false, 'message' => 'Room is not available at this time'], 422);
        }

        $booking = RoomBooking::create([
            'room_id' => $validated['room_id'],
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'purpose' => $validated['purpose'],
            'recurring_type' => $validated['recurring_type'] ?? 'none',
            'booked_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'booking' => $booking, 'message' => 'Room booked successfully']);
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $isAvailable = $this->checkRoomAvailability(
            $validated['room_id'],
            $validated['date'],
            $validated['start_time'],
            $validated['end_time']
        );

        return response()->json(['success' => true, 'available' => $isAvailable]);
    }

    private function checkRoomAvailability($roomId, $date, $startTime, $endTime): bool
    {
        $dayOfWeek = date('l', strtotime($date));

        // Check timetable slots
        $timetableConflict = TimetableSlot::where('room_id', $roomId)
            ->where('day', $dayOfWeek)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->exists();

        if ($timetableConflict) return false;

        // Check one-off bookings
        $bookingConflict = RoomBooking::where('room_id', $roomId)
            ->where('date', $date)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime]);
            })
            ->exists();

        return !$bookingConflict;
    }
}
