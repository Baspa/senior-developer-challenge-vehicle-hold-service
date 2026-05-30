<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateHoldRequest;
use App\Http\Resources\HoldResource;
use App\Services\HoldService;
use Illuminate\Http\JsonResponse;
use App\Models\Vehicle;
use App\Models\Hold;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    public function __construct(private readonly HoldService $holdService) {}

    public function store(CreateHoldRequest $request): JsonResponse
    {
        /** @var Vehicle $vehicle */
        $vehicle = Vehicle::findOrFail($request->validated()['vehicle_id']);

        $hold = $this->holdService->create($vehicle, $request->validated()['buyer_ref']);

        return HoldResource::make($hold)->response()
            ->setStatusCode(201);
    }

    public function show(Hold $hold): HoldResource
    {
        return HoldResource::make($hold);
    }

    public function destroy(Request $request, Hold $hold): HoldResource
    {
        $hold = $this->holdService->release($hold, $request->header('X-Release-Token'));

        return HoldResource::make($hold);
    }
}
