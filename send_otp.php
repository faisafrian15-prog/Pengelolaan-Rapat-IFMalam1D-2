<?php

date_default_timezone_set('Asia/Jakarta'); 
session_set_cookie_params(0, '/', '', false, true);
session_start();

require_once "Koneksi.php";

$mailUser = 'yantiindah110@gmail.com';      
$mailPass = 'blke alyo gxaw pdas';       

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
    exit("Terlalu banyak permintaan OTP");
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

date_default_timezone_set('Asia/Jakarta'); 

sleep(1);
$otp = sprintf("%06d", mt_rand(100000, 999999)); 

$_SESSION['otp_code']   = $otp;  
$_SESSION['otp_expire'] = time() + 300; 
$_SESSION['reset_user'] = $username;
$_SESSION['otp_attempt'] = 0; 

$logFile = __DIR__ . '/otp_debug.log';
$timestamp = date('Y-m-d H:i:s');
$log = "\n=== OTP GENERATED [$timestamp] ===\n";
$log .= "Session ID: " . session_id() . "\n";
$log .= "OTP Code: " . $_SESSION['otp_code'] . "\n";
$log .= "Current Time: " . time() . " (" . date('Y-m-d H:i:s') . ")\n";
$log .= "OTP Expire: " . $_SESSION['otp_expire'] . " (" . date('Y-m-d H:i:s', $_SESSION['otp_expire']) . ")\n";
$log .= "Reset User: " . $_SESSION['reset_user'] . "\n";
$log .= "Valid Duration: 300 seconds (5 minutes)\n";

error_log($log);
file_put_contents($logFile, $log, FILE_APPEND);

if (!isset($_SESSION['csrf_otp'])) {
    $_SESSION['csrf_otp'] = hash('sha256', uniqid(mt_rand(), true) . microtime(true) . session_id());
}

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
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>Reset Password</h2>
            <p>Anda menerima email ini karena ada permintaan reset password untuk akun: <strong>$username</strong></p>
            <div style='background: #f5f5f5; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                <p style='margin: 0; color: #666;'>Kode OTP Anda:</p>
                <h1 style='letter-spacing: 8px; color: #007bff; margin: 10px 0; font-size: 36px;'>$otp</h1>
            </div>
            <p style='color: #d9534f;'><strong>⚠️ Kode ini berlaku selama 5 menit</strong></p>
            <p style='color: #666; font-size: 14px;'>Jangan bagikan kode ini kepada siapa pun. Jika Anda tidak meminta reset password, abaikan email ini.</p>
            <hr style='border: none; border-top: 1px solid #ddd; margin: 20px 0;'>
            <p style='color: #999; font-size: 12px;'>Email ini dikirim otomatis, mohon tidak membalas.</p>
        </div>
    ";

    $mail->send();
    
    error_log("OTP untuk $username ($email): $otp - Expire: " . date('Y-m-d H:i:s', $_SESSION['otp_expire']));
    
    echo "OTP_SENT";

} catch (Exception $e) {
    error_log("PHPMailer Error: " . $mail->ErrorInfo);
    echo "Gagal mengirim email. Silakan coba lagi.";
}