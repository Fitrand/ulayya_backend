@extends('seller.layout')

@section('content')
    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #1f2937;">Kelola Kategori</h2>
                <p style="margin: 0; color: #6b7280; font-size: 14px;">Kelola kategori produk untuk memudahkan pengorganisasian</p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px;">
            <div style="position: relative; flex: 1;">
                <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" placeholder="Cari kategori..." style="width: 100%; padding: 12px 16px 12px 42px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; outline: none; transition: border-color 0.2s;">
            </div>
            <a href="{{ route('seller.categories.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #d97706; color: white; padding: 12px 20px; border-radius: 12px; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kategori
            </a>
        </div>

        <div style="border: 1px solid #f3f4f6; border-radius: 16px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 1px solid #f3f4f6;">
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 20%;">Nama Kategori</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 15%;">Slug</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 30%;">Deskripsi</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 15%;">Jumlah Produk</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 10%;">Status</th>
                        <th style="padding: 16px 24px; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody style="background: white;">
                    @foreach ($categoryCards as $category)
                    @php
                        $desc = 'Kue Bhoi tradisional asli Aceh yang dibuat dari bahan pilihan.';
                        $n = strtolower($category['name']);
                        if (str_contains($n, 'pandan')) {
                            $desc = 'Varian dengan aroma pandan harum';
                        } elseif (str_contains($n, 'keju')) {
                            $desc = 'Perpaduan tradisional dengan keju creamy';
                        } elseif (str_contains($n, 'cokelat')) {
                            $desc = 'Kombinasi tradisi dengan cokelat premium';
                        } elseif (str_contains($n, 'spesial')) {
                            $desc = 'Edisi khusus dan paket bundling';
                        } elseif (str_contains($n, 'klasik')) {
                            $desc = 'Kue Bhoi dengan resep tradisional';
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6; transition: background 0.2s;">
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                </div>
                                <span style="font-weight: 600; color: #374151; font-size: 14px;">{{ $category['name'] }}</span>
                            </div>
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="background: #f3f4f6; color: #4b5563; padding: 4px 8px; border-radius: 6px; font-family: monospace; font-size: 12px;">{{ $category['slug'] }}</span>
                        </td>
                        <td style="padding: 16px 24px; color: #6b7280; font-size: 14px;">
                            {{ $desc }}
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="font-weight: 600; color: #111827; font-size: 14px;">{{ $category['count'] }} produk</span>
                        </td>
                        <td style="padding: 16px 24px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 9999px; background: #dcfce7; color: #16a34a; font-size: 12px; font-weight: 600;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                Aktif
                            </span>
                        </td>
                        <td style="padding: 16px 24px;">
                            <div style="display: flex; gap: 8px;">
                                <a href="{{ route('seller.categories.edit', $category['id']) }}" style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #3b82f6; transition: background 0.2s;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('seller.categories.destroy', $category['id']) }}" method="post" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus kategori ini?')" style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; background: #fef2f2; color: #ef4444; border: none; cursor: pointer; transition: background 0.2s;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection