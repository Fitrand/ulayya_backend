<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\ReportNote;
use Illuminate\Http\Request;

class ReportNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = ReportNote::query()->with('author')->latest();

        if ($period = $request->query('period')) {
            $query->where('period', $period);
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['required', 'in:today,week,month,all'],
            'content' => ['required', 'string'],
        ]);

        $note = ReportNote::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Catatan laporan berhasil dibuat.',
            'data' => $note->load('author'),
        ], 201);
    }

    public function show(ReportNote $report)
    {
        return response()->json([
            'data' => $report->load('author'),
        ]);
    }

    public function update(Request $request, ReportNote $report)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['required', 'in:today,week,month,all'],
            'content' => ['required', 'string'],
        ]);

        $report->update($validated);

        return response()->json([
            'message' => 'Catatan laporan berhasil diperbarui.',
            'data' => $report->fresh()->load('author'),
        ]);
    }

    public function destroy(ReportNote $report)
    {
        $report->delete();

        return response()->json([
            'message' => 'Catatan laporan berhasil dihapus.',
        ]);
    }
}
