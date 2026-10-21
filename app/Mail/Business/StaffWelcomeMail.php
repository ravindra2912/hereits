<?php

namespace App\Mail\Business;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $business;
    public $role;
    public $tempPassword;
    public $loginUrl;
    public $permissionsList;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $business, $role, $tempPassword = null, $loginUrl = null, $permissionsList = [])
    {
        $this->user = $user;
        $this->business = $business;
        $this->role = $role;
        $this->tempPassword = $tempPassword;
        $this->loginUrl = $loginUrl;
        $this->permissionsList = $permissionsList;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to join ' . $this->business->name . ' as ' . $this->role->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.business.staff_welcome',
            with: [
                'user' => $this->user,
                'business' => $this->business,
                'role' => $this->role,
                'tempPassword' => $this->tempPassword,
                'loginUrl' => $this->loginUrl,
                'permissionsList' => $this->permissionsList,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
