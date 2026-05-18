@extends('seller.layout')

@section('content')
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Tambah Kategori</h2>
                <p>Buat kategori baru untuk produk.</p>
            </div>
        </div>

        <form action="{{ route('seller.categories.store') }}" method="post" class="seller-form">
            @csrf

            <label class="seller-field">
                <span>Nama</span>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </label>

            <label class="seller-field">
                <span>Slug (opsional)</span>
                <input type="text" name="slug" value="{{ old('slug') }}">
            </label>

            <div class="seller-form-actions">
                <button class="seller-button seller-button-primary" type="submit">Simpan</button>
                <a href="{{ route('seller.categories') }}" class="seller-button">Batal</a>
            </div>
        </form>
    </section>
@endsection
