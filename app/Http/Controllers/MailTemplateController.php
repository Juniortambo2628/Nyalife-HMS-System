<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MailTemplate;
use Inertia\Inertia;

class MailTemplateController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/MailTemplates/Index', [
            'templates' => MailTemplate::all()
        ]);
    }

    public function edit($id)
    {
        $template = MailTemplate::findOrFail($id);
        return Inertia::render('Settings/MailTemplates/Edit', [
            'template' => $template
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = MailTemplate::findOrFail($id);
        
        $validated = $request->validate([
            'subject' => 'required|string',
            'html_template' => 'required|string',
            'text_template' => 'nullable|string',
        ]);

        $template->update($validated);

        return redirect()->route('mail-templates.index')->with('success', 'Email template updated successfully.');
    }
}
