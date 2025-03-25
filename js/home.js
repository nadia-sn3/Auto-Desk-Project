function redirectToSignup() {
    if (!isLoggedIn) {

        window.location.href = "signup.php";
    } else {
        window.location.href = "create-project.php";
    }
}
