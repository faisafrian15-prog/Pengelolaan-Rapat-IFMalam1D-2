<?php

date_default_timezone_set('Asia/Jakarta'); 

session_set_cookie_params(0, '/', '', false, true);
session_start();

$logFile = __DIR__ . '/otp_debug.log';

function debugLog($message) {
    global $DEBUG, $logFile;
    if ($DEBUG) {
        $timestamp = date('Y-m-d H:i:s');
        $log = "[$timestamp] $message\n";
        error_log($log);
        file_put_contents($logFile, $log, FILE_APPEND);
    }
}

debugLog("=== VERIFY OTP START ===");
debugLog("Request Method: " . $_SERVER['REQUEST_METHOD']);
debugLog("Session ID: " . session_id());
debugLog("Current Time: " . time() . " (" . date('Y-m-d H:i:s') . ")");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    debugLog("ERROR: Method not POST");
    exit("Method not allowed");
}

debugLog("Session Data:");
debugLog("  - otp_code: " . (isset($_SESSION['otp_code']) ? $_SESSION['otp_code'] : 'NOT SET'));
debugLog("  - otp_expire: " . (isset($_SESSION['otp_expire']) ? $_SESSION['otp_expire'] . " (" . date('Y-m-d H:i:s', $_SESSION['otp_expire']) . ")" : 'NOT SET'));
debugLog("  - reset_user: " . (isset($_SESSION['reset_user']) ? $_SESSION['reset_user'] : 'NOT SET'));
debugLog("  - csrf_otp: " . (isset($_SESSION['csrf_otp']) ? 'SET (' . strlen($_SESSION['csrf_otp']) . ' chars)' : 'NOT SET'));
debugLog("  - otp_attempt: " . (isset($_SESSION['otp_attempt']) ? $_SESSION['otp_attempt'] : 'NOT SET'));

debugLog("POST Data:");
debugLog("  - otp: " . (isset($_POST['otp']) ? $_POST['otp'] : 'NOT SET'));
debugLog("  - csrf: " . (isset($_POST['csrf']) ? 'SET (' . strlen($_POST['csrf']) . ' chars)' : 'NOT SET'));

if ($DEBUG) {
    debugLog("SKIPPING CSRF validation for debug");
} else {
    if (
        empty($_POST['csrf']) ||
        empty($_SESSION['csrf_otp']) ||
        !hash_equals($_SESSION['csrf_otp'], $_POST['csrf'])
    ) {
        debugLog("ERROR: CSRF validation failed");
        exit("Invalid CSRF token");
    }
}

if (empty($_SESSION['otp_code'])) {
    debugLog("ERROR: Session otp_code is empty");
    exit("OTP tidak ditemukan. Silakan minta OTP baru.");
}

if (empty($_SESSION['otp_expire'])) {
    debugLog("ERROR: Session otp_expire is empty");
    exit("Data OTP tidak lengkap. Silakan minta OTP baru.");
}

if (empty($_SESSION['reset_user'])) {
    debugLog("ERROR: Session reset_user is empty");
    exit("Data user tidak ditemukan. Silakan minta OTP baru.");
}

$currentTime = time();
$expireTime = (int)$_SESSION['otp_expire'];
$sisaDetik = $expireTime - $currentTime;

debugLog("Time Check:");
debugLog("  - Current: $currentTime (" . date('Y-m-d H:i:s', $currentTime) . ")");
debugLog("  - Expire: $expireTime (" . date('Y-m-d H:i:s', $expireTime) . ")");
debugLog("  - Sisa: $sisaDetik detik");

if ($sisaDetik <= 0) {
    debugLog("ERROR: OTP expired! Difference: $sisaDetik seconds");
    
    unset($_SESSION['otp_code'], $_SESSION['otp_expire'], $_SESSION['otp_attempt'], $_SESSION['reset_user']);
    exit("OTP sudah kadaluarsa (" . abs($sisaDetik) . " detik yang lalu). Silakan minta OTP baru.");
}

if (!isset($_SESSION['otp_attempt'])) {
    $_SESSION['otp_attempt'] = 0;
}

$_SESSION['otp_attempt']++;
debugLog("OTP Attempt: " . $_SESSION['otp_attempt'] . " / 5");

if ($_SESSION['otp_attempt'] > 5) {
    debugLog("ERROR: Too many attempts");
    
    unset($_SESSION['otp_code'], $_SESSION['otp_expire'], $_SESSION['otp_attempt'], $_SESSION['reset_user']);
    exit("Terlalu banyak percobaan salah. Silakan minta OTP baru.");
}

$inputOtp = isset($_POST['otp']) ? trim($_POST['otp']) : '';
$storedOtp = trim($_SESSION['otp_code']);

debugLog("OTP Comparison:");
debugLog("  - Input: '$inputOtp' (length: " . strlen($inputOtp) . ")");
debugLog("  - Stored: '$storedOtp' (length: " . strlen($storedOtp) . ")");
debugLog("  - Input (hex): " . bin2hex($inputOtp));
debugLog("  - Stored (hex): " . bin2hex($storedOtp));

$inputOtp = (string)$inputOtp;
$storedOtp = (string)$storedOtp;

$isMatch = ($inputOtp === $storedOtp);
debugLog("  - Match: " . ($isMatch ? 'YES' : 'NO'));

if ($isMatch) {
    debugLog("✅ SUCCESS: OTP verified!");

    $_SESSION['otp_valid'] = true;

    $oldSessionId = session_id();
    session_regenerate_id(true);
    debugLog("Session regenerated: $oldSessionId -> " . session_id());

    unset($_SESSION['otp_code'], $_SESSION['otp_expire'], $_SESSION['otp_attempt']);
    debugLog("OTP data cleared from session");

    echo "OK";

} else {
    debugLog("❌ FAILED: OTP mismatch");
    
    $sisaWaktu = ceil($sisaDetik / 60);
    $sisaPercobaan = 6 - $_SESSION['otp_attempt'];
    
    debugLog("Remaining: $sisaPercobaan attempts, $sisaWaktu minutes");
    
    if ($sisaPercobaan > 0) {
        echo "OTP salah. Sisa percobaan: $sisaPercobaan. OTP berlaku $sisaWaktu menit lagi.";
    } else {
        echo "Terlalu banyak percobaan salah. Silakan minta OTP baru.";
    }
}

debugLog("=== VERIFY OTP END ===\n");