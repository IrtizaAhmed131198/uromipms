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

    public function barcodeScanner()
    {
        return view('pwa.barcode_scanner');
    }

    public function barcodeProductLookup(Request $request, \App\Utils\ProductUtil $productUtil)
    {
        $business_id = $request->session()->get('user.business_id');
        if (empty($business_id)) {
            return response()->json(['success' => false, 'msg' => 'Unauthorized']);
        }

        $sku = $request->input('sku');
        if (empty($sku)) {
            return response()->json(['success' => false, 'msg' => 'SKU is required']);
        }

        // Use existing filterProduct logic
        $result = $productUtil->filterProduct($business_id, $sku, null, false, null, [], ['sub_sku'], false, 'exact')->first();

        if (empty($result)) {
            return response()->json(['success' => false, 'msg' => 'Product not found for SKU: ' . $sku]);
        }

        // Format result for frontend
        $productData = [
            'name' => $result->name,
            'sku' => $result->sub_sku,
            'price' => session('currency')['symbol'] . ' ' . number_format((float) $result->sell_price_inc_tax, 2),
            'stock' => $result->qty_available ?? 0,
            'category' => $result->category_name ?? '',
            'unit' => $result->unit_name ?? '',
            'url' => action([\App\Http\Controllers\ProductController::class, 'view'], [$result->product_id])
        ];

        if ($result->type == 'variable') {
            $productData['name'] .= ' - ' . $result->variation_name;
        }

        return response()->json([
            'success' => true,
            'product' => $productData
        ]);
    }
}
