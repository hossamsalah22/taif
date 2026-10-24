<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Turns a Blade view into a PDF with mPDF, which joins Arabic letters and lays out RTL text in plain PHP,
 * so exports work on any server without Chromium or Node.
 */
class PdfRenderer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromView(string $view, array $data = []): string
    {
        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'tempDir' => $tempDir,
        ]);

        $mpdf->WriteHTML(view($view, $data)->render());

        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
