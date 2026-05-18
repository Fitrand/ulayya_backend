@extends('seller.layout')

@section('content')
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Tambah Produk</h2>
                <p>Tambahkan produk baru ke toko Anda.</p>
            </div>
        </div>

        <form action="{{ route('seller.products.store') }}" method="post" enctype="multipart/form-data" class="seller-form">
            @csrf

            <label class="seller-field">
                <span>Nama</span>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </label>

            <label class="seller-field">
                <span>Kategori</span>
                <select name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="seller-field">
                <span>Deskripsi</span>
                <textarea name="description">{{ old('description') }}</textarea>
            </label>

            <label class="seller-field">
                <span>Harga</span>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" required>
            </label>

            <label class="seller-field">
                <span>Stok</span>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" required>
            </label>

            <label class="seller-field">
                <span>Gambar (bisa multiple)</span>
                <input type="file" name="images[]" accept="image/*" multiple>
            </label>

            <label class="seller-field-inline">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active'))> Aktif
            </label>

            <div class="seller-form-actions">
                <button class="seller-button seller-button-primary" type="submit">Simpan</button>
                <a href="{{ route('seller.products') }}" class="seller-button">Batal</a>
            </div>
        </form>
    </section>
@endsection
