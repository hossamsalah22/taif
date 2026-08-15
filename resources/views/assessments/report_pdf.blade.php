<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Assessment Report') }} - {{ $record->child->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 28px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 30%;
        }
        .section {
            margin-bottom: 25px;
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            border-right: 4px solid #4F46E5;
        }
        .section h3 {
            margin-top: 0;
            color: #374151;
            font-size: 18px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .rich-content {
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ __('Clinical Evaluation Report') }}</h1>
        <p>{{ __('Submission ID') }}: SUB-ASM-{{ $record->id }}</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell info-label">{{ __('Child Profile') }}</div>
            <div class="info-cell">{{ $record->child->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">{{ __('Assigned Severity') }}</div>
            <div class="info-cell">{{ \App\Enums\AutismLevelEnum::label($record->assessment->autism_level) }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">{{ __('Submission Time') }}</div>
            <div class="info-cell">{{ $record->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">{{ __('Performance Accuracy') }}</div>
            <div class="info-cell">{{ $record->performance_accuracy ? $record->performance_accuracy . '%' : __('Not Graded') }}</div>
        </div>
    </div>

    @if($record->specialist_notes)
    <div class="section">
        <h3>{{ __('Specialist Notes & Diagnostic Summary') }}</h3>
        <div class="rich-content">
            {!! $record->specialist_notes !!}
        </div>
    </div>
    @endif

    @if($record->strengths)
    <div class="section">
        <h3>{{ __('Strengths') }}</h3>
        <div class="rich-content">
            {{ $record->strengths }}
        </div>
    </div>
    @endif

    @if($record->improvements)
    <div class="section">
        <h3>{{ __('Areas for Improvement') }}</h3>
        <div class="rich-content">
            {{ $record->improvements }}
        </div>
    </div>
    @endif

    @if($record->recommendations)
    <div class="section">
        <h3>{{ __('Specialist Recommendations') }}</h3>
        <div class="rich-content">
            {{ $record->recommendations }}
        </div>
    </div>
    @endif

    @if($record->getFirstMediaUrl('reports'))
    <div class="section" style="text-align: center; background-color: #eef2ff;">
        <h3>{{ __('Clinical Report Document') }}</h3>
        <p>{{ __('An official clinical document was attached to this evaluation.') }}</p>
        <a href="{{ $record->getFirstMediaUrl('reports') }}" style="display: inline-block; padding: 10px 20px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">
            {{ __('Download Document') }}
        </a>
    </div>
    @endif

</body>
</html>
