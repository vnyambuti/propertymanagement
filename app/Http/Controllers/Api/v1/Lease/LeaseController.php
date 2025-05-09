<?php

namespace App\Http\Controllers\Api\v1\Lease;

use App\Http\Controllers\Controller;
use App\Services\LeaseService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Leases",
 *     description="API Endpoints for lease management"
 * )
 */
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
     * @OA\Get(
     *     path="/api/v1/leases",
     *     summary="Get all leases",
     *     description="Returns a paginated list of all leases",
     *     operationId="getLeases",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             properties={
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Lease")
     *                 ),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $leases = $this->leaseService->getAllLeases($perPage);

        return response()->json($leases);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/leases/{id}",
     *     summary="Get lease by ID",
     *     description="Returns a specific lease by ID",
     *     operationId="getLeaseById",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Lease")
     *     ),
     *     @OA\Response(response=404, description="Lease not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/leases",
     *     summary="Create a new lease",
     *     description="Creates a new lease record",
     *     operationId="createLease",
     *     tags={"Leases"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unit_id", "tenant_id", "start_date", "end_date", "rent_amount", "security_deposit", "status"},
     *             @OA\Property(property="unit_id", type="integer", example=1),
     *             @OA\Property(property="tenant_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="rent_amount", type="number", format="float", example=1500.00),
     *             @OA\Property(property="security_deposit", type="number", format="float", example=3000.00),
     *             @OA\Property(property="status", type="string", enum={"active", "pending", "terminated", "expired"}, example="active"),
     *             @OA\Property(property="notes", type="string", example="Monthly lease with option to renew", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lease created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Lease")
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Put(
     *     path="/api/v1/leases/{id}",
     *     summary="Update a lease",
     *     description="Updates an existing lease record",
     *     operationId="updateLease",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="unit_id", type="integer", example=1),
     *             @OA\Property(property="tenant_id", type="integer", example=1),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-01-01"),
     *             @OA\Property(property="rent_amount", type="number", format="float", example=1500.00),
     *             @OA\Property(property="security_deposit", type="number", format="float", example=3000.00),
     *             @OA\Property(property="status", type="string", enum={"active", "pending", "terminated", "expired"}, example="active"),
     *             @OA\Property(property="notes", type="string", example="Monthly lease with option to renew", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lease updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Lease")
     *     ),
     *     @OA\Response(response=404, description="Lease not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Delete(
     *     path="/api/v1/leases/{id}",
     *     summary="Delete a lease",
     *     description="Deletes an existing lease record",
     *     operationId="deleteLease",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lease deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Lease deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Lease not found"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/units/{unitId}/leases",
     *     summary="Get leases by unit ID",
     *     description="Returns a paginated list of leases for a specific unit",
     *     operationId="getLeasesByUnit",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="unitId",
     *         in="path",
     *         description="Unit ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lease")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/tenants/{tenantId}/leases",
     *     summary="Get leases by tenant ID",
     *     description="Returns a paginated list of leases for a specific tenant",
     *     operationId="getLeasesByTenant",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="tenantId",
     *         in="path",
     *         description="Tenant ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lease")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/leases/active",
     *     summary="Get active leases",
     *     description="Returns a paginated list of active leases",
     *     operationId="getActiveLeases",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lease")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Get(
     *     path="/api/v1/leases/expiring",
     *     summary="Get expiring leases",
     *     description="Returns a paginated list of leases expiring within the specified number of days",
     *     operationId="getExpiringLeases",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         description="Number of days threshold for expiration",
     *         required=false,
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Lease")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/leases/{id}/terminate",
     *     summary="Terminate a lease",
     *     description="Terminates an active lease",
     *     operationId="terminateLease",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"termination_reason"},
     *             @OA\Property(property="termination_reason", type="string", example="Tenant moving out of state")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lease terminated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Lease")
     *     ),
     *     @OA\Response(response=404, description="Lease not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
     * @OA\Post(
     *     path="/api/v1/leases/{id}/renew",
     *     summary="Renew a lease",
     *     description="Renews an existing lease with a new end date and optional new rent amount",
     *     operationId="renewLease",
     *     tags={"Leases"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lease ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"new_end_date"},
     *             @OA\Property(property="new_end_date", type="string", format="date", example="2027-01-01"),
     *             @OA\Property(property="new_rent_amount", type="number", format="float", example=1600.00, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lease renewed successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Lease")
     *     ),
     *     @OA\Response(response=404, description="Lease not found"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
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
