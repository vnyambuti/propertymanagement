<?php

namespace App\Http\Controllers\Api\v1\Property;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Property",
 *     description="API Endpoints for property management"
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="API Server"
 * )
 */

class IndexController extends Controller
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    /**
     * Display a listing of the properties.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/property",
     *     summary="Get a list of properties",
     *     description="Returns a paginated list of all properties",
     *     operationId="getPropertiesList",
     *     tags={"Properties"},
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
     *                     @OA\Items(ref="#/components/schemas/Property")
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
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->get('per_page', 15);

        $cacheKey = 'properties_page_' . $page . '_' . $perPage;

        // Add the key to our list of property cache keys
        $this->trackCacheKey($cacheKey);

        // Cache the results for 10 minutes
        return Cache::remember($cacheKey, 600, function () use ($perPage) {
            $properties = $this->propertyService->getAllProperties($perPage);

            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        });
    }

    /**
     * Store a newly created property in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @OA\Post(
     *     path="/property",
     *     summary="Create a new property",
     *     description="Stores a new property and returns the property data",
     *     operationId="storeProperty",
     *     tags={"Properties"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Property data",
     *         @OA\JsonContent(
     *             required={"name", "address", "town", "county", "type", "user_id"},
     *             @OA\Property(property="name", type="string", example="Sunset Apartments"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="town", type="string", example="Springfield"),
     *             @OA\Property(property="county", type="string", example="Greenfield County"),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"apartment", "house", "commercial", "condo", "townhouse"},
     *                 example="apartment"
     *             ),
     *             @OA\Property(property="user_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Property created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Property")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'town' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'type' => 'required|string|in:apartment,house,commercial,condo,townhouse',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $property = $this->propertyService->createProperty($request->all());

        // Clear the properties cache after creating a new property
        $this->clearPropertiesCache();

        return response()->json([
            'success' => true,
            'data' => $property
        ], 201);
    }

    /**
     * Display the specified property.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/property/{id}",
     *     summary="Get property details",
     *     description="Returns details for a specific property",
     *     operationId="getProperty",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Property")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Property not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function show($id)
    {
        $cacheKey = 'property_' . $id;

        return Cache::remember($cacheKey, 600, function () use ($id) {
            try {
                $property = $this->propertyService->getPropertyById($id);

                return response()->json([
                    'success' => true,
                    'data' => $property
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Property not found'
                ], 404);
            }
        });
    }

    /**
     * Update the specified property in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     * @OA\Put(
     *     path="/property/{id}",
     *     summary="Update a property",
     *     description="Updates an existing property and returns the updated property data",
     *     operationId="updateProperty",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Property data",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Sunset Apartments Updated"),
     *             @OA\Property(property="address", type="string", example="123 Main Street"),
     *             @OA\Property(property="town", type="string", example="Springfield"),
     *             @OA\Property(property="county", type="string", example="Greenfield County"),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={"apartment", "house", "commercial", "condo", "townhouse"},
     *                 example="apartment"
     *             ),
     *             @OA\Property(property="user_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Property")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Property not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'town' => 'sometimes|string|max:255',
            'county' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:apartment,house,commercial,condo,townhouse',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $property = $this->propertyService->updateProperty($id, $request->all());

            // Clear the properties cache and specific property cache
            $this->clearPropertiesCache();
            Cache::forget('property_' . $id);

            return response()->json([
                'success' => true,
                'data' => $property
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }
    }

    /**
     * Remove the specified property from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     *
     * @OA\Delete(
     *     path="/property/{id}",
     *     summary="Delete a property",
     *     description="Deletes a property and returns a success message",
     *     operationId="deleteProperty",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Property deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Property deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Property not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Property not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $this->propertyService->deleteProperty($id);

            // Clear the properties cache and specific property cache
            $this->clearPropertiesCache();
            Cache::forget('property_' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }
    }

    /**
     * Get properties by user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *     path="/property/user/{userId}",
     *     summary="Get properties by user",
     *     description="Returns a paginated list of properties for a specific user",
     *     operationId="getPropertiesByUser",
     *     tags={"Properties"},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
     *                     @OA\Items(ref="#/components/schemas/Property")
     *                 ),
     *                 @OA\Property(property="total", type="integer", example=30),
     *                 @OA\Property(property="per_page", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getByUser(Request $request, $userId)
    {
        $page = $request->input('page', 1);
        $perPage = $request->get('per_page', 15);

        $cacheKey = 'properties_user_' . $userId . '_page_' . $page . '_' . $perPage;

        // Add the key to our list of property cache keys
        $this->trackCacheKey($cacheKey);

        // Cache the results for 10 minutes
        return Cache::remember($cacheKey, 600, function () use ($userId, $perPage) {
            $properties = $this->propertyService->getPropertiesByUser($userId, $perPage);

            return response()->json([
                'success' => true,
                'data' => $properties
            ]);
        });
    }

    /**
     * Track cache keys for later invalidation
     */
    private function trackCacheKey($key)
    {
        $keys = Cache::get('property_cache_keys', []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::forever('property_cache_keys', $keys);
        }
    }

    /**
     * Clear all property list cache keys
     */
    private function clearPropertiesCache()
    {
        // Get all cache keys related to properties
        $keys = Cache::get('property_cache_keys', []);

        // Delete each key
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        // Reset cache keys tracking
        Cache::forever('property_cache_keys', []);
    }
}
