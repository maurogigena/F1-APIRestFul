<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\ReplaceCircuitRequest;
use App\Http\Requests\Api\StoreCircuitRequest;
use App\Http\Requests\Api\UpdateCircuitRequest;
use App\Http\Resources\Api\CircuitResource;
use App\Http\Filters\Api\CircuitFilter;

use App\Models\Circuit;
use App\Policies\Api\CircuitPolicy;

class CircuitController extends ApiController
{
    protected $policyClass = CircuitPolicy::class;

    public function index(CircuitFilter $filter)
    {
        $query = Circuit::query();
        $filtered = $filter->apply($query);

        return $this->ok('F1 2025 Circuits', CircuitResource::collection($filtered->paginate()));
    }

    public function store(StoreCircuitRequest $request)
    {
        $this->authorize('store', Circuit::class);

        $attributes = $request->mappedAttributes();
        $circuit = Circuit::create($attributes);

        return $this->success('Circuit created successfully', new CircuitResource($circuit), 201);
    }

    public function show(Circuit $circuit)
    {
        return $this->ok("Circuit of $circuit->country", new CircuitResource($circuit));
    }

    public function update(UpdateCircuitRequest $request, Circuit $circuit)
    {
        $this->authorize('update', $circuit);

        $circuit->update($request->mappedAttributes());

        return $this->ok('Circuit Updated Successfully', new CircuitResource($circuit));
    }

    public function replace(ReplaceCircuitRequest $request, Circuit $circuit)
    {
        $this->authorize('replace', $circuit);

        $circuit->fill($request->mappedAttributes())->save();

        return $this->ok('Circuit Replaced Successfully', new CircuitResource($circuit));
    }

    public function destroy(Circuit $circuit)
    {
        $this->authorize('delete', $circuit);

        $circuit->delete();

        return $this->noContent("Circuit of $circuit->city deleted successfully");
    }
}