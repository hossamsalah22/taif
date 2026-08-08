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

        $validated = $request->validated();
        
        // TAYF-85: Check for critical diagnostic changes
        $criticalChanged = false;
        if (
            $child->age != $validated['age'] || 
            $child->autism_level !== $validated['autism_level'] || 
            $child->speech_status !== $validated['speech_status']
        ) {
            $criticalChanged = true;
        }

        if ($criticalChanged && empty($validated['confirm_recalibrate'])) {
            return $this->failedResponse(__('Warning: Changing critical diagnostic data will update the task layout. Please confirm to proceed.'), [
                'requires_recalibration' => true
            ], 409);
        }

        $child->update($validated);

        if ($criticalChanged) {
            // Recalibrate logic: wipe uncompleted tasks / trigger re-test
            $child->update(['force_re_test' => true]);
            \Illuminate\Support\Facades\DB::table('child_learning_plans')
                ->where('child_id', $child->id)
                ->where('is_completed', false)
                ->delete();
        }

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
