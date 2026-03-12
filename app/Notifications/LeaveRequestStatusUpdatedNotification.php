<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LeaveRequest $leaveRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'leave_request_status_updated',
            'leave_request' => [
                'id' => $this->leaveRequest->id,
                'status' => $this->leaveRequest->status,
                'rejection_note' => $this->leaveRequest->rejection_note,
                'reviewed_at' => $this->leaveRequest->reviewed_at,
                'reviewed_by' => $this->leaveRequest->reviewed_by,
            ],
        ];
    }
}

