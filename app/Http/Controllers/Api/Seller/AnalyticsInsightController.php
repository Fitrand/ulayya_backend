<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsInsight;
use Illuminate\Http\Request;

class AnalyticsInsightController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => AnalyticsInsight::query()->with('author')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'metric' => ['required', 'string', 'max:100'],
            'value' => ['required', 'numeric'],
            'trend' => ['required', 'in:up,down,stable'],
            'note' => ['nullable', 'string'],
        ]);

        $insight = AnalyticsInsight::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Insight analytics berhasil dibuat.',
            'data' => $insight->load('author'),
        ], 201);
    }

    public function show(AnalyticsInsight $insight)
    {
        return response()->json([
            'data' => $insight->load('author'),
        ]);
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

        return response()->json([
            'message' => 'Insight analytics berhasil diperbarui.',
            'data' => $insight->fresh()->load('author'),
        ]);
    }

    public function destroy(AnalyticsInsight $insight)
    {
        $insight->delete();

        return response()->json([
            'message' => 'Insight analytics berhasil dihapus.',
        ]);
    }
}
