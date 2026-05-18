<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'data' => $request->user()->addresses()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);
        if ($isDefault) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json([
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => $address,
        ], 201);
    }

    public function update(Request $request, int $addressId)
    {
        $address = $request->user()->addresses()->findOrFail($addressId);
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);
        if ($isDefault) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Alamat berhasil diupdate.',
            'data' => $address,
        ]);
    }

    public function destroy(Request $request, int $addressId)
    {
        $address = $request->user()->addresses()->findOrFail($addressId);
        $address->delete();

        return response()->json([
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }
}
