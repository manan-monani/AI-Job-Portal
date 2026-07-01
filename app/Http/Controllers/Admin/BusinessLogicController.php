<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBusinessSettingsRequest;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BusinessLogicController extends Controller
{
    public function __construct(protected \App\Services\BusinessSettingService $businessSettingService) {}

    public function index()
    {
        Gate::authorize(Permissions::BUSINESS_LOGIC);

        $settings = $this->businessSettingService->getSettings(['currency_symbol', 'timezone', 'country', 'language']);

        return Inertia::render('Admin/Business/BusinessLogic', [
            'settings' => [
                'currency_symbol' => $settings->get('currency_symbol', '$'),
                'timezone' => $settings->get('timezone', 'UTC'),
                'country' => $settings->get('country', ''),
                'language' => $settings->get('language', 'en'),
            ],
        ]);
    }

    public function update(UpdateBusinessSettingsRequest $request)
    {
        Gate::authorize(Permissions::BUSINESS_LOGIC);

        $validated = $request->validated();

        $this->businessSettingService->updateSettings($validated);

        return back()->with('success', 'Business settings updated successfully.');
    }
}
