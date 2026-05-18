<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->latest();

        if ($keyword = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%');
            });
        }

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $customer = User::create([
            ...$validated,
            'is_admin' => false,
        ]);

        return response()->json([
            'message' => 'Pelanggan berhasil dibuat.',
            'data' => $customer,
        ], 201);
    }

    public function show(User $customer)
    {
        abort_if($customer->is_admin, 404);

        $customer->loadCount('orders');

        return response()->json([
            'data' => $customer,
        ]);
    }

    public function update(Request $request, User $customer)
    {
        abort_if($customer->is_admin, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $customer->update($data);

        return response()->json([
            'message' => 'Pelanggan berhasil diperbarui.',
            'data' => $customer->fresh(),
        ]);
    }

    public function destroy(User $customer)
    {
        abort_if($customer->is_admin, 404);

        $customer->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus.',
        ]);
    }
}
