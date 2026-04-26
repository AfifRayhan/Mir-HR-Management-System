<?php

namespace App\Http\Controllers;

use App\Models\ReportTemplate;
use App\Models\ReportTemplateType;
use Illuminate\Http\Request;

class ReportTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = ReportTemplate::with('type')->get();
        return view('reports.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formats = ['English', 'Bangla', 'Bangla-2'];
        return view('reports.templates.create', compact('formats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'format' => 'required|string',
            'content' => 'required',
            'is_active' => 'boolean',
        ]);

        // Extract tags from content
        preg_match_all('/#[a-zA-Z]\w*/', $validated['content'], $matches);
        $contentTags = !empty($matches[0]) ? array_unique($matches[0]) : [];

        // Create the type first
        $type = ReportTemplateType::create([
            'name' => $validated['name'],
            'key_tags' => implode(',', $contentTags), // Save extracted tags
        ]);

        ReportTemplate::create([
            'report_template_type_id' => $type->id,
            'format' => $validated['format'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('personnel.report-templates.index')
            ->with('success', 'Letter template created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $template = ReportTemplate::findOrFail($id);
        $types = ReportTemplateType::all();
        $formats = ['English', 'Bangla', 'Bangla-2'];
        return view('reports.templates.edit', compact('template', 'types', 'formats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);

        $validated = $request->validate([
            'report_template_type_id' => 'required|exists:report_template_types,id',
            'format' => 'required|string',
            'content' => 'required',
            'is_active' => 'boolean',
            'new_tags' => 'nullable|array',
            'new_tags.*' => 'string'
        ]);

        $template->update([
            'report_template_type_id' => $validated['report_template_type_id'],
            'format' => $validated['format'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Process tags: from manual input + from content
        $type = ReportTemplateType::find($validated['report_template_type_id']);
        if ($type) {
            $existingTags = preg_split('/[\s,;]+|&nbsp;?/', $type->key_tags, -1, PREG_SPLIT_NO_EMPTY);
            
            // From content
            preg_match_all('/#[a-zA-Z]\w*/', $validated['content'], $matches);
            $contentTags = !empty($matches[0]) ? $matches[0] : [];
            
            // From manual input
            $manualTags = $request->input('new_tags', []);
            
            $allTags = array_unique(array_merge($existingTags, $contentTags, $manualTags));
            $type->update(['key_tags' => implode(',', $allTags)]);
        }

        return redirect()->route('personnel.report-templates.index')
            ->with('success', 'Letter template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $template = ReportTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('personnel.report-templates.index')
            ->with('success', 'Letter template deleted successfully.');
    }
}
