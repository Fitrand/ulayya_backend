<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsInsight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AnalyticsInsightController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'metric' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric'],
            'trend' => ['required', 'in:up,down,stable'],
            'note' => ['nullable', 'string'],
        ]);

        AnalyticsInsight::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return Redirect::route('seller.analytics')->with('status', 'Insight analytics berhasil dibuat.');
    }

    public function update(Request $request, AnalyticsInsight $insight)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'metric' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric'],
            'trend' => ['required', 'in:up,down,stable'],
            'note' => ['nullable', 'string'],
        ]);

        $insight->update($validated);

        return Redirect::route('seller.analytics')->with('status', 'Insight analytics berhasil diperbarui.');
    }

    public function destroy(AnalyticsInsight $insight)
    {
        $insight->delete();

        return Redirect::route('seller.analytics')->with('status', 'Insight analytics berhasil dihapus.');
    }
}
