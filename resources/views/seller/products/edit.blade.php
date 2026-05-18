@extends('seller.layout')

@section('content')
    <div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="margin: 0 0 8px; font-size: 24px; font-weight: 700; color: #1f2937;">Edit Produk</h2>
            <p style="margin: 0; color: #6b7280; font-size: 14px;">Ubah detail produk dan galeri gambar</p>
        </div>
        <div>
            <a href="{{ route('seller.products') }}" style="display: inline-flex; align-items: center; gap: 8px; background: white; color: #374151; padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div style="margin-bottom: 24px; padding: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; color: #991b1b;">
            <strong style="display: block; margin-bottom: 8px; font-size: 14px;">Gagal menyimpan:</strong>
            <ul style="margin: 0 0 0 20px; padding: 0; font-size: 13px;">
                @foreach ($errors->all() as $error)
                    <li style="margin-bottom: 4px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('seller.products.update', $product->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px;">
            <!-- Kolom Kiri: Informasi Dasar -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f3f4f6;">
                    <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 600; color: #111827;">Informasi Umum</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Nama Produk</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.2s; background: #f9fafb;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Kategori</label>
                            <select name="category_id" required style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Deskripsi</label>
                            <textarea name="description" rows="6" style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb; resize: vertical; line-height: 1.5;">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Harga, Stok, Gambar -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f3f4f6;">
                    <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 600; color: #111827;">Penjualan</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Harga (Rp)</label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" required style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Stok</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required style="width: 100%; padding: 12px 16px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 14px; outline: none; background: #f9fafb;">
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
                            <div style="position: relative; width: 44px; height: 24px;">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) style="opacity: 0; width: 0; height: 0; position: absolute;" id="status-toggle">
                                <span class="toggle-track" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ old('is_active', $product->is_active) ? '#10b981' : '#d1d5db' }}; transition: .4s; border-radius: 34px;"></span>
                                <span class="toggle-thumb" style="position: absolute; height: 18px; width: 18px; left: {{ old('is_active', $product->is_active) ? '22px' : '3px' }}; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.1);"></span>
                            </div>
                            <div>
                                <span style="font-weight: 600; font-size: 14px; color: #374151; display: block;">Status Produk</span>
                                <span style="font-size: 12px; color: #6b7280;">Aktifkan untuk menampilkan di toko</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f3f4f6;">
                    <h3 style="margin: 0 0 20px; font-size: 16px; font-weight: 600; color: #111827;">Media & Galeri</h3>
                    
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 8px;">Unggah Gambar Baru</label>
                    <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; border: 2px dashed #d1d5db; border-radius: 12px; padding: 32px 20px; background: #f9fafb; cursor: pointer; margin-bottom: 24px; transition: border-color 0.2s;">
                        <svg style="color: #9ca3af; width: 36px; height: 36px; margin-bottom: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        <span style="font-size: 14px; font-weight: 600; color: #4b5563;">Klik untuk mengunggah</span>
                        <span style="font-size: 12px; color: #6b7280; margin-top: 4px;">Mendukung multi file (JPG, PNG)</span>
                        <input type="file" name="images[]" accept="image/*" multiple style="display: none;">
                    </label>

                    @if($product->images->count() > 0)
                        <label style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 12px;">
                            <span>Galeri Saat Ini</span>
                            <span style="font-size: 11px; font-weight: 500; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 99px;">{{ $product->images->count() }} gambar</span>
                        </label>
                        <div class="seller-gallery" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                            @foreach ($product->images as $img)
                                @php
                                    $imgSrc = $img->image_url;
                                    if ($imgSrc && !str_starts_with($imgSrc, 'http')) {
                                        $imgSrc = rtrim(config('app.url'), '/') . '/' . ltrim($imgSrc, '/');
                                    }
                                @endphp
                                <div class="seller-gallery-item" data-image-id="{{ $img->id }}" style="position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; group;">
                                    <img src="{{ $imgSrc }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <div class="seller-gallery-actions" style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                                        <button type="button" class="seller-button seller-image-delete" data-image-id="{{ $img->id }}" style="background: white; color: #ef4444; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 12px;">
            <a href="{{ route('seller.products') }}" style="display: inline-flex; align-items: center; justify-content: center; background: white; color: #374151; padding: 12px 24px; border: 1px solid #d1d5db; border-radius: 12px; font-weight: 600; font-size: 14px; text-decoration: none;">Batal</a>
            <button type="submit" style="display: inline-flex; align-items: center; justify-content: center; background: #d97706; color: white; padding: 12px 32px; border: none; border-radius: 12px; font-weight: 600; font-size: 14px; cursor: pointer; box-shadow: 0 4px 12px rgba(217,119,6,0.2);">Simpan Perubahan</button>
        </div>
    </form>

    <style>
        .seller-gallery-item:hover .seller-gallery-actions { opacity: 1 !important; }
        input:focus, textarea:focus, select:focus { border-color: #d97706 !important; box-shadow: 0 0 0 3px rgba(217,119,6,0.1) !important; }
        
        /* Checkbox toggle JS logic fallback */
        #status-toggle:checked ~ .toggle-track { background-color: #10b981 !important; }
        #status-toggle:checked ~ .toggle-thumb { left: 22px !important; }
        #status-toggle:not(:checked) ~ .toggle-track { background-color: #d1d5db !important; }
        #status-toggle:not(:checked) ~ .toggle-thumb { left: 3px !important; }
    </style>

    {{-- Form urutan gambar tersembunyi (digunakan oleh JS internal jika ada) --}}
    <form id="seller-image-reorder-form" data-reorder-form data-product-id="{{ $product->id }}" style="display:none;">
        @csrf
        <input type="hidden" name="order" value="">
    </form>
@endsection
