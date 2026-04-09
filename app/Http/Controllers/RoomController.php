<?php
// app/Http/Controllers/RoomController.php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\TimetableSlot;
use App\Models\TimetablePeriod;
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
        // Uncomment these when you have permissions set up
        // $this->middleware('permission:View rooms', ['only' => ['index', 'show', 'checkAvailability']]);
        // $this->middleware('permission:Create rooms', ['only' => ['store']]);
        // $this->middleware('permission:Edit rooms', ['only' => ['update']]);
        // $this->middleware('permission:Delete rooms', ['only' => ['destroy']]);
        // $this->middleware('permission:Manage room bookings', ['only' => ['book', 'cancelBooking']]);
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
        try {
            $validated = $request->validate([
                'room_code' => 'required|string|max:50|unique:rooms,room_code',
                'room_name' => 'required|string|max:100',
                'type' => 'required|in:classroom,laboratory,auditorium,library,sports,other',
                'capacity' => 'required|integer|min:1',
                'facilities' => 'nullable|array',
                'building' => 'nullable|string|max:100',
                'floor' => 'nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
                'notes' => 'nullable|string',
            ]);

            // Set default for is_active if not provided
            $validated['is_active'] = $request->input('is_active', true);

            // Convert facilities array to JSON if needed
            if (isset($validated['facilities']) && is_array($validated['facilities'])) {
                $validated['facilities'] = json_encode($validated['facilities']);
            }

            $room = Room::create($validated);

            // Return in the format frontend expects
            return response()->json([
                'success' => true,
                'room' => $room,
                'message' => 'Room created successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Room creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            // Get current week's timetable slots
            $currentWeekSlots = TimetableSlot::where('room_id', $id)
                ->with(['period', 'subject', 'teacher'])
                ->get()
                ->map(function($slot) {
                    return [
                        'day' => $slot->day,
                        'period' => $slot->period ? [
                            'name' => $slot->period->name,
                            'start_time' => $slot->period->start_time,
                            'end_time' => $slot->period->end_time
                        ] : null,
                        'subject' => $slot->subject ? ['subject' => $slot->subject->subject_name] : null,
                        'teacher' => $slot->teacher ? ['name' => $slot->teacher->name] : null,
                    ];
                });

            // Decode facilities if it's JSON
            if (is_string($room->facilities)) {
                $room->facilities = json_decode($room->facilities, true);
            }

            return response()->json([
                'success' => true,
                'room' => $room,
                'current_bookings' => $currentWeekSlots
            ]);
        } catch (\Exception $e) {
            Log::error('Room show failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            $validated = $request->validate([
                'room_code' => 'required|string|max:50|unique:rooms,room_code,' . $id,
                'room_name' => 'required|string|max:100',
                'type' => 'required|in:classroom,laboratory,auditorium,library,sports,other',
                'capacity' => 'required|integer|min:1',
                'facilities' => 'nullable|array',
                'building' => 'nullable|string|max:100',
                'floor' => 'nullable|string|max:50',
                'is_active' => 'sometimes|boolean',
                'notes' => 'nullable|string',
            ]);

            // Convert facilities array to JSON if needed
            if (isset($validated['facilities']) && is_array($validated['facilities'])) {
                $validated['facilities'] = json_encode($validated['facilities']);
            }

            $room->update($validated);

            return response()->json([
                'success' => true,
                'room' => $room,
                'message' => 'Room updated successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Room update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $room = Room::findOrFail($id);

            // Check if room is used in timetable
            $isUsed = TimetableSlot::where('room_id', $id)->exists();
            if ($isUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room that is currently in use in a timetable'
                ], 422);
            }

            // Check for future bookings
            $hasFutureBookings = RoomBooking::where('room_id', $id)
                ->where('date', '>=', now()->toDateString())
                ->exists();

            if ($hasFutureBookings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room with upcoming bookings'
                ], 422);
            }

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Room deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function book(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'purpose' => 'required|string|max:500',
                'recurring_type' => 'sometimes|in:none,weekly,biweekly',
            ]);

            $isAvailable = $this->checkRoomAvailability(
                $validated['room_id'],
                $validated['date'],
                $validated['start_time'],
                $validated['end_time']
            );

            if (!$isAvailable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room is not available at this time'
                ], 422);
            }

            $booking = RoomBooking::create([
                'room_id' => $validated['room_id'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'purpose' => $validated['purpose'],
                'recurring_type' => $validated['recurring_type'] ?? 'none',
                'booked_by' => Auth::id(),
                'status' => 'confirmed'
            ]);

            return response()->json([
                'success' => true,
                'booking' => $booking,
                'message' => 'Room booked successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Booking failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to book room: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelBooking($bookingId): JsonResponse
    {
        try {
            $booking = RoomBooking::findOrFail($bookingId);

            if ($booking->booked_by !== Auth::id() && !Auth::user()->can('Manage room bookings')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($booking->date < now()->toDateString()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a past booking'
                ], 422);
            }

            $booking->delete();

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Booking cancellation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel booking'
            ], 500);
        }
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        try {
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

            return response()->json([
                'success' => true,
                'available' => $isAvailable
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Failed to check availability'
            ], 500);
        }
    }

    private function checkRoomAvailability($roomId, $date, $startTime, $endTime): bool
    {
        $dayOfWeek = date('l', strtotime($date));

        // Check against recurring timetable slots
        $timetableConflict = TimetableSlot::where('room_id', $roomId)
            ->where('day', $dayOfWeek)
            ->whereHas('period', function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($timetableConflict) return false;

        // Check against one-off bookings
        $bookingConflict = RoomBooking::where('room_id', $roomId)
            ->where('date', $date)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();

        return !$bookingConflict;
    }
}
