<?php

namespace App\Http\Controllers;

use App\Business;
use Illuminate\Http\Request;

class PwaController extends Controller
{
    public function manifest(Request $request)
    {
        $business = null;

        // Try session first (authenticated user)
        $business_id = session('user.business_id');

        // Fallback: pick the first business in the DB
        if (!$business_id) {
            $business = Business::first();
        } else {
            $business = Business::find($business_id);
        }

        $name = $business->name ?? 'Innfusion';

        $manifest = [
            'name'                        => $name,
            'short_name'                  => $name,
            'description'                 => 'Hotel Management System',
            'start_url'                   => '/',
            'scope'                       => '/',
            'display'                     => 'standalone',
            'orientation'                 => 'portrait',
            'background_color'            => '#ffffff',
            'theme_color'                 => '#0d6efd',
            'prefer_related_applications' => false,
            'icons'                       => $this->icons(),
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-store');
    }

    private function icons(): array
    {
        $icons = [];

        foreach ([72, 96, 128, 144, 152, 192, 384, 512] as $size) {
            $icons[] = [
                'src' => asset("images/icons/icon-{$size}x{$size}.png"),
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        return $icons;
    }
}
