<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestCreatedNotification extends Notification
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
        $this->leaveRequest->loadMissing(['user:id,name', 'leaveType:id,name']);

        return [
            'kind' => 'leave_request_created',
            'leave_request' => [
                'id' => $this->leaveRequest->id,
                'status' => $this->leaveRequest->status,
                'applied_at' => $this->leaveRequest->applied_at,
                'start_date' => $this->leaveRequest->start_date,
                'end_date' => $this->leaveRequest->end_date,
                'duration' => $this->leaveRequest->duration,
                'user_id' => $this->leaveRequest->user_id,
                'user_name' => $this->leaveRequest->user?->name,
                'leave_type_id' => $this->leaveRequest->leave_type_id,
                'leave_type_name' => $this->leaveRequest->leaveType?->name,
                'school_id' => $this->leaveRequest->school_id,
            ],
        ];
    }
}
