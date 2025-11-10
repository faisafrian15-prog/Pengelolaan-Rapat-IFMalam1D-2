<?php
session_start();
session_unset();
session_destroy();

echo "Logout berhasil! Mengalihkan ke login...";
sleep(2);

header("Location: Login.php");
exit();
?>
