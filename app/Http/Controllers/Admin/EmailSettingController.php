<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailSettingController extends Controller
{
    public function __construct(protected \App\Services\BusinessSettingService $businessSettingService) {}

    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::EMAIL_SETUP);

        $keys = [
            'mail_enabled',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ];

        return Inertia::render('Admin/Business/EmailSettings', [
            'settings' => $this->businessSettingService->getSettings($keys),
        ]);
    }

    public function update(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize(\App\Constants\Permissions::EMAIL_SETUP);

        $validated = $request->validate([
            'mail_enabled' => 'boolean',
            'mail_host' => 'exclude_if:mail_enabled,false|required|string|max:255',
            'mail_port' => 'exclude_if:mail_enabled,false|required|string|max:10',
            'mail_username' => 'exclude_if:mail_enabled,false|required|string|max:255',
            'mail_password' => 'nullable|string|max:255', // Password can be nullable on update
            'mail_encryption' => 'exclude_if:mail_enabled,false|required|string|in:tls,ssl',
            'mail_from_address' => 'exclude_if:mail_enabled,false|required|email|max:255',
            'mail_from_name' => 'exclude_if:mail_enabled,false|required|string|max:255',
        ]);

        $this->businessSettingService->updateSettings($validated);

        return back()->with('success', 'Email credentials have been updated.');
    }
}
