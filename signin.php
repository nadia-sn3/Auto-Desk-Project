<?php
require 'db/connection.php'; 

$error_message = ''; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name']; // Store first name in session

        // Check if user is part of an organization
        $stmt = $pdo->prepare("SELECT om.role_id, o.org_id 
                              FROM organisation_members om
                              JOIN organisations o ON om.org_id = o.org_id
                              WHERE om.user_id = ?");
        $stmt->execute([$user['user_id']]);
        $orgMembership = $stmt->fetch();

        if ($orgMembership) {
            $_SESSION['current_org_id'] = $orgMembership['org_id'];
            $_SESSION['org_role'] = $orgMembership['role_id'];
            
            switch ($orgMembership['role_id']) {
                case 2: // Owner
                    header("Location: org-owner-home.php");
                    break;
                case 3: // Admin
                    header("Location: org-admin-home.php");
                    break;
                case 1: // System Admin
                    header("Location: system-admin-home.php");
                    break;
                default: // Regular member
                    header("Location: org-member-home.php");
            }
        } else {
            header("Location: project-home.php");
        }
        exit();
    } else {
        $error_message = "Invalid email or password."; 
    }
}
?>
