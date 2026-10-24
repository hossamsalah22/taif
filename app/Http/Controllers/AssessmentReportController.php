<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Http\Resources\AssessmentReportResource;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Services\PdfRenderer;
use Illuminate\Http\Request;

class AssessmentReportController extends Controller
{
    /**
     * Fetch the assessment report for a specific submission.
     */
    public function show(Request $request, Child $child, AssessmentSubmission $submission)
    {
        if ($child->parent_id !== $request->user()->id || $submission->child_id !== $child->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        if ($submission->status !== AssessmentSubmissionStatusEnum::PUBLISHED) {
            abort(400, 'Report is not published yet.');
        }

        $submission->load(['assessment', 'answers.question']);

        return response()->json([
            'report' => new AssessmentReportResource($submission),
        ]);
    }

    public function download(Request $request, Child $child, AssessmentSubmission $submission)
    {
        if ($child->parent_id !== $request->user()->id || $submission->child_id !== $child->id) {
            abort(403, 'Unauthorized access to this report.');
        }

        if ($submission->status !== AssessmentSubmissionStatusEnum::PUBLISHED) {
            abort(400, 'Report is not published yet.');
        }

        $pdf = PdfRenderer::fromView('reports.assessment', ['submission' => $submission]);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="assessment-report-'.$submission->id.'.pdf"');
    }
}
