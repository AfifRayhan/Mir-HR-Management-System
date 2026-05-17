<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;

class ReportGeneratorController extends Controller
{
    /**
     * Show the report generation form.
     */
    public function index()
    {
        // Get types that actually have active templates
        $types = ReportTemplateType::whereHas('templates', function($q) {
            $q->where('is_active', true);
        })->get();
        
        $formats = ['English', 'Bangla', 'Bangla-2'];

        $employees = \App\Models\Employee::with(['designation', 'department', 'office'])
            ->where('status', 'active')
            ->get();

        return view('reports.generate.index', compact('types', 'formats', 'employees'));
    }

    /**
     * AJAX endpoint to fetch dynamic fields for a selected template.
     */
    public function getFields(Request $request)
    {
        $request->validate([
            'report_template_type_id' => 'required|exists:report_template_types,id',
            'format' => 'required|string',
        ]);

        $template = ReportTemplate::where('report_template_type_id', $request->input('report_template_type_id'))
            ->where('format', $request->input('format'))
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json(['error' => 'No active template found for the selected type and format.'], 404);
        }

        // Get tags from the type
        $tags = preg_split('/[\s,;]+|&nbsp;?/', $template->type->key_tags, -1, PREG_SPLIT_NO_EMPTY);
        $tags = array_filter($tags, function($tag) {
            return preg_match('/^#[a-zA-Z]\w*$/', $tag);
        });

        return response()->json([
            'tags' => array_values($tags)
        ]);
    }

    /**
     * Generate the PDF report.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'report_template_type_id' => 'required|exists:report_template_types,id',
            'format' => 'required|string',
            'tags' => 'nullable|array',
        ]);

        $template = ReportTemplate::with('type')
            ->where('report_template_type_id', $request->input('report_template_type_id'))
            ->where('format', $request->input('format'))
            ->where('is_active', true)
            ->firstOrFail();

        $content = $template->content;
        $tagInputs = $request->input('tags', []);

        // Replace tags in content
        foreach ($tagInputs as $tag => $value) {
            $searchTag = str_starts_with($tag, '#') ? $tag : '#' . $tag;
            $value = htmlspecialchars($value);
            $content = str_replace($searchTag, $value, $content);
        }

        $reportName = $template->type->name;

        return view('reports.generate.preview', compact('content', 'reportName'));
    }

    /**
     * Sanitize HTML content by removing dangerous tags and event handlers.
     */
    private function sanitizeHtml(string $content): string
    {
        // Strip potentially dangerous tags (script, iframe, object, embed, link, meta, base)
        $content = preg_replace('/<\s*(script|iframe|object|embed|link|meta|base)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $content);
        $content = preg_replace('/<\s*(script|iframe|object|embed|link|meta|base)[^>]*\/?>/is', '', $content);

        // Remove event handlers (onclick, onerror, onload, etc.)
        $content = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/\bon\w+\s*=\s*\S+/i', '', $content);

        return $content;
    }

    /**
     * Generate the PDF report from final content with proper A4 formatting.
     */
    public function generatePdf(Request $request)
    {
        $request->validate([
            'final_content' => 'required|string',
            'report_name' => 'required|string',
        ]);

        $content = $this->sanitizeHtml($request->input('final_content'));

        // Wrap content in a proper HTML document with A4 formatting, spacing, and margins
        $wrappedHtml = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: A4;
        margin: 20mm 18mm 20mm 18mm;
    }
    body {
        font-family: Calibri, "SolaimanLipi", Arial, sans-serif;
        font-size: 13px;
        line-height: 1.6;
        color: #222;
        margin: 0;
        padding: 0;
    }
    p {
        margin-top: 4px;
        margin-bottom: 8px;
        line-height: 1.6;
    }
    table {
        border-collapse: collapse;
        width: auto;
        line-height: 1.5;
    }
    td, th {
        padding: 3px 6px;
        vertical-align: top;
    }
    ul, ol {
        margin-top: 4px;
        margin-bottom: 8px;
        padding-left: 24px;
    }
    li {
        margin-bottom: 4px;
        line-height: 1.5;
    }
    div {
        line-height: 1.6;
    }
    h1, h2, h3, h4, h5, h6 {
        margin-top: 8px;
        margin-bottom: 6px;
        line-height: 1.4;
    }
    strong, b {
        font-weight: bold;
    }
</style>
</head>
<body>' . $content . '</body>
</html>';

        $pdf = Pdf::loadHTML($wrappedHtml);

        // Set SnappyPdf options for proper A4 rendering
        $pdf->setOption('page-size', 'A4');
        $pdf->setOption('margin-top', '20mm');
        $pdf->setOption('margin-bottom', '20mm');
        $pdf->setOption('margin-left', '18mm');
        $pdf->setOption('margin-right', '18mm');
        $pdf->setOption('encoding', 'UTF-8');
        $pdf->setOption('print-media-type', true);
        $pdf->setOption('disable-smart-shrinking', true);

        $fileName = Str::slug($request->input('report_name')) . '-' . date('Ymd-His') . '.pdf';

        if ($request->input('action') === 'print') {
            return $pdf->inline($fileName);
        }
        return $pdf->download($fileName);
    }

    /**
     * Generate the DOCX report from final content.
     */
    public function generateDocx(Request $request)
    {
        $request->validate([
            'final_content' => 'required|string',
            'report_name' => 'required|string',
        ]);

        $content = $this->sanitizeHtml($request->input('final_content'));

        // PHPWord's HTML reader works best with a full HTML document
        $wrappedHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Calibri", sans-serif; font-size: 11pt; line-height: 1.5; }
        p { margin-bottom: 10pt; }
    </style>
</head>
<body>' . $content . '</body>
</html>';

        // Save to a temporary file as PHPWord's HTML reader prefers files
        $tempHtmlPath = storage_path('app/private/temp_' . uniqid() . '.html');
        file_put_contents($tempHtmlPath, $wrappedHtml);

        try {
            // Use IOFactory to load the HTML file - this is more robust than addHtml
            $phpWord = IOFactory::load($tempHtmlPath, 'HTML');
            
            // The HTML reader creates its own sections. Let's ensure they have A4 margins.
            foreach ($phpWord->getSections() as $section) {
                $section->getStyle()->setPageSizeW(11906); // A4 Width
                $section->getStyle()->setPageSizeH(16838); // A4 Height
                $section->getStyle()->setMarginTop(1440);  // 1 inch
                $section->getStyle()->setMarginBottom(1440);
                $section->getStyle()->setMarginLeft(1440);
                $section->getStyle()->setMarginRight(1440);
            }

            // Generate the DOCX file
            $fileName = Str::slug($request->input('report_name')) . '-' . date('Ymd-His') . '.docx';
            $tempDocxPath = storage_path('app/private/' . $fileName);

            $writer = IOFactory::createWriter($phpWord, 'Word2007');
            $writer->save($tempDocxPath);

            // Clean up the temp HTML file
            if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);

            return response()->download($tempDocxPath, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Clean up on failure
            if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
            
            // If PHPWord loading fails, fallback to the project's "reference" method:
            // Returning raw HTML with MS-Word headers
            $filename = Str::slug($request->input('report_name')) . '-' . date('Ymd-His') . '.doc';
            return response($wrappedHtml)
                ->header('Content-Type', 'application/vnd.ms-word')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
    }
}
