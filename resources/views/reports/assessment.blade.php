<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Assessment Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; line-height: 1.6; color: #333; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 24px; color: #2c3e50; }
        .meta-info { margin-bottom: 20px; padding: 10px; background: #f9f9f9; border-radius: 5px; }
        .meta-info p { margin: 5px 0; }
        .section { margin-bottom: 20px; }
        h2 { font-size: 18px; color: #2980b9; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .content { padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 5px; min-height: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Assessment Specialist Report</h1>
        <p>Report ID: #{{ $submission->id }}</p>
    </div>

    <div class="meta-info">
        <p><strong>Date Published:</strong> {{ $submission->report_published_at ? $submission->report_published_at->format('Y-m-d H:i') : 'N/A' }}</p>
        <p><strong>Baseline Severity Level:</strong> {{ $submission->child->autism_level->value ?? 'N/A' }}</p>
        <p><strong>Diagnosed Severity Level:</strong> {{ $submission->diagnosed_severity_level ?? 'N/A' }}</p>
    </div>

    <div class="section">
        <h2>Strengths</h2>
        <div class="content">
            {!! nl2br(e($submission->strengths)) !!}
        </div>
    </div>

    <div class="section">
        <h2>Areas for Improvement</h2>
        <div class="content">
            {!! nl2br(e($submission->improvements)) !!}
        </div>
    </div>

    <div class="section">
        <h2>Specialist Recommendations</h2>
        <div class="content">
            {!! nl2br(e($submission->recommendations)) !!}
        </div>
    </div>
</body>
</html>
