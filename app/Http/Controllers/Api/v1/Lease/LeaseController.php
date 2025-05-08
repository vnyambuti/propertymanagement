<?php

namespace App\Http\Controllers\Api\v1\Lease;

use App\Http\Controllers\Controller;
use App\Services\LeaseService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaseController extends Controller
{
    protected $leaseService;

    /**
     * Create a new controller instance.
     *
     * @param LeaseService $leaseService
     */
    public function __construct(LeaseService $leaseService)
    {
        $this->leaseService = $leaseService;
        // $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of leases.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getAllLeases($perPage);

        return response()->json($leases);
    }

    /**
     * Display the specified lease.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $lease = $this->leaseService->getLeaseById($id);

        if (!$lease) {
            return response()->json(['message' => 'Lease not found'], 404);
        }

        return response()->json($lease);
    }

    /**
     * Store a newly created lease.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'tenant_id' => 'required|exists:tenants,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,pending,terminated,expired',
            'notes' => 'nullable|string',
        ]);

        try {
            $lease = $this->leaseService->createLease($validated);
            return response()->json($lease, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update the specified lease.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'unit_id' => 'sometimes|required|exists:units,id',
            'tenant_id' => 'sometimes|required|exists:tenants,id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'rent_amount' => 'sometimes|required|numeric|min:0',
            'security_deposit' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:active,pending,terminated,expired',
            'notes' => 'nullable|string',
        ]);

        $lease = $this->leaseService->updateLease($id, $validated);

        if (!$lease) {
            return response()->json(['message' => 'Lease not found'], 404);
        }

        return response()->json($lease);
    }

    /**
     * Remove the specified lease.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deleted = $this->leaseService->deleteLease($id);

        if (!$deleted) {
            return response()->json(['message' => 'Lease not found'], 404);
        }

        return response()->json(['message' => 'Lease deleted successfully']);
    }

    /**
     * Get leases by unit ID.
     *
     * @param  Request  $request
     * @param  int  $unitId
     * @return \Illuminate\Http\Response
     */
    public function getLeasesByUnit(Request $request, $unitId)
    {
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getLeasesByUnit($unitId, $perPage);

        return response()->json($leases);
    }

    /**
     * Get leases by tenant ID.
     *
     * @param  Request  $request
     * @param  int  $tenantId
     * @return \Illuminate\Http\Response
     */
    public function getLeasesByTenant(Request $request, $tenantId)
    {
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getLeasesByTenant($tenantId, $perPage);

        return response()->json($leases);
    }

    /**
     * Get active leases.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getActiveLeases(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getActiveLeases($perPage);

        return response()->json($leases);
    }

    /**
     * Get expiring leases.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getExpiringLeases(Request $request)
    {
        $daysThreshold = $request->input('days', 30);
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getExpiringLeases($daysThreshold, $perPage);

        return response()->json($leases);
    }

    /**
     * Terminate a lease.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function terminateLease(Request $request, $id)
    {
        $validated = $request->validate([
            'termination_reason' => 'required|string',
        ]);

        $lease = $this->leaseService->terminateLease($id, $validated['termination_reason']);

        if (!$lease) {
            return response()->json(['message' => 'Lease not found'], 404);
        }

        return response()->json($lease);
    }

    /**
     * Renew a lease.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function renewLease(Request $request, $id)
    {
        $validated = $request->validate([
            'new_end_date' => 'required|date',
            'new_rent_amount' => 'nullable|numeric|min:0',
        ]);

        $lease = $this->leaseService->renewLease(
            $id,
            $validated['new_end_date'],
            $validated['new_rent_amount'] ?? null
        );

        if (!$lease) {
            return response()->json(['message' => 'Lease not found'], 404);
        }

        return response()->json($lease);
    }
    }

