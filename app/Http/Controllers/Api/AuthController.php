<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPasswordOtp;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($validated);
        Cart::firstOrCreate(['user_id' => $user->id]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Register success',
            'token' => $token,
            'data' => array_merge($user->toArray(), [
                'avatar_url' => $user->avatar
                    ? url('storage/' . $user->avatar)
                    : null,
            ]),
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;
        Cart::firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'data' => array_merge($user->toArray(), [
                'avatar_url' => $user->avatar
                    ? url('storage/' . $user->avatar)
                    : null,
            ]),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => array_merge($user->toArray(), [
                'avatar_url' => $user->avatar
                    ? url('storage/' . $user->avatar)
                    : null,
            ]),
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Hapus avatar lama jika ada
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => array_merge($user->fresh()->toArray(), [
                'avatar_url' => url('storage/' . $path),
            ]),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'data' => array_merge($user->fresh()->toArray(), [
                'avatar_url' => $user->avatar
                    ? url('storage/' . $user->avatar)
                    : null,
            ]),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak ditemukan.'],
            ]);
        }

        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));

        // Delete old OTPs
        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        // Insert new OTP
        DB::table('password_reset_tokens')->insert([
            'email'      => $user->email,
            'token'      => $otp,
            'created_at' => now(),
        ]);

        // Send Email
        Mail::to($user->email)->send(new ResetPasswordOtp($otp));

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke email Anda.',
        ]);
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'otp'      => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->otp)
            ->first();

        if (! $resetRecord) {
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP salah atau tidak ditemukan.'],
            ]);
        }

        // Cek kedaluwarsa (15 menit)
        if (\Carbon\Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            throw ValidationException::withMessages([
                'otp' => ['Kode OTP sudah kedaluwarsa.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->update([
                'password' => $request->password,
            ]);
        }

        // Hapus token setelah berhasil
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login dengan password baru.',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout success',
        ]);
    }
}
