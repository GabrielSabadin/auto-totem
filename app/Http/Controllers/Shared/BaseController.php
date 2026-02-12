<?php

namespace App\Http\Controllers\Shared;

use App\Services\Shared\BaseService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

abstract class BaseController
{
    public function __construct(protected BaseService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->getAll($request, ...array_values(array_reverse($request->route()->parameters)));
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        return $this->service->get(...array_values(array_reverse(request()->route()->parameters)));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        return $this->service->delete(...array_values(array_reverse(request()->route()->parameters)));
    }
}
