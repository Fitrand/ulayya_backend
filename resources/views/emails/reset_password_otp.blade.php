<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Lupa Password</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="text-align: center; color: #FF3B00;">Ulayya Kue Bhoi</h2>
        <p>Halo,</p>
        <p>Kami menerima permintaan untuk mereset password akun Anda. Berikut adalah kode OTP rahasia Anda:</p>
        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #333; background: #f4f4f4; padding: 15px 30px; border-radius: 8px;">{{ $otp }}</span>
        </div>
        <p>Silakan masukkan kode tersebut di aplikasi untuk membuat password baru. Kode ini hanya berlaku selama 15 menit.</p>
        <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
        <hr style="border-top: 1px solid #eee; margin-top: 30px;" />
        <p style="font-size: 12px; text-align: center; color: #999;">Terima kasih,<br>Tim Ulayya</p>
    </div>
</body>
</html>
