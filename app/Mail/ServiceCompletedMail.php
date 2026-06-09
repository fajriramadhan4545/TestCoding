<?php

namespace App\Mail;

use App\Models\MaintenanceLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public readonly MaintenanceLog $completedLog,
        public readonly MaintenanceLog $scheduledLog,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $shipName = $this->completedLog->ship->nama ?? 'Unknown Ship';

        return new Envelope(
            subject: "[NOTIFIKASI] Servis Selesai - {$shipName} | Jadwal Servis Berikutnya Telah Dibuat",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.service-completed',
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
