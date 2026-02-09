<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = \App\Models\Setting::all()->pluck('value', 'key');

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('role') : null,
            ],
            'locale' => app()->getLocale(),
            'settings' => [
                'app_name' => $settings->get('app_name', 'InVault'),
                'app_logo' => $settings->get('app_logo') ? asset('storage/' . $settings->get('app_logo')) : null,
                'currency' => $settings->get('currency', 'MAD'),
                'tax_percentage' => $settings->get('tax_percentage', 20),
            ],
        ];
    }
}