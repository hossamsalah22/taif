<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssessmentReportResource;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

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

        if ($submission->status !== 'published') {
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

        if ($submission->status !== 'published') {
            abort(400, 'Report is not published yet.');
        }

        $html = view('reports.assessment', ['submission' => $submission])->render();

        $pdf = app()->environment('local') ? Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->pdf() :
            Browsershot::html($html)
                ->setNodeModulePath('/home/techorgmobile/public_html/taifapp.com.app/node_modules')
                ->format('A4')
                ->margins(10, 10, 10, 10)
                ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="assessment-report-'.$submission->id.'.pdf"');
    }
}
