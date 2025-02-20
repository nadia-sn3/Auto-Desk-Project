// redirect.js

function redirectToSignup() {
    if (!isLoggedIn) {
        // Redirect to the signup page
        window.location.href = "signup.php";
    } else {
        // If logged in, continue with the intended action (e.g., navigate to create project page)
        window.location.href = "create-project.php";
    }
}
