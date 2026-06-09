<?php

namespace App\Jobs;

use App\Mail\ServiceCompletedMail;
use App\Models\MaintenanceLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendServiceCompletedNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly MaintenanceLog $completedLog,
        public readonly MaintenanceLog $scheduledLog,
    ) {}

    /**
     * Execute the job.
     * Sends an email notification to the operations manager when
     * a maintenance service status changes to 'completed'.
     */
    public function handle(): void
    {
        $managerEmail = config('mail.operations_manager_email', 'manager@shipmaintenance.com');

        // Send email notification
        Mail::to($managerEmail)
            ->send(new ServiceCompletedMail($this->completedLog, $this->scheduledLog));

        Log::info('Service completion notification sent.', [
            'ship_id'           => $this->completedLog->ship_id,
            'ship_name'         => $this->completedLog->ship->nama ?? 'Unknown',
            'completed_log_id'  => $this->completedLog->id,
            'scheduled_log_id'  => $this->scheduledLog->id,
            'next_service_date' => $this->scheduledLog->tanggal_servis,
            'manager_email'     => $managerEmail,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send service completion notification.', [
            'log_id' => $this->completedLog->id,
            'error'  => $exception->getMessage(),
        ]);
    }
}
