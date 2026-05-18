@extends('seller.layout')

@section('content')
    <section class="seller-card">
        <div class="seller-section-head">
            <div>
                <h2>Edit Pelanggan</h2>
                <p>Perbarui data akun pelanggan.</p>
            </div>
        </div>

        <form method="post" action="{{ route('seller.customers.update', $customer->id) }}" class="seller-form">
            @csrf
            @method('PUT')
            <label class="seller-field">
                <span>Nama</span>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" @class(['is-invalid' => $errors->has('name')]) required>
                @error('name') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" @class(['is-invalid' => $errors->has('email')]) required>
                @error('email') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Telepon</span>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" @class(['is-invalid' => $errors->has('phone')])>
                @error('phone') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Password Baru (opsional)</span>
                <input type="password" name="password" @class(['is-invalid' => $errors->has('password')])>
                @error('password') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <label class="seller-field">
                <span>Konfirmasi Password Baru</span>
                <input type="password" name="password_confirmation">
                @error('password_confirmation') <small class="seller-field-error">{{ $message }}</small> @enderror
            </label>
            <div class="seller-actions">
                <button class="seller-button seller-button-primary" type="submit">Update</button>
                <a href="{{ route('seller.customers') }}" class="seller-button">Batal</a>
            </div>
        </form>
    </section>
@endsection
