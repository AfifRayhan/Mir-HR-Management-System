<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\Snappy\Facades\SnappyPdf as Pdf;

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
     * Generate the PDF report from final content.
     */
    public function generatePdf(Request $request)
    {
        $request->validate([
            'final_content' => 'required|string',
            'report_name' => 'required|string',
        ]);

        $content = $request->input('final_content');

        // Strip potentially dangerous tags (script, iframe, object, embed, link, meta, base)
        $content = preg_replace('/<\s*(script|iframe|object|embed|link|meta|base)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $content);
        $content = preg_replace('/<\s*(script|iframe|object|embed|link|meta|base)[^>]*\/?>/is', '', $content);

        // Remove event handlers (onclick, onerror, onload, etc.)
        $content = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/\bon\w+\s*=\s*\S+/i', '', $content);

        $pdf = Pdf::loadHTML($content);
        $fileName = Str::slug($request->input('report_name')) . '-' . date('Ymd-His') . '.pdf';

        return $pdf->download($fileName);
    }
}
