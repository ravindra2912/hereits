<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PendingPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $transaction;
    public $business;
    public $purchase;

    /**
     * Create a new message instance.
     */
    public function __construct($transaction, $business, $purchase)
    {
        $this->transaction = $transaction;
        $this->business = $business;
        $this->purchase = $purchase;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pending Manual UPI Payment Verification Request ⏳',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.pending_payment',
            with: [
                'transaction' => $this->transaction,
                'business' => $this->business,
                'purchase' => $this->purchase,
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
