<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'submission_id' => $this->id,
            'child_id' => $this->child_id,
            'assessment' => [
                'id' => $this->assessment->id,
                'title' => $this->assessment->title,
                'version' => $this->assessment_version,
            ],
            'status' => $this->status,
            'submitted_at' => $this->created_at,
            'report_published_at' => $this->report_published_at,
            'baseline_severity_level' => $this->child->autism_level->value ?? null,
            'diagnosed_severity_level' => $this->diagnosed_severity_level,
            'strengths' => $this->strengths,
            'improvements' => $this->improvements,
            'recommendations' => $this->recommendations,
            'download_url' => route('api.reports.download', ['submission' => $this->id]),
            // Removing full answers payload since report details screen is mostly specialist analysis
        ];
    }
}
