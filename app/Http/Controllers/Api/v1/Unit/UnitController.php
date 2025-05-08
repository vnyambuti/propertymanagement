<?php

namespace App\Http\Controllers\Api\v1\Unit;

use App\Http\Controllers\Controller;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class UnitController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
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
