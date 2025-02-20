<?php
session_start();

if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = "Session is working!";
} else {
    echo $_SESSION['test'];
}
?>
