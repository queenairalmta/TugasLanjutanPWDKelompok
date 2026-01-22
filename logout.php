<?php
require_once 'config.php';

$username = $_SESSION['username'] ?? '';

session_unset();
session_destroy();

header('Location: login.php?message=Logout+berhasil.+Sampai+Jumpa+' . urlencode($username));
exit();
?>