@extends('seller.layout')

@section('content')
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Tambah Pelanggan</h2>
                <p>Buat akun pelanggan baru untuk transaksi manual.</p>
            </div>
        </div>

        <form method="post" action="{{ route('seller.customers.store') }}" class="seller-form">
            @csrf
            <label class="seller-field">
                <span>Nama</span>
                <input type="text" name="name" value="{{ old('name') }}" @class(['is-invalid' => $errors->has('name')]) required>
                @error('name') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" @class(['is-invalid' => $errors->has('email')]) required>
                @error('email') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Telepon</span>
                <input type="text" name="phone" value="{{ old('phone') }}" @class(['is-invalid' => $errors->has('phone')])>
                @error('phone') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Password</span>
                <input type="password" name="password" @class(['is-invalid' => $errors->has('password')]) required>
                @error('password') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Konfirmasi Password</span>
                <input type="password" name="password_confirmation" required>
                @error('password_confirmation') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <div class="seller-actions">
                <button class="seller-button seller-button-primary" type="submit">Simpan</button>
                <a href="{{ route('seller.customers') }}" class="seller-button">Batal</a>
            </div>
        </form>
    </section>
@endsection
