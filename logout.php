<?php
session_start();
session_destroy(); // Destroy the session
setcookie(session_name(), '', time() - 3600, '/'); // Remove session cookie

header("Location: signin.php"); // Redirect to login page
exit();
?>