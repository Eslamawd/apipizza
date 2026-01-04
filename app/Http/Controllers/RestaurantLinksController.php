<?php

namespace App\Http\Controllers;

use App\Models\RestaurantLinks;
use Illuminate\Http\Request;

class RestaurantLinksController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'google_review' => 'nullable|string',
            'facebook'      => 'nullable|string',
            'instagram'     => 'nullable|string',
            'tiktok'        => 'nullable|string',
            'website'       => 'nullable|string',
        ]);

        // ❗ مهم: متحطش restaurant_id جوه البيانات اللي بتتحدث
        $data = collect($validated)->except('restaurant_id')->toArray();

        $links = RestaurantLinks::updateOrCreate(
            ['restaurant_id' => $validated['restaurant_id']],
            $data
        );

        return response()->json([
            'message' => 'Links updated successfully',
            'links' => $links
        ]);
    }
}
