<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterMaintenanceLogRequest;
use App\Models\MaintenanceLog;
use App\Services\MaintenanceService;
use Illuminate\Http\JsonResponse;

class MaintenanceLogController extends Controller
{
    public function __construct(
        private readonly MaintenanceService $maintenanceService
    ) {}

    /**
     * Display a listing of maintenance logs.
     * Supports filters:
     * - ship_id: filter by ship
     * - status: filter by status
     * - date_from / date_to: date range filter
     * - per_page: pagination size
     */
    public function index(FilterMaintenanceLogRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $query = MaintenanceLog::query()->with('ship');

        if (isset($validated['ship_id'])) {
            $query->where('ship_id', $validated['ship_id']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['date_from'])) {
            $query->where('tanggal_servis', '>=', $validated['date_from']);
        }

        if (isset($validated['date_to'])) {
            $query->where('tanggal_servis', '<=', $validated['date_to']);
        }

        $perPage = $request->integer('per_page', 15);

        $logs = $query->orderByDesc('tanggal_servis')->paginate($perPage);

        $logsArray = $logs->toArray();
        unset($logsArray['links']);

        return response()->json([
            'success' => true,
            'message' => 'Data log servis berhasil diambil.',
            'data'    => $logsArray,
        ]);
    }

    /**
     * Display the specified maintenance log.
     */
    public function show(MaintenanceLog $maintenanceLog): JsonResponse
    {
        $maintenanceLog->load('ship');

        return response()->json([
            'success' => true,
            'message' => 'Detail log servis berhasil diambil.',
            'data'    => $maintenanceLog,
        ]);
    }

    /**
     * Mark a maintenance log as 'completed' and automatically schedule
     * the next routine service 6 months later.
     * Also dispatches a notification job to the queue.
     *
     * @param MaintenanceLog $maintenanceLog
     * @return JsonResponse
     */
    public function complete(MaintenanceLog $maintenanceLog): JsonResponse
    {
        try {
            $result = $this->maintenanceService->completeService($maintenanceLog);

            return response()->json([
                'success' => true,
                'message' => 'Servis berhasil ditandai sebagai selesai. Servis rutin berikutnya telah dijadwalkan.',
                'data'    => [
                    'completed_log'   => $result['completed']->load('ship'),
                    'scheduled_log'   => $result['scheduled']->load('ship'),
                    'next_service_at' => $result['scheduled']->tanggal_servis,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
