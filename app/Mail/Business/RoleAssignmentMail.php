<?php

namespace App\Mail\Business;

use App\Models\Business;
use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RoleAssignmentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $business;
    public $role;
    public $password;

    public function __construct(User $user, Business $business, Role $role, $password = null)
    {
        $this->user = $user;
        $this->business = $business;
        $this->role = $role;
        $this->password = $password;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been added to " . $this->business->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.business.role_assignment',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
