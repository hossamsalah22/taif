<?php

namespace App\Http\Controllers\User;

use App\Enums\ChildLearningPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChildRequest;
use App\Http\Resources\ChildResource;
use App\Http\Resources\User\ChildRewardResource;
use App\Http\Resources\User\ClinicalProgressReportItemResource;
use App\Models\Child;
use App\Models\ClinicalProgressReport;
use App\Models\ExerciseInteractionLog;
use App\Models\LearningLesson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                'requires_recalibration' => true,
            ], 409);
        }

        $child->update($validated);

        if ($criticalChanged) {
            // Recalibrate logic: wipe uncompleted tasks / trigger re-test
            $child->update(['force_re_test' => true]);
            DB::table('child_learning_plans')
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

    /**
     * Get the assessment and report status of the specified child.
     */
    public function status(Request $request, Child $child)
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $latestSubmission = $child->assessmentSubmissions()->latest()->first();

        $hasAssessment = $latestSubmission !== null;
        $hasReport = $hasAssessment && $latestSubmission->status === 'published';

        return $this->successResponse(__('Retrieved Successfully'), [
            'has_assessment' => $hasAssessment,
            'has_report' => $hasReport,
            'assessment_status' => $hasAssessment ? $latestSubmission->status : null,
        ]);
    }

    public function rewards(Request $request, Child $child)
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $activePlan = $child->childLearningPlans()->where('status', ChildLearningPlanStatusEnum::InProgress->value)->latest()->first();
        $plan = $activePlan ? $activePlan->learningPlan : null;

        $starJourneyPercentage = 0;
        if ($plan) {
            $totalGoals = $plan->goals()->count();
            $completedGoals = $child->completedGoals()->where('learning_plan_id', $plan->id)->count();
            if ($totalGoals > 0) {
                $starJourneyPercentage = round(($completedGoals / $totalGoals) * 100);
            }
        }

        $interactionDates = ExerciseInteractionLog::where('child_id', $child->id)
            ->selectRaw('DATE(created_at) as date')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $streakDays = 0;
        $checkDate = now()->startOfDay();

        if (! in_array($checkDate->toDateString(), $interactionDates)) {
            $checkDate->subDay();
        }

        while (in_array($checkDate->toDateString(), $interactionDates)) {
            $streakDays++;
            $checkDate->subDay();
        }

        $unlockedRewardIds = $child->rewards()->pluck('reward_id')->toArray();
        $planRewards = [];

        if ($plan) {
            $lessonsWithRewards = LearningLesson::whereHas('goal', function ($q) use ($plan) {
                $q->where('learning_plan_id', $plan->id);
            })->whereNotNull('reward_id')->with('reward')->get();

            ChildRewardResource::$unlockedRewardIds = $unlockedRewardIds;
            $planRewards = ChildRewardResource::collection($lessonsWithRewards->filter(fn ($l) => $l->reward !== null));
        }

        return $this->successResponse(__('Rewards retrieved successfully'), [
            'star_journey_percentage' => $starJourneyPercentage,
            'streak_days' => $streakDays,
            'rewards' => $planRewards,
        ]);
    }

    public function progressReport(Request $request, Child $child)
    {
        if ($child->parent_id !== $request->user()->id) {
            return $this->failedResponse(__('Data Not Found'), [], 404);
        }

        $activePlan = $child->childLearningPlans()->where('status', ChildLearningPlanStatusEnum::InProgress->value)->latest()->first();
        $plan = $activePlan ? $activePlan->learningPlan : null;

        $progressPercentage = 0;
        $chartData = [];

        if ($plan) {
            $totalLessons = LearningLesson::whereHas('goal', function ($q) use ($plan) {
                $q->where('learning_plan_id', $plan->id);
            })->count();

            $completedLessons = $child->completedLessons()->whereHas('goal', function ($q) use ($plan) {
                $q->where('learning_plan_id', $plan->id);
            })->count();

            if ($totalLessons > 0) {
                $progressPercentage = round(($completedLessons / $totalLessons) * 100);
            }

            $goals = $plan->goals()->orderBy('display_priority')->get();
            $labels = ['الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة', 'الثامنة', 'التاسعة', 'العاشرة'];

            foreach ($goals as $index => $goal) {
                $goalTotalLessons = $goal->lessons()->count();
                $goalCompletedLessons = $child->completedLessons()->where('learning_goal_id', $goal->id)->count();

                $percentage = $goalTotalLessons > 0 ? round(($goalCompletedLessons / $goalTotalLessons) * 100) : 0;

                $chartData[] = [
                    'label' => $labels[$index] ?? 'المرحلة '.($index + 1),
                    'percentage' => $percentage,
                ];
            }
        }

        $report = ClinicalProgressReport::where('child_id', $child->id)
            ->where('is_visible_to_parent', true)
            ->latest()
            ->first();

        $strengths = $report && is_array($report->strengths) ? $report->strengths : [];
        $improvements = $report && is_array($report->improvements) ? $report->improvements : [];

        $strengths = ClinicalProgressReportItemResource::collection($strengths);
        $improvements = ClinicalProgressReportItemResource::collection($improvements);

        return $this->successResponse(__('Progress report retrieved successfully'), [
            'progress_percentage' => $progressPercentage,
            'chart_data' => $chartData,
            'strengths' => $strengths,
            'needs_improvement' => $improvements,
            'smart_tip' => $report ? $report->smart_parental_advice : null,
        ]);
    }
}
