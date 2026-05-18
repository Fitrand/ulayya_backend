@extends('seller.layout')

@section('content')
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Edit Kategori</h2>
                <p>Ubah nama atau slug kategori.</p>
            </div>
        </div>

        <form action="{{ route('seller.categories.update', $category->id) }}" method="post" class="seller-form">
            @csrf
            @method('PUT')

            <label class="seller-field">
                <span>Nama</span>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required>
            </label>

            <label class="seller-field">
                <span>Slug (opsional)</span>
                <input type="text" name="slug" value="{{ old('slug', $category->slug) }}">
            </label>

            <div class="seller-form-actions">
                <button class="seller-button seller-button-primary" type="submit">Simpan</button>
                <a href="{{ route('seller.categories') }}" class="seller-button">Batal</a>
            </div>
        </form>
    </section>
@endsection
