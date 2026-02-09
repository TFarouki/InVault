<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return Inertia::render('Settings/Index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.app_name' => 'nullable|string|max:255',
            'settings.currency' => 'nullable|string|max:10',
            'settings.address' => 'nullable|string',
            'settings.phone' => 'nullable|string',
            'settings.tax_percentage' => 'nullable|numeric|min:0|max:100',
            'language' => 'nullable|string|in:en,fr,ar',
            'app_logo' => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
        ]);

        // Handle language switching
        if ($request->has('language')) {
            session(['locale' => $request->language]);
            app()->setLocale($request->language);
            Setting::updateOrCreate(['key' => 'language'], ['value' => $request->language]);
        }

        // Handle settings array
        foreach ($request->input('settings', []) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Handle logo upload
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('business', 'public');
            Setting::updateOrCreate(['key' => 'app_logo'], ['value' => $path]);
        }

        return redirect()->route('settings.index')->with('success', __('Settings updated successfully.'));
    }
}