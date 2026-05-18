@extends('seller.layout')

@section('content')
<div class="settings-page">
    <section class="settings-stack">
        <article class="settings-card settings-card--store">
            <div class="settings-card-head">
                <div class="settings-card-icon settings-card-icon--amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                </div>
                <div>
                    <h2 class="settings-card-title">Informasi Toko</h2>
                </div>
            </div>

            <form method="post" action="{{ route('seller.settings.update') }}" class="settings-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="form_type" value="store_info">

                <div class="settings-field">
                    <label class="settings-label" for="shop_name">Nama Toko</label>
                    <input class="settings-input @error('shop_name') is-error @enderror"
                           id="shop_name" type="text" name="shop_name"
                           value="{{ old('shop_name', $storeProfile->shop_name ?? 'Kue Bhoi') }}">
                    @error('shop_name')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-field">
                    <label class="settings-label" for="shop_description">Deskripsi Toko</label>
                    <textarea class="settings-input settings-textarea @error('shop_description') is-error @enderror"
                              id="shop_description" name="shop_description" rows="4">{{ old('shop_description', $storeProfile->shop_description ?? 'Kue tradisional Aceh dengan resep turun temurun') }}</textarea>
                    @error('shop_description')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-grid-2">
                    <div class="settings-field">
                        <label class="settings-label" for="shop_phone">Nomor Telepon</label>
                        <input class="settings-input @error('shop_phone') is-error @enderror"
                               id="shop_phone" type="text" name="shop_phone"
                               value="{{ old('shop_phone', $storeProfile->shop_phone ?? '08123456789') }}">
                        @error('shop_phone')<span class="settings-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="settings-field">
                        <label class="settings-label" for="shop_email">Email</label>
                        <input class="settings-input @error('shop_email') is-error @enderror"
                               id="shop_email" type="email" name="shop_email"
                               value="{{ old('shop_email', $storeProfile->shop_email ?? 'info@kuebhoi.com') }}">
                        @error('shop_email')<span class="settings-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="settings-field">
                    <label class="settings-label" for="shop_address">Alamat Toko</label>
                    <textarea class="settings-input settings-textarea @error('shop_address') is-error @enderror"
                              id="shop_address" name="shop_address" rows="3">{{ old('shop_address', $storeProfile->shop_address ?? 'Jl. Raya Banda Aceh No. 123') }}</textarea>
                    @error('shop_address')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                {{-- Hidden inputs for coordinates --}}
                <input type="hidden" id="shop_latitude" name="shop_latitude"
                       value="{{ old('shop_latitude', $storeProfile->shop_latitude ?? '') }}">
                <input type="hidden" id="shop_longitude" name="shop_longitude"
                       value="{{ old('shop_longitude', $storeProfile->shop_longitude ?? '') }}">

                {{-- Map Picker --}}
                <div class="settings-field">
                    <div class="map-picker-label-row">
                        <label class="settings-label">Titik Lokasi Toko di Peta</label>
                        <span id="map-coords-display" class="map-coords-badge"
                              style="{{ ($storeProfile->shop_latitude ?? null) ? '' : 'display:none' }}">
                            📍 {{ number_format($storeProfile->shop_latitude ?? 0, 6) }},
                               {{ number_format($storeProfile->shop_longitude ?? 0, 6) }}
                        </span>
                    </div>
                    <p class="settings-hint">Geser pin untuk mengatur koordinat toko. Koordinat ini digunakan sebagai titik awal perhitungan ongkos kirim.</p>
                    <div id="store-map" class="store-map-container"></div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="settings-button settings-button--primary">Simpan Perubahan</button>
                </div>
            </form>
        </article>

        <article class="settings-card settings-card--notifikasi">
            <div class="settings-card-head">
                <div class="settings-card-icon settings-card-icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                </div>
                <div>
                    <h2 class="settings-card-title">Notifikasi</h2>
                </div>
            </div>

            <form method="post" action="{{ route('seller.settings.update') }}" class="settings-switch-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="form_type" value="notifications">

                <label class="settings-switch-row" for="email_notifications">
                    <div class="settings-switch-copy">
                        <span class="settings-switch-title">Notifikasi Email</span>
                        <span class="settings-switch-desc">Terima notifikasi via email</span>
                    </div>
                    <span class="settings-switch-control">
                        <input class="settings-switch-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" @checked(old('email_notifications', $storeProfile->email_notifications ?? true))>
                        <span class="settings-switch-track" aria-hidden="true"></span>
                    </span>
                </label>

                <label class="settings-switch-row" for="order_notifications">
                    <div class="settings-switch-copy">
                        <span class="settings-switch-title">Pesanan Baru</span>
                        <span class="settings-switch-desc">Notifikasi saat ada pesanan baru</span>
                    </div>
                    <span class="settings-switch-control">
                        <input class="settings-switch-input" type="checkbox" id="order_notifications" name="order_notifications" value="1" @checked(old('order_notifications', $storeProfile->order_notifications ?? true))>
                        <span class="settings-switch-track" aria-hidden="true"></span>
                    </span>
                </label>

                <label class="settings-switch-row" for="low_stock_notifications">
                    <div class="settings-switch-copy">
                        <span class="settings-switch-title">Stok Menipis</span>
                        <span class="settings-switch-desc">Notifikasi saat stok produk menipis</span>
                    </div>
                    <span class="settings-switch-control">
                        <input class="settings-switch-input" type="checkbox" id="low_stock_notifications" name="low_stock_notifications" value="1" @checked(old('low_stock_notifications', $storeProfile->low_stock_notifications ?? true))>
                        <span class="settings-switch-track" aria-hidden="true"></span>
                    </span>
                </label>
            </form>
        </article>

        <article class="settings-card settings-card--payment">
            <div class="settings-card-head">
                <div class="settings-card-icon settings-card-icon--mint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                        <line x1="1" y1="10" x2="23" y2="10"/>
                    </svg>
                </div>
                <div>
                    <h2 class="settings-card-title">Pembayaran</h2>
                </div>
            </div>

            <form method="post" action="{{ route('seller.settings.update') }}" class="settings-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="form_type" value="payment_info">

                <div class="settings-field">
                    <label class="settings-label" for="bank_account_number">Nomor Rekening</label>
                    <input class="settings-input @error('bank_account_number') is-error @enderror"
                           id="bank_account_number" type="text" name="bank_account_number"
                           value="{{ old('bank_account_number', $storeProfile->bank_account_number ?? '1234567890') }}">
                    @error('bank_account_number')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-grid-2">
                    <div class="settings-field">
                        <label class="settings-label" for="bank_name">Bank</label>
                        <select class="settings-input settings-select @error('bank_name') is-error @enderror" id="bank_name" name="bank_name">
                            @php
                                $banks = ['Bank BCA', 'Bank BNI', 'Bank BRI', 'Bank Mandiri', 'Bank CIMB Niaga', 'Bank Danamon', 'Bank Permata', 'Bank BTN', 'Bank BSI'];
                                $selectedBank = old('bank_name', $storeProfile->bank_name ?? 'Bank BCA');
                            @endphp
                            @foreach ($banks as $bank)
                                <option value="{{ $bank }}" @selected($selectedBank === $bank)>{{ $bank }}</option>
                            @endforeach
                        </select>
                        @error('bank_name')<span class="settings-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="settings-field">
                        <label class="settings-label" for="bank_account_name">Atas Nama</label>
                        <input class="settings-input @error('bank_account_name') is-error @enderror"
                               id="bank_account_name" type="text" name="bank_account_name"
                               value="{{ old('bank_account_name', $storeProfile->bank_account_name ?? 'Kue Bhoi') }}">
                        @error('bank_account_name')<span class="settings-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="settings-actions">
                    <button type="submit" class="settings-button settings-button--primary">Simpan Perubahan</button>
                </div>
            </form>
        </article>

        <article class="settings-card settings-card--security">
            <div class="settings-card-head">
                <div class="settings-card-icon settings-card-icon--rose">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="settings-card-title">Keamanan</h2>
                </div>
            </div>

            <form method="post" action="{{ route('seller.settings.update') }}" class="settings-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="form_type" value="security">

                <div class="settings-field">
                    <label class="settings-label" for="current_password">Password Lama</label>
                    <input class="settings-input @error('current_password') is-error @enderror"
                           id="current_password" type="password" name="current_password"
                           placeholder="Masukkan password lama">
                    @error('current_password')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-field">
                    <label class="settings-label" for="new_password">Password Baru</label>
                    <input class="settings-input @error('new_password') is-error @enderror"
                           id="new_password" type="password" name="new_password"
                           placeholder="Masukkan password baru">
                    @error('new_password')<span class="settings-error">{{ $message }}</span>@enderror
                </div>

                <div class="settings-field">
                    <label class="settings-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
                    <input class="settings-input"
                           id="new_password_confirmation" type="password" name="new_password_confirmation"
                           placeholder="Konfirmasi password baru">
                </div>

                <div class="settings-actions">
                    <button type="submit" class="settings-button settings-button--danger">Ubah Password</button>
                </div>
            </form>
        </article>
    </section>
</div>

<style>
.settings-page {
    max-width: 760px;
}

.settings-stack {
    display: grid;
    gap: 20px;
}

.settings-card {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(109, 74, 31, 0.12);
    border-radius: 20px;
    padding: 22px 24px 24px;
    box-shadow: 0 10px 30px rgba(78, 43, 17, 0.06);
    backdrop-filter: blur(10px);
}

.settings-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}

.settings-card-title {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f1a17;
}

.settings-card-icon {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.settings-card-icon svg {
    width: 16px;
    height: 16px;
}

.settings-card-icon--amber {
    background: #fff2de;
    color: #f08b00;
}

.settings-card-icon--blue {
    background: #e8f0ff;
    color: #3b82f6;
}

.settings-card-icon--mint {
    background: #e7faf2;
    color: #10b981;
}

.settings-card-icon--rose {
    background: #fdecec;
    color: #ef4444;
}

.settings-form,
.settings-switch-form {
    display: grid;
    gap: 16px;
}

.settings-field {
    display: grid;
    gap: 6px;
}

.settings-label {
    font-size: 0.84rem;
    font-weight: 600;
    color: #4f4740;
}

.settings-grid-2 {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.settings-input {
    width: 100%;
    border: 1px solid rgba(169, 149, 130, 0.42);
    border-radius: 10px;
    padding: 10px 14px;
    color: #201913;
    background: #fff;
    font-size: 0.94rem;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
    box-sizing: border-box;
}

.settings-input:focus {
    border-color: #f08b00;
    box-shadow: 0 0 0 3px rgba(240, 139, 0, 0.12);
}

.settings-input.is-error {
    border-color: #ef4444;
}

.settings-textarea {
    min-height: 92px;
    resize: vertical;
}

.settings-select {
    appearance: auto;
    cursor: pointer;
}

.settings-error {
    color: #ef4444;
    font-size: 0.8rem;
    font-weight: 500;
}

.settings-actions {
    margin-top: 2px;
}

.settings-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    padding: 10px 18px;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.12s ease, opacity 0.12s ease;
}

.settings-button:hover {
    opacity: 0.92;
}

.settings-button:active {
    transform: translateY(1px);
}

.settings-button--primary {
    background: linear-gradient(135deg, #f59e0b, #e67e22);
    color: #fff;
    box-shadow: 0 8px 18px rgba(230, 126, 34, 0.24);
}

.settings-button--danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    box-shadow: 0 8px 18px rgba(220, 38, 38, 0.22);
}

.settings-switch-form {
    gap: 0;
}

.settings-switch-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 10px 0 14px;
    border-bottom: 1px solid #f1ece4;
    cursor: pointer;
}

.settings-switch-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.settings-switch-copy {
    display: grid;
    gap: 2px;
}

.settings-switch-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1f1a17;
}

.settings-switch-desc {
    font-size: 0.8rem;
    color: #7d6c5b;
}

.settings-switch-control {
    position: relative;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
}

.settings-switch-input {
    position: absolute;
    opacity: 0;
    inset: 0;
    width: 0;
    height: 0;
}

.settings-switch-track {
    width: 36px;
    height: 20px;
    border-radius: 999px;
    background: #d8cec1;
    position: relative;
    transition: background 0.22s ease;
}

.settings-switch-track::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.22);
    transition: left 0.22s ease;
}

.settings-switch-input:checked + .settings-switch-track {
    background: linear-gradient(135deg, #f59e0b, #e67e22);
}

.settings-switch-input:checked + .settings-switch-track::after {
    left: 18px;
}

@media (max-width: 640px) {
    .settings-page {
        max-width: 100%;
    }

    .settings-card {
        padding: 18px 18px 20px;
    }

    .settings-grid-2 {
        grid-template-columns: 1fr;
    }

    .settings-switch-row {
        gap: 12px;
    }
}

/* Map Picker */
.store-map-container {
    height: 280px;
    border-radius: 12px;
    border: 1px solid rgba(169, 149, 130, 0.42);
    overflow: hidden;
    margin-top: 6px;
}
.map-picker-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.map-coords-badge {
    font-size: 0.78rem;
    font-weight: 600;
    color: #15803d;
    background: #dcfce7;
    border: 1px solid #bbf7d0;
    border-radius: 999px;
    padding: 3px 10px;
    white-space: nowrap;
}
.settings-hint {
    margin: 0 0 4px;
    font-size: 0.8rem;
    color: #7d6c5b;
    line-height: 1.4;
}
</style>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.querySelectorAll('.settings-switch-input').forEach(function (input) {
    input.addEventListener('change', function () {
        var form = this.closest('form');
        if (!form) {
            return;
        }

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: formData
        });
    });
});

// ── Store Location Map Picker ──────────────────────────────────
(function () {
    const existingLat  = parseFloat('{{ $storeProfile->shop_latitude ?? "" }}') || 5.1801;
    const existingLng  = parseFloat('{{ $storeProfile->shop_longitude ?? "" }}') || 97.1419;
    const defaultZoom  = {{ ($storeProfile->shop_latitude ?? null) ? 15 : 13 }};

    const map = L.map('store-map').setView([existingLat, existingLng], defaultZoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Custom orange store marker
    const storeIcon = L.divIcon({
        className: '',
        html: `<div style="
            width:36px;height:36px;border-radius:50%;
            background:#f59e0b;border:3px solid #fff;
            box-shadow:0 2px 8px rgba(0,0,0,.3);
            display:flex;align-items:center;justify-content:center;
            font-size:18px;cursor:grab;
        ">🏪</div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
    });

    const marker = L.marker([existingLat, existingLng], {
        draggable: true,
        icon: storeIcon,
    }).addTo(map);

    function updateCoords(lat, lng) {
        document.getElementById('shop_latitude').value  = lat.toFixed(7);
        document.getElementById('shop_longitude').value = lng.toFixed(7);

        const badge = document.getElementById('map-coords-display');
        badge.style.display = '';
        badge.textContent = '📍 ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
    }

    // Initialize with existing coords
    if ({{ ($storeProfile->shop_latitude ?? null) ? 'true' : 'false' }}) {
        updateCoords(existingLat, existingLng);
    }

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    // Click on map to move marker
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });
})();
</script>
@endpush

@endsection