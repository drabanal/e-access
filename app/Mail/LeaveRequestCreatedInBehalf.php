<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestCreatedInBehalf extends Mailable
{
    use Queueable, SerializesModels;

    protected $supervisor = null;

    /**
     * Create a new message instance.
     */
    public function __construct(public LeaveRequest $leaveRequest, public User $user)
    {
        $this->supervisor = $this->user->employee;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[E-Access] New Leave Request Notification - ' . Carbon::parse($this->leaveRequest->date_time_from)->format('m/d/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.leave-request-created-in-behalf',
            with: [
                'supervisor' => $this->supervisor,
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
