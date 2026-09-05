<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Notifications\ReminderService;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    public function __construct(
        protected ReminderService $reminders
    ) {
        $this->middleware('permission:View financial reports');
        // Or a dedicated permission: Send payment reminders
    }

    public function sendReminders(Request $request)
    {
        $data = $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'integer',
            'term_id'       => 'nullable|integer',
            'session_id'    => 'nullable|integer',
            'channels'      => 'required|array|min:1',
            'channels.*'    => 'in:email,sms,whatsapp',
        ]);

        try {
            $result = $this->reminders->sendFeeReminders(
                $data['student_ids'],
                $data['channels'],
                isset($data['term_id']) ? (int) $data['term_id'] : null,
                isset($data['session_id']) ? (int) $data['session_id'] : null
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'summary' => $result['summary'],
            ]);
        } catch (\Throwable $e) {
            \Log::error('Send reminders failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage(),
            ], 500);
        }
    }
}