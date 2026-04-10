<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CMSController extends Controller
{
    public function index()
    {
        return Inertia::render('CMS/Settings', [
            'settings' => Setting::all()->groupBy('group'),
            'serviceTabs' => \App\Models\ServiceTab::orderBy('sort_order')->get()
        ]);
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        foreach ($settings as $key => $value) {
            // Handle file uploads if any
            if ($request->hasFile("settings.$key")) {
                $path = $request->file("settings.$key")->store('cms', 'public');
                $value = $path; // Store relative path
            }
            
            Setting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function updateServiceTabs(Request $request)
    {
        $tabs = $request->input('tabs', []);
        
        foreach ($tabs as $index => $tabData) {
            $id = $tabData['id'] ?? null;
            $data = [
                'title' => $tabData['title'],
                'icon' => $tabData['icon'] ?? 'fa-check-circle',
                'content_title' => $tabData['content_title'],
                'content_lead' => $tabData['content_lead'],
                'content_body' => $tabData['content_body'],
                'sort_order' => $index,
            ];

            if ($id) {
                \App\Models\ServiceTab::where('id', $id)->update($data);
            } else {
                \App\Models\ServiceTab::create($data);
            }
        }

        return back()->with('success', 'Service tabs updated successfully.');
    }
}
