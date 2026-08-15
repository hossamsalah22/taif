<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Report / تقرير التقييم</title>
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
        .header p {
            color: #6b7280;
            margin-top: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            background: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
            width: 40%;
        }
        .info-label .en {
            font-size: 0.85em;
            color: #6b7280;
            display: block;
        }
        .section {
            margin-bottom: 25px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            border-right: 4px solid #4F46E5;
        }
        .section h3 {
            margin-top: 0;
            color: #111827;
            font-size: 18px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        .section h3 .en {
            font-size: 0.85em;
            color: #6b7280;
            font-weight: normal;
        }
        .rich-content {
            font-size: 15px;
            color: #4b5563;
            white-space: pre-line;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>تقرير تقييم الأخصائي <br> <span style="font-size: 20px; color: #6b7280;">Assessment Specialist Report</span></h1>
        <p>رقم التقرير / Report ID: #{{ $submission->id }}</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell info-label">
                ملف الطفل
                <span class="en">Child Profile</span>
            </div>
            <div class="info-cell">{{ $submission->child->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">
                مستوى الشدة المبدئي
                <span class="en">Baseline Severity Level</span>
            </div>
            <div class="info-cell">{{ $submission->child->autism_level->value ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">
                مستوى الشدة المشخص
                <span class="en">Diagnosed Severity Level</span>
            </div>
            <div class="info-cell">{{ $submission->diagnosed_severity_level ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-cell info-label">
                تاريخ النشر
                <span class="en">Date Published</span>
            </div>
            <div class="info-cell">{{ $submission->report_published_at ? $submission->report_published_at->format('Y-m-d H:i') : 'N/A' }}</div>
        </div>
    </div>

    @if($submission->specialist_notes)
    <div class="section">
        <h3>
            <span>ملاحظات الأخصائي وملخص التشخيص</span>
            <span class="en" dir="ltr">Specialist Notes & Diagnostic Summary</span>
        </h3>
        <div class="rich-content">
            {!! $submission->specialist_notes !!}
        </div>
    </div>
    @endif

    @if($submission->strengths)
    <div class="section">
        <h3>
            <span>نقاط القوة</span>
            <span class="en" dir="ltr">Strengths</span>
        </h3>
        <div class="rich-content">{{ $submission->strengths }}</div>
    </div>
    @endif

    @if($submission->improvements)
    <div class="section">
        <h3>
            <span>مجالات التحسين</span>
            <span class="en" dir="ltr">Areas for Improvement</span>
        </h3>
        <div class="rich-content">{{ $submission->improvements }}</div>
    </div>
    @endif

    @if($submission->recommendations)
    <div class="section">
        <h3>
            <span>توصيات الأخصائي</span>
            <span class="en" dir="ltr">Specialist Recommendations</span>
        </h3>
        <div class="rich-content">{{ $submission->recommendations }}</div>
    </div>
    @endif

    @if($submission->getFirstMediaUrl('reports'))
    <div class="section" style="text-align: center; background-color: #eef2ff;">
        <h3>
            <span>ملف التقرير السريري المرفق</span>
            <span class="en" dir="ltr">Attached Clinical Report Document</span>
        </h3>
        <p style="color: #6b7280;">تم إرفاق ملف تقرير سريري رسمي بهذا التقييم. <br> An official clinical document was attached to this evaluation.</p>
        <a href="{{ $submission->getFirstMediaUrl('reports') }}" style="display: inline-block; padding: 10px 20px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 5px; margin-top: 10px;">
            تحميل الملف / Download Document
        </a>
    </div>
    @endif

</body>
</html>
