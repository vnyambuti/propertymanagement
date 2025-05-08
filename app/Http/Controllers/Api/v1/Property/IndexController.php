<?php

namespace App\Http\Controllers\Api\v1\Property;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

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
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'town' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'type' => 'required|string|max:50',
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
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:255',
            'town' => 'sometimes|string|max:255',
            'county' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:50',
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
