<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ReportNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ReportNoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['required', 'in:today,week,month,all'],
            'content' => ['required', 'string'],
        ]);

        ReportNote::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return Redirect::route('seller.reports')->with('status', 'Catatan laporan berhasil dibuat.');
    }

    public function update(Request $request, ReportNote $laporan)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'period' => ['required', 'in:today,week,month,all'],
            'content' => ['required', 'string'],
        ]);

        $laporan->update($validated);

        return Redirect::route('seller.reports')->with('status', 'Catatan laporan berhasil diperbarui.');
    }

    public function destroy(ReportNote $laporan)
    {
        $laporan->delete();

        return Redirect::route('seller.reports')->with('status', 'Catatan laporan berhasil dihapus.');
    }
}
