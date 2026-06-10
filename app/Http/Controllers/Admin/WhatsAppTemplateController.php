<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\MetaProvider;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    public function index()
    {
        $templates = WhatsAppTemplate::latest()->get();
        return view('admin.whatsapp-templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'language'     => 'required|string|max:10',
            'display_name' => 'required|string|max:150',
            'preview_text' => 'nullable|string|max:500',
            'status'       => 'required|in:active,inactive',
        ]);

        $check = (new MetaProvider())->verifyTemplate($data['name'], $data['language']);

        if ($check['exists'] === false) {
            return back()->withInput()->with('error',
                "Template \"{$data['name']}\" was not found in your Meta Business Account. Create and approve it in Meta Business Manager first, then add it here."
            );
        }

        WhatsAppTemplate::create($data);

        if ($check['exists'] === null) {
            return back()->with('success', "Template saved. (Meta verification skipped: {$check['error']})");
        }

        if ($check['status'] && $check['status'] !== 'APPROVED') {
            return back()->with('success', "Template saved, but its Meta status is \"{$check['status']}\" — blasts will fail until Meta approves it.");
        }

        return back()->with('success', 'Template verified in Meta and saved successfully.');
    }

    public function update(Request $request, WhatsAppTemplate $whatsappTemplate)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'language'     => 'required|string|max:10',
            'display_name' => 'required|string|max:150',
            'preview_text' => 'nullable|string|max:500',
            'status'       => 'required|in:active,inactive',
        ]);

        $check = (new MetaProvider())->verifyTemplate($data['name'], $data['language']);

        if ($check['exists'] === false) {
            return back()->withInput()->with('error',
                "Template \"{$data['name']}\" was not found in your Meta Business Account. Create and approve it in Meta Business Manager first."
            );
        }

        $whatsappTemplate->update($data);

        if ($check['exists'] === null) {
            return back()->with('success', "Template updated. (Meta verification skipped: {$check['error']})");
        }

        if ($check['status'] && $check['status'] !== 'APPROVED') {
            return back()->with('success', "Template updated, but its Meta status is \"{$check['status']}\" — blasts will fail until Meta approves it.");
        }

        return back()->with('success', 'Template verified in Meta and updated successfully.');
    }

    public function toggleStatus(WhatsAppTemplate $whatsappTemplate)
    {
        $whatsappTemplate->update([
            'status' => $whatsappTemplate->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Template status updated.');
    }

    public function destroy(WhatsAppTemplate $whatsappTemplate)
    {
        $whatsappTemplate->delete();
        return back()->with('success', 'Template deleted.');
    }
}
