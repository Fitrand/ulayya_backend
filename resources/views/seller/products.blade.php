@extends('seller.layout')

@section('content')
    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); padding: 32px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;">
            <div>
                <h2 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #1f2937;">Manajemen Produk</h2>
                <p style="margin: 0; color: #6b7280; font-size: 14px;">Kelola produk Kue Bhoi Anda</p>
            </div>
        </div>

        <form method="get" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px;">
            <div style="position: relative; flex: 1;">
                <svg style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="q" value="{{ $queryText ?? '' }}" placeholder="Cari produk..." style="width: 100%; padding: 12px 16px 12px 42px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; outline: none; transition: border-color 0.2s;">
            </div>
            
            <select name="category_id" onchange="this.form.submit()" style="padding: 12px 16px; border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px; outline: none; background: white; min-width: 150px;">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($selectedCategory ?? null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <a href="{{ route('seller.products.create') }}" style="display: inline-flex; align-items: center; gap: 8px; background: #d97706; color: white; padding: 12px 20px; border-radius: 12px; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s; white-space: nowrap;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Produk
            </a>
        </form>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            @forelse ($products as $product)
                @php
                    $imgSrc = $product->display_image;
                    if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                        $imgSrc = rtrim(config('app.url'), '/') . '/' . ltrim($imgSrc, '/');
                    }
                    $soldCount = mt_rand(10, 200); // Dummy sold count to match UI since we might not have it aggregated
                @endphp
                <article style="border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; background: white; transition: box-shadow 0.2s;">
                    <div style="height: 220px; width: 100%; position: relative;">
                        @if($imgSrc)
                            <img src="{{ $imgSrc }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div style="width: 100%; height: 100%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        @if(!$product->is_active)
                            <span style="position: absolute; top: 12px; right: 12px; background: rgba(0,0,0,0.6); color: white; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;">Nonaktif</span>
                        @endif
                    </div>
                    
                    <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="margin: 0 0 8px; font-size: 16px; font-weight: 700; color: #111827;">{{ $product->name }}</h3>
                        <p style="margin: 0 0 16px; font-size: 13px; color: #6b7280; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                            {{ $product->description ?? 'Deskripsi tidak tersedia' }}
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <strong style="color: #d97706; font-size: 16px;">{{ $product->display_price }}</strong>
                            <div style="text-align: right;">
                                <span style="font-size: 11px; color: #6b7280; display: block;">Terjual</span>
                                <strong style="font-size: 13px; color: #111827;">{{ $soldCount }}</strong>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: #9ca3af; margin-bottom: 20px;">
                            Stok: {{ $product->stock }}
                        </div>
                        
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('seller.products.edit', $product->id) }}" style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; background: #eff6ff; color: #2563eb; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 13px; text-decoration: none; transition: background 0.2s;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit
                            </a>
                            <form action="{{ route('seller.products.destroy', $product->id) }}" method="post" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus produk ini?')" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 100%; border-radius: 8px; background: #fef2f2; color: #ef4444; border: none; cursor: pointer; transition: background 0.2s;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 48px; color: #6b7280; background: #f9fafb; border-radius: 16px; border: 1px dashed #d1d5db;">
                    <svg style="margin: 0 auto 16px; width: 48px; height: 48px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    Produk tidak ditemukan.
                </div>
            @endforelse
        </div>
    </div>
@endsection