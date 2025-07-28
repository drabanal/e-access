<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $employeeName = null;
    protected $employee = null;
    /**
     * Create a new message instance.
     */
    public function __construct(public LeaveRequest $leaveRequest)
    {
        $this->employee = $this->leaveRequest->user->employee;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[E-Access] New Leave Request Notification - ' . $this->employee->getFullNameAttribute() . ' (' . Carbon::parse($this->leaveRequest->date_time_from)->format('m/d/Y') . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.leave-request-created',
            with: [
                'employeeName' => $this->employee->getFullNameAttribute(),
                'leaveRequest' => $this->leaveRequest,
                'leaveType' => $this->leaveRequest->leaveType
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
