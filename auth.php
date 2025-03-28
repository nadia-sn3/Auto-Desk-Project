<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: signin.php");
        exit();
    }
}

function require_admin() {
    require_login(); 
    if ($_SESSION['system_role_id'] != 1) {
        header("Location: index.php");
        exit();
    }
}

function require_org_access($org_id = null) {
    require_login();
    if ($_SESSION['system_role_id'] == 1) {
        return; 
    }
    
    if ($org_id && !has_org_access($org_id)) {
        header("Location: org-dashboard.php");
        exit();
    }
}

function has_org_access($org_id) {
    if (!isset($_SESSION['org_memberships'])) {
        return false;
    }
    
    foreach ($_SESSION['org_memberships'] as $membership) {
        if ($membership['org_id'] == $org_id) {
            return true;
        }
    }
    
    return false;
}

function require_project_access($project_id) {
    require_login();
    if ($_SESSION['system_role_id'] == 1) {
        return; 
    }
    
    if (!has_project_access($project_id)) {
        header("Location: project-home.php");
        exit();
    }
}

function has_project_access($project_id) {
    if (!isset($_SESSION['project_memberships'])) {
        return false;
    }
    
    foreach ($_SESSION['project_memberships'] as $membership) {
        if ($membership['project_id'] == $project_id) {
            return true;
        }
    }
    
    return false;
}
?>