<?php

namespace App\Services;

use App\Jobs\SendServiceCompletedNotification;
use App\Models\MaintenanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    /**
     * Mark a maintenance log as completed and automatically schedule
     * the next routine service 6 months in the future.
     *
     * @param MaintenanceLog $log
     * @return array{completed: MaintenanceLog, scheduled: MaintenanceLog}
     * @throws \Exception
     */
    public function completeService(MaintenanceLog $log): array
    {
        if ($log->status === MaintenanceLog::STATUS_COMPLETED) {
            throw new \Exception("Servis ini sudah berstatus 'completed'.");
        }

        return DB::transaction(function () use ($log) {
            // 1. Mark current log as completed
            $log->update(['status' => MaintenanceLog::STATUS_COMPLETED]);
            $log->refresh();

            // 2. Auto-schedule next routine service 6 months ahead
            $nextServiceDate = Carbon::parse($log->tanggal_servis)->addMonths(6);

            $scheduledLog = MaintenanceLog::create([
                'ship_id'        => $log->ship_id,
                'tanggal_servis' => $nextServiceDate->format('Y-m-d'),
                'jenis_servis'   => $log->jenis_servis . ' (Rutin)',
                'biaya'          => $log->getRawOriginal('biaya'), // estimated cost based on previous
                'status'         => MaintenanceLog::STATUS_PLANNED,
            ]);

            // 3. Dispatch notification job to queue
            SendServiceCompletedNotification::dispatch($log, $scheduledLog);

            return [
                'completed'  => $log,
                'scheduled'  => $scheduledLog,
            ];
        });
    }
}
