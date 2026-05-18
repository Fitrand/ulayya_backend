<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('is_admin', false)
            ->with(['orders' => fn ($q) => $q->select('id', 'user_id', 'status', 'total_amount', 'created_at')->latest()])
            ->withCount('orders')
            ->latest();

        $keyword = trim((string) $request->query('q', ''));
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('email', 'like', '%' . $keyword . '%')
                  ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }

        // Stat totals across ALL non-admin customers
        $allCustomers   = User::where('is_admin', false)
            ->withCount('orders')
            ->with(['orders' => fn ($q) => $q->select('id', 'user_id', 'status', 'total_amount')])
            ->get();
        $totalCustomers = $allCustomers->count();
        $totalOrders    = (int) $allCustomers->sum('orders_count');
        $allRevenue     = $allCustomers->sum(fn ($c) => $c->orders->where('status', '!=', 'cancelled')->sum('total_amount'));
        $avgTransaction = $totalOrders > 0 ? $allRevenue / $totalOrders : 0;

        return view('seller.customers', [
            'pageTitle'      => 'Pelanggan',
            'pageDescription'=> 'Kelola dan pantau data pelanggan Anda',
            'customers'      => $query->paginate(15)->withQueryString(),
            'queryText'      => $keyword,
            'totalCustomers' => $totalCustomers,
            'totalOrders'    => $totalOrders,
            'avgTransaction' => 'Rp ' . number_format($avgTransaction, 0, ',', '.'),
        ]);
    }

    public function create()
    {
        return view('seller.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_admin' => false,
        ]);

        return Redirect::route('seller.customers')->with('status', 'Pelanggan berhasil ditambahkan.');
    }

    public function edit(User $pelanggan)
    {
        abort_if($pelanggan->is_admin, 404);

        return view('seller.customers.edit', [
            'customer' => $pelanggan,
        ]);
    }

    public function update(Request $request, User $pelanggan)
    {
        abort_if($pelanggan->is_admin, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $pelanggan->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $pelanggan->update($data);

        return Redirect::route('seller.customers')->with('status', 'Data pelanggan berhasil diperbarui.');
    }

    public function destroy(User $pelanggan)
    {
        abort_if($pelanggan->is_admin, 404);

        $pelanggan->delete();

        return Redirect::route('seller.customers')->with('status', 'Pelanggan berhasil dihapus.');
    }
}
