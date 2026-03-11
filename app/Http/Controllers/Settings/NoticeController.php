<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::with('creator')->orderBy('created_at', 'desc')->paginate(15);
        return view('settings.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('settings.notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:notice,event',
            'expires_at' => 'nullable|date',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        Notice::create($validated);

        return redirect()->route('settings.notices.index')
            ->with('success', 'Notice/Event created successfully.');
    }

    public function edit(Notice $notice)
    {
        return view('settings.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:notice,event',
            'expires_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $notice->update($validated);

        return redirect()->route('settings.notices.index')
            ->with('success', 'Notice/Event updated successfully.');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        return redirect()->route('settings.notices.index')
            ->with('success', 'Notice/Event deleted successfully.');
    }
}
