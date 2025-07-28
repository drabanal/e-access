<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\LeaveStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;

    /**
     * Create a new message instance.
     */
    public function __construct(public LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->leave_status_id == LeaveStatus::TL_APPROVED) {
            $this->subject = '[E-Access] Pending Request Update - ' . $leaveRequest->leaveType->name . ' (' . Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') . ')';
        } elseif ($leaveRequest->leave_status_id == LeaveStatus::ADMIN_APPROVED) {
            $this->subject = '[E-Access] Approved Request Notification - ' . $leaveRequest->leaveType->name . ' (' . Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') . ')';
        } elseif ($leaveRequest->leave_status_id == LeaveStatus::DISAPPROVED) {
            $this->subject = '[E-Access] Disapproved Request Notification - ' . $leaveRequest->leaveType->name . ' (' . Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') . ')';
        } else {
            $this->subject = '[E-Access] Cancelled Request Notification - ' . $leaveRequest->leaveType->name . ' (' . Carbon::parse($leaveRequest->date_time_from)->format('m/d/Y') . ')';
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.leave-request-status-updated',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'leaveType' => $this->leaveRequest->leaveType,
                'latestLeaveStatusLog' => $this->leaveRequest->latestLeaveStatusLog
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
