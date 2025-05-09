<?php

namespace App\Http\Controllers\Api\v1\Tenant;

use App\Domain\Property\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Tag(
 *     name="Tenant",
 *     description="API Endpoints for Tenant management"
 * )
 *
 * @OA\Get(
 *     path="/tenant",
 *     summary="Get a list of tenants",
 *     description="Returns a paginated list of all tenants",
 *     operationId="getTenantsList",
 *     tags={"Tenant"},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         description="Page number",
 *         required=false,
 *         @OA\Schema(type="integer", default=1)
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
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(
 *                     property="data",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/Tenant")
 *                 ),
 *                 @OA\Property(property="total", type="integer", example=30),
 *                 @OA\Property(property="per_page", type="integer", example=15)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 *
 * @OA\Post(
 *     path="/tenant",
 *     summary="Create a new tenant",
 *     description="Stores a new tenant and returns the tenant data",
 *     operationId="storeTenant",
 *     tags={"Tenant"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Tenant data",
 *         @OA\JsonContent(ref="#/components/schemas/TenantRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Tenant created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Tenant")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 *
 * @OA\Get(
 *     path="/tenant/{id}",
 *     summary="Get tenant details",
 *     description="Returns details for a specific tenant",
 *     operationId="getTenant",
 *     tags={"Tenant"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Tenant ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Tenant")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tenant not found",
 *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 *
 * @OA\Put(
 *     path="/tenant/{id}",
 *     summary="Update a tenant",
 *     description="Updates an existing tenant and returns the updated tenant data",
 *     operationId="updateTenant",
 *     tags={"Tenant"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Tenant ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Tenant data",
 *         @OA\JsonContent(ref="#/components/schemas/TenantRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tenant updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", ref="#/components/schemas/Tenant")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tenant not found",
 *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/tenant/{id}",
 *     summary="Delete a tenant",
 *     description="Deletes a tenant and returns a success message",
 *     operationId="deleteTenant",
 *     tags={"Tenant"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Tenant ID",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tenant deleted successfully",
 *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tenant not found",
 *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 */

class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of the tenants.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->get('per_page', 15);
        $cacheKey = 'tenants_page_' . $page . '_' . $perPage;

        // Add the key to our list of tenant cache keys
        $this->trackCacheKey($cacheKey);

        // Cache the results for 10 minutes
        return Cache::remember($cacheKey, 600, function () use ($perPage) {
            $tenants = $this->tenantService->getAllTenants($perPage);

            return response()->json([
                'success' => true,
                'data' => $tenants
            ]);
        });
    }

    /**
     * Store a newly created tenant in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'ssn' => 'nullable|string|max:11',
            'employment_status' => 'nullable|string|max:50',
            'annual_income' => 'nullable|numeric',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tenant = $this->tenantService->createTenant($request->all());
        $this->clearTenantsCache();

        return response()->json([
            'success' => true,
            'data' => $tenant
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified tenant.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cacheKey = 'tenant_' . $id;

        return Cache::remember($cacheKey, 600, function () use ($id) {
            $tenant = $this->tenantService->getTenantById($id);

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $tenant
            ]);
        });
    }

    /**
     * Update the specified tenant in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $tenant = $this->tenantService->getTenantById($id);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:50',
            'last_name' => 'sometimes|required|string|max:50',
            'email' => 'sometimes|required|email|unique:tenants,email,' . $id,
            'phone' => 'sometimes|required|string|max:20',
            'date_of_birth' => 'nullable|date',
            'ssn' => 'nullable|string|max:11',
            'employment_status' => 'nullable|string|max:50',
            'annual_income' => 'nullable|numeric',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updatedTenant = $this->tenantService->updateTenant($id, $request->all());

        // Clear the tenants cache and specific tenant cache
        $this->clearTenantsCache();
        Cache::forget('tenant_' . $id);

        return response()->json([
            'success' => true,
            'data' => $updatedTenant
        ]);
    }

    /**
     * Remove the specified tenant from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $tenant = $this->tenantService->getTenantById($id);

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->tenantService->deleteTenant($id);

        // Clear the tenants cache and specific tenant cache
        $this->clearTenantsCache();
        Cache::forget('tenant_' . $id);

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    /**
     * Track cache keys for later invalidation
     */
    private function trackCacheKey($key)
    {
        $keys = Cache::get('tenant_cache_keys', []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::forever('tenant_cache_keys', $keys);
        }
    }

    /**
     * Clear all tenant list cache keys
     */
    private function clearTenantsCache()
    {
        // Get all cache keys related to tenants
        $keys = Cache::get('tenant_cache_keys', []);

        // Delete each key
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Reset cache keys tracking
        Cache::forever('tenant_cache_keys', []);
    }
}
