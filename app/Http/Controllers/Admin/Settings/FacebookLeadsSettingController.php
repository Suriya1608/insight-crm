<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FacebookLeadsSettingController extends Controller
{
    public function show()
    {
        return view('admin.settings.facebook-leads', [
            'appId'       => Setting::get('fb_leads_app_id', ''),
            'appSecret'   => Setting::getSecure('fb_leads_app_secret', ''),
            'pageToken'   => Setting::getSecure('fb_leads_page_token', ''),
            'pageId'      => Setting::getSecure('fb_leads_page_id', ''),
            'verifyToken' => Setting::get('fb_leads_verify_token', ''),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'fb_leads_app_id'       => 'nullable|string|max:255',
            'fb_leads_app_secret'   => 'nullable|string|max:255',
            'fb_leads_page_token'   => 'nullable|string|max:1000',
            'fb_leads_page_id'      => 'nullable|string|max:255',
            'fb_leads_verify_token' => 'nullable|string|max:255',
        ]);

        Setting::set('fb_leads_app_id', $request->input('fb_leads_app_id'));
        Setting::set('fb_leads_verify_token', $request->input('fb_leads_verify_token'));

        if ($request->filled('fb_leads_app_secret')) {
            Setting::setSecure('fb_leads_app_secret', $request->input('fb_leads_app_secret'));
        }
        if ($request->filled('fb_leads_page_token')) {
            Setting::setSecure('fb_leads_page_token', $request->input('fb_leads_page_token'));
        }
        if ($request->filled('fb_leads_page_id')) {
            Setting::setSecure('fb_leads_page_id', $request->input('fb_leads_page_id'));
        }

        return redirect()->route('admin.settings.facebook-leads')
            ->with('success', 'Facebook Lead Ads settings saved.');
    }

    public function subscribeAppToPage()
    {
        $savedToken = Setting::getSecure('fb_leads_page_token', '');
        $pageId     = Setting::getSecure('fb_leads_page_id', '');

        if (! $savedToken || ! $pageId) {
            return back()->with('fb_subscribe_error', 'Page Token and Page ID must be saved first.');
        }

        // Exchange User Token → Page Access Token via /me/accounts
        $pageToken = $savedToken;
        $accountsResp = Http::timeout(15)->get('https://graph.facebook.com/v19.0/me/accounts', [
            'access_token' => $savedToken,
        ]);

        if ($accountsResp->successful()) {
            foreach ($accountsResp->json('data', []) as $page) {
                if ($page['id'] === $pageId && ! empty($page['access_token'])) {
                    $pageToken = $page['access_token'];
                    // Save the real Page Access Token so future calls work directly
                    Setting::setSecure('fb_leads_page_token', $pageToken);
                    break;
                }
            }
        }

        $response = Http::timeout(15)->post("https://graph.facebook.com/v19.0/{$pageId}/subscribed_apps", [
            'subscribed_fields' => 'leadgen',
            'access_token'      => $pageToken,
        ]);

        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            return back()->with('fb_subscribe_success', 'App successfully subscribed to the page. Leadgen webhook is active.');
        }

        $errorMsg = $body['error']['message'] ?? $response->body();
        return back()->with('fb_subscribe_error', 'Subscription failed: ' . $errorMsg);
    }
}
