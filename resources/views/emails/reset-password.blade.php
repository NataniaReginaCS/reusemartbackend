<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f2f2f2; padding: 20px;">
    <div style="max-width: 600px; margin: auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1F510F;">ReUseMart - Reset Password</h2>
        <p>Halo,</p>
        <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetLink }}" style="background-color: #1F510F; color: white; padding: 12px 20px; border-radius: 5px; text-decoration: none;">Reset Password</a>
        </p>
        <p>Jika Anda tidak meminta reset password, silakan abaikan email ini.</p>
        <br>
        <p>Terima kasih,</p>
        <p><strong>ReUseMart Team</strong></p>
        <hr>
        <p style="font-size: 12px; color: #888;">&copy; {{ date('Y') }} ReUseMart. All rights reserved.</p>
    </div>
</body>
</html>
