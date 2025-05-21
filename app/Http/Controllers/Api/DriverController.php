<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Filters\Api\DriverFilter;
use App\Models\Driver;
use App\Http\Requests\Api\StoreDriverRequest;
use App\Http\Requests\Api\UpdateDriverRequest;
use App\Http\Requests\Api\ReplaceDriverRequest;
use App\Http\Resources\Api\DriverResource;
use App\Policies\Api\DriverPolicy;
use Illuminate\Http\Request;

class DriverController extends ApiController
{
    protected $policyClass = DriverPolicy::class;

        public function index(Request $request)
        {
            $filter = new DriverFilter($request);

            $query = Driver::query();

            if ($this->include('team')) {
                $query->with('team');
            }

            $drivers = $filter->apply($query)->paginate();

            return $this->success('F1 2025 Drivers', DriverResource::collection($drivers));
        }

    public function store(StoreDriverRequest $request)
    {
        $attributes = $request->mappedAttributes();
        $driver = Driver::create($attributes);
        
        $driver->load('team');

        return $this->success('Driver created successfully', new DriverResource($driver), 201);
    }

    public function show(Driver $driver)
    {
        if (! $this->authorize('view', $driver)) {
            return $this->notAuthorized('You are not authorized to view this driver');
        }

        return $this->success("Driver N° $driver->id", new DriverResource($driver));
    }

    public function update(UpdateDriverRequest $request, Driver $driver)
    {
        if (! $this->authorize('update', $driver)) {
            return $this->notAuthorized('You are not authorized to update this driver');
        }

        $attributes = $request->mappedAttributes();
        $driver->update($attributes);

        return $this->success('Driver updated successfully', new DriverResource($driver));
    }

    public function replace(ReplaceDriverRequest $request, Driver $driver)
    {
        $attributes = $request->mappedAttributes();
        $driver->fill($attributes);
        $driver->save();

        return $this->success('Driver replaced successfully', new DriverResource($driver));
    }

    public function destroy(Driver $driver)
    {
        if (! $this->authorize('delete', $driver)) {
            return $this->notAuthorized('You are not authorized to delete this driver');
        }

        $driver->delete();

        return $this->noContent("$driver->name deleted successfully");
    }
}