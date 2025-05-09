<?php

namespace App\Http\Controllers\Api\v1\Unit;

use App\Http\Controllers\Controller;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Units",
 *     description="API Endpoints for Unit management"
 * )
 */
class UnitController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/units",
     *     operationId="getUnitsList",
     *     tags={"Units"},
     *     summary="Get paginated list of units",
     *     description="Returns a paginated list of all units",
     *     security={{"bearerAuth":{}}},
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Unit")),
     *                 @OA\Property(property="meta", type="object"),
     *                 @OA\Property(property="links", type="object")
     *             )
     *         )
     *     )
     * )
     *
     * Display a listing of the units.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $units = $this->unitService->getAllUnits($perPage);

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/units",
     *     operationId="storeUnit",
     *     tags={"Units"},
     *     summary="Create a new unit",
     *     description="Stores a new unit in the database",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unit_number", "property_id"},
     *             @OA\Property(property="unit_number", type="string", maxLength=50, example="101A"),
     *             @OA\Property(property="property_id", type="integer", example=1),
     *             @OA\Property(property="floor", type="integer", example=1),
     *             @OA\Property(property="square_feet", type="number", format="float", example=750),
     *             @OA\Property(property="bedrooms", type="integer", example=2),
     *             @OA\Property(property="bathrooms", type="number", format="float", example=1.5),
     *             @OA\Property(property="rent_amount", type="number", format="float", example=1200),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"vacant", "occupied", "maintenance"},
     *                 example="vacant"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Unit created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Unit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Store a newly created unit in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_number' => 'required|string|max:50',
            'property_id' => 'required|exists:properties,id',
            'floor' => 'nullable|integer',
            'square_feet' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|numeric',
            'rent_amount' => 'nullable|numeric',
            'status' => 'nullable|string|in:vacant,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $unit = $this->unitService->createUnit($request->all());

        return response()->json([
            'success' => true,
            'data' => $unit
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/units/{id}",
     *     operationId="getUnitById",
     *     tags={"Units"},
     *     summary="Get unit information",
     *     description="Returns unit data for a specific unit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Unit ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Unit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     )
     * )
     *
     * Display the specified unit.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $unit = $this->unitService->getUnitById($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/units/{id}",
     *     operationId="updateUnit",
     *     tags={"Units"},
     *     summary="Update an existing unit",
     *     description="Updates an existing unit's information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Unit ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="unit_number", type="string", maxLength=50, example="101A"),
     *             @OA\Property(property="property_id", type="integer", example=1),
     *             @OA\Property(property="floor", type="integer", example=1),
     *             @OA\Property(property="square_feet", type="number", format="float", example=750),
     *             @OA\Property(property="bedrooms", type="integer", example=2),
     *             @OA\Property(property="bathrooms", type="number", format="float", example=1.5),
     *             @OA\Property(property="rent_amount", type="number", format="float", example=1200),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"vacant", "occupied", "maintenance"},
     *                 example="vacant"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Unit")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     *
     * Update the specified unit in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $unit = $this->unitService->getUnitById($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'unit_number' => 'sometimes|required|string|max:50',
            'property_id' => 'sometimes|required|exists:properties,id',
            'floor' => 'nullable|integer',
            'square_feet' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|numeric',
            'rent_amount' => 'nullable|numeric',
            'status' => 'nullable|string|in:vacant,occupied,maintenance',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $updatedUnit = $this->unitService->updateUnit($id, $request->all());

        return response()->json([
            'success' => true,
            'data' => $updatedUnit
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/units/{id}",
     *     operationId="deleteUnit",
     *     tags={"Units"},
     *     summary="Delete a unit",
     *     description="Deletes an existing unit",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         description="Unit ID",
     *         required=true,
     *         in="path",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unit deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Unit deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Unit not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unit not found")
     *         )
     *     )
     * )
     *
     * Remove the specified unit from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $unit = $this->unitService->getUnitById($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $this->unitService->deleteUnit($id);

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/properties/{propertyId}/units",
     *     operationId="getUnitsByProperty",
     *     tags={"Units"},
     *     summary="Get units by property",
     *     description="Returns units associated with a specific property",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="propertyId",
     *         description="Property ID",
     *         required=true,
     *         in="path",
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Unit")),
     *                 @OA\Property(property="meta", type="object"),
     *                 @OA\Property(property="links", type="object")
     *             )
     *         )
     *     )
     * )
     *
     * Get units by property.
     *
     * @param Request $request
     * @param int $propertyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitsByProperty(Request $request, $propertyId)
    {
        $perPage = $request->get('per_page', 15);
        $units = $this->unitService->getUnitsByProperty($propertyId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/units/vacant",
     *     operationId="getVacantUnits",
     *     tags={"Units"},
     *     summary="Get vacant units",
     *     description="Returns vacant units, optionally filtered by property",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="property_id",
     *         in="query",
     *         description="Filter by Property ID",
     *         required=false,
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
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Unit")),
     *                 @OA\Property(property="meta", type="object"),
     *                 @OA\Property(property="links", type="object")
     *             )
     *         )
     *     )
     * )
     *
     * Get vacant units, optionally filtered by property.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVacantUnits(Request $request)
    {
        $propertyId = $request->get('property_id');
        $perPage = $request->get('per_page', 15);

        $units = $this->unitService->getVacantUnits($propertyId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $units
        ]);
    }
}

/**
 * @OA\Schema(
 *     schema="Unit",
 *     required={"id", "unit_number", "property_id"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="unit_number", type="string", example="101A"),
 *     @OA\Property(property="property_id", type="integer", example=1),
 *     @OA\Property(property="floor", type="integer", example=1, nullable=true),
 *     @OA\Property(property="square_feet", type="number", format="float", example=750, nullable=true),
 *     @OA\Property(property="bedrooms", type="integer", example=2, nullable=true),
 *     @OA\Property(property="bathrooms", type="number", format="float", example=1.5, nullable=true),
 *     @OA\Property(property="rent_amount", type="number", format="float", example=1200, nullable=true),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"vacant", "occupied", "maintenance"},
 *         example="vacant",
 *         nullable=true
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
