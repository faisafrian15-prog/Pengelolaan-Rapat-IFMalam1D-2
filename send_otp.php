<?php

date_default_timezone_set('Asia/Jakarta'); 
session_set_cookie_params(0, '/', '', false, true);
session_start();

require_once "Koneksi.php";

$config = require_once __DIR__ . '/config.php';
$mailUser = $config['MAIL_USER'];
$mailPass = $config['MAIL_PASS'];       

if ($mailUser == '' || $mailPass == '') {
    exit("Konfigurasi email belum lengkap");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    exit("Method not allowed");
}

if (!isset($_SESSION['otp_time']) || (time() - $_SESSION['otp_time']) > 600) {
    $_SESSION['otp_time'] = time();
    $_SESSION['otp_request'] = 0;
}

$_SESSION['otp_request']++;

if ($_SESSION['otp_request'] > 5) {
    exit("Terlalu banyak permintaan OTP. Coba lagi dalam beberapa menit.");
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email    = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($username == '' || $email == '') {
    exit("Data tidak lengkap");
}

$stmt = $koneksi->prepare("SELECT email FROM users WHERE username = ? LIMIT 1");
if (!$stmt) {
    exit("Query error");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($db_email);

if (!$stmt->fetch()) {
    exit("Akun tidak valid");
}
$stmt->close();

if ($db_email !== $email) {
    exit("Akun tidak valid");
}

sleep(1);
$otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$current_time = time();
$expire_duration = 300; 
$otp_expire = $current_time + $expire_duration;

$_SESSION['otp_code']   = $otp;  
$_SESSION['otp_expire'] = $otp_expire;
$_SESSION['otp_generated_at'] = $current_time;
$_SESSION['reset_user'] = $username;
$_SESSION['otp_attempt'] = 0; 

if (!isset($_SESSION['csrf_otp'])) {
$_SESSION['csrf_otp'] = hash('sha256', uniqid() . microtime() . rand());}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailUser;
    $mail->Password   = $mailPass;
    $mail->SMTPSecure = 'tls'; 
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($mailUser, 'Reset Password');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Kode OTP Reset Password';
    
    $expire_date_formatted = date('d/m/Y H:i:s', $otp_expire);
    $safe_username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>Reset Password</h2>
            <p>Anda menerima email ini karena ada permintaan reset password untuk akun: <strong>{$safe_username}</strong></p>
            <div style='background: #f5f5f5; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                <p style='margin: 0; color: #666;'>Kode OTP Anda:</p>
                <h1 style='letter-spacing: 8px; color: #007bff; margin: 10px 0; font-size: 36px;'>{$otp}</h1>
            </div>
            <p style='color: #d9534f;'><strong>⚠️ Kode ini berlaku selama 5 menit</strong></p>
            <p style='color: #666; font-size: 14px;'>Kode akan expired pada: <strong>{$expire_date_formatted} WIB</strong></p>
            <p style='color: #666; font-size: 14px;'>Jangan bagikan kode ini kepada siapa pun. Jika Anda tidak meminta reset password, abaikan email ini.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='color: #999; font-size: 12px;'>Email ini dikirim otomatis, mohon tidak membalas.</p>
        </div>
    ";

    $mail->send();
    echo "OTP_SENT";

} catch (Exception $e) {
    error_log("Failed to send OTP: " . $mail->ErrorInfo);
    echo "Gagal mengirim email. Silakan coba lagi.";
}
?>