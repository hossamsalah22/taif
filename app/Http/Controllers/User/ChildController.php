<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChildRequest;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    /**
     * Display a listing of the children for the authenticated user.
     */
    public function index(Request $request)
    {
        $children = $request->user()->children()->get();

        return $this->successResponse(__('Retrieved Successfully'), ChildResource::collection($children), 200);
    }

    /**
     * Store a newly created child in storage.
     */
    public function store(ChildRequest $request)
    {
        $data = $request->validated();

        $child = Child::create($data);

        return $this->successResponse(__('Created Successfully'), ChildResource::make($child), 201);
    }

    /**
     * Display the specified child.
     */
    public function show(Request $request, Child $child)
    {
        if ($child->parent_id !== auth('user')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        return $this->successResponse(__('Retrieved Successfully'), ChildResource::make($child));
    }

    /**
     * Update the specified child in storage.
     */
    public function update(ChildRequest $request, Child $child)
    {
        if ($child->parent_id !== auth('user')->id()) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $child->update($request->validated());

        return $this->successResponse(__('Updated Successfully'), ChildResource::make($child));
    }

    /**
     * Remove the specified child from storage.
     */
    public function destroy(Request $request, Child $child)
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $child->delete();

        return $this->successResponse(__('Deleted Successfully'), [], 200);
    }
}
