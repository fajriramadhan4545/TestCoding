<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FilterShipRequest;
use App\Models\Ship;
use Illuminate\Http\JsonResponse;

class ShipController extends Controller
{
    /**
     * Display a listing of ships with last service info and total service cost.
     * Supports filtering by:
     * - min_biaya: minimum total service cost
     * - max_biaya: maximum total service cost
     * - status: maintenance log status (planned, ongoing, completed)
     * - search: search by ship name or ship code
     * - per_page: items per page (default: 15)
     */
    public function index(FilterShipRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $query = Ship::query()
            ->withSum('maintenanceLogs as total_biaya_servis', 'biaya')
            // Ambil data servis terakhir dengan subquery — efisien, 1 query
            ->withMax('maintenanceLogs as last_service_id', 'id')
            ->withMax('maintenanceLogs as last_service_date', 'tanggal_servis')
            ->withAggregate('maintenanceLogs as last_service_jenis', 'jenis_servis', 'max')
            ->withAggregate('maintenanceLogs as last_service_biaya', 'biaya', 'max')
            ->withAggregate('maintenanceLogs as last_service_status', 'status', 'max');

        // Filter by minimum total biaya servis
        if (isset($validated['min_biaya'])) {
            $query->where(function ($q) use ($validated) {
                $q->selectRaw('COALESCE(SUM(biaya), 0)')
                    ->from('maintenance_logs')
                    ->whereColumn('maintenance_logs.ship_id', 'ships.id');
            }, '>=', (int) $validated['min_biaya']);
        }

        // Filter by maximum total biaya servis
        if (isset($validated['max_biaya'])) {
            $query->where(function ($q) use ($validated) {
                $q->selectRaw('COALESCE(SUM(biaya), 0)')
                    ->from('maintenance_logs')
                    ->whereColumn('maintenance_logs.ship_id', 'ships.id');
            }, '<=', (int) $validated['max_biaya']);
        }

        // Filter by status of the latest maintenance log
        if (isset($validated['status'])) {
            $status = $validated['status'];
            $query->whereHas('maintenanceLogs', function ($q) use ($status) {
                $q->where('status', $status)
                  ->whereRaw('id = (select max(id) from maintenance_logs as ml where ml.ship_id = maintenance_logs.ship_id)');
            });
        }

        // Search by name or kode_kapal
        if (isset($validated['search'])) {
            $search = $validated['search'];
            $query->where(fn($q) => $q->where('nama', 'like', "%{$search}%")
                                      ->orWhere('kode_kapal', 'like', "%{$search}%"));
        }

        $perPage = $request->integer('per_page', 15);
        $ships   = $query->orderBy('nama')->paginate($perPage);

        // Ambil detail servis terakhir yang akurat per ship via 1 query tambahan
        $lastLogIds = $ships->getCollection()
            ->pluck('last_service_id')
            ->filter()
            ->unique()
            ->values();

        $lastLogs = \App\Models\MaintenanceLog::whereIn('id', $lastLogIds)
            ->get()
            ->keyBy('id');

        $ships->getCollection()->transform(function ($ship) use ($lastLogs) {
            $lastLog = $lastLogs->get($ship->last_service_id);
            return [
                'id'                 => $ship->id,
                'nama'               => $ship->nama,
                'kode_kapal'         => $ship->kode_kapal,
                'tahun_pembuatan'    => $ship->tahun_pembuatan,
                'total_biaya_servis' => $ship->total_biaya_servis,
                'servis_terakhir'    => $lastLog ? [
                    'id'             => $lastLog->id,
                    'tanggal_servis' => $lastLog->tanggal_servis->format('Y-m-d'),
                    'jenis_servis'   => $lastLog->jenis_servis,
                    'biaya'          => $lastLog->biaya,
                    'status'         => $lastLog->status,
                ] : null,
                'created_at'         => $ship->created_at,
                'updated_at'         => $ship->updated_at,
            ];
        });

        $shipsArray = $ships->toArray();
        unset($shipsArray['links']);

        return response()->json([
            'success' => true,
            'message' => 'Data kapal berhasil diambil.',
            'data'    => $shipsArray,
        ]);
    }

    /**
     * Display the specified ship with its maintenance logs summary.
     */
    public function show(Ship $ship): JsonResponse
    {
        $ship->load('maintenanceLogs');
        $ship->loadSum('maintenanceLogs as total_biaya_servis', 'biaya');

        $lastLog = $ship->maintenanceLogs->sortByDesc('tanggal_servis')->first();

        return response()->json([
            'success' => true,
            'message' => 'Detail kapal berhasil diambil.',
            'data'    => [
                'id'                  => $ship->id,
                'nama'                => $ship->nama,
                'kode_kapal'          => $ship->kode_kapal,
                'tahun_pembuatan'     => $ship->tahun_pembuatan,
                'total_biaya_servis'  => $ship->total_biaya_servis,
                'jumlah_servis'       => $ship->maintenanceLogs->count(),
                'servis_terakhir'     => $lastLog ? [
                    'id'             => $lastLog->id,
                    'tanggal_servis' => $lastLog->tanggal_servis->format('Y-m-d'),
                    'jenis_servis'   => $lastLog->jenis_servis,
                    'biaya'          => $lastLog->biaya,
                    'status'         => $lastLog->status,
                ] : null,
                'statistik_status'    => [
                    'planned'   => $ship->maintenanceLogs->where('status', 'planned')->count(),
                    'ongoing'   => $ship->maintenanceLogs->where('status', 'ongoing')->count(),
                    'completed' => $ship->maintenanceLogs->where('status', 'completed')->count(),
                ],
                'created_at'          => $ship->created_at,
                'updated_at'          => $ship->updated_at,
            ],
        ]);
    }
}
