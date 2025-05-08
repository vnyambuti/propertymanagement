<?php

namespace App\Http\Controllers\Api\v1\Property;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
        $perPage = $request->get('per_page', 15);
        $properties = $this->propertyService->getAllProperties($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
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
        $perPage = $request->get('per_page', 15);
        $properties = $this->propertyService->getPropertiesByUser($userId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }
}
