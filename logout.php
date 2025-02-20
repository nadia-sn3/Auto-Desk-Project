<?php
session_start();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/'); 

header("Location: signin.php"); 
exit();
?>