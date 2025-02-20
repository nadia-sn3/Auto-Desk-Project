<?php
include('includes/connect.php');

$errors = array();
$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["passwordRepeat"];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if (empty($user) || empty($email) || empty($password)) {
        array_push($errors, "All fields are required");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Email is not valid");
    }

    if (strlen($password) < 8) {
        array_push($errors, "Password must be at least 8 characters long");
    }

    if ($password !== $passwordRepeat) {
        array_push($errors, "Passwords do not match");
    }

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        die("SQL statement preparation failed: " . mysqli_stmt_error($stmt));
    } else {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            array_push($errors, "Email already exists!");
        }
    }

    if (empty($errors)) {
        $role_id = 2; 
        $sql = "INSERT INTO user (username, email, password, role_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            die("SQL statement preparation failed: " . mysqli_stmt_error($stmt));
        } else {
            mysqli_stmt_bind_param($stmt, "sssi", $user, $email, $passwordHash, $role_id);
            if (mysqli_stmt_execute($stmt)) {
                $successMessage = "You are registered successfully.";
            } else {
                die("Error executing statement: " . mysqli_stmt_error($stmt));
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoDesk | Register</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h2 class="logo">
                <a href="home.php">AutoDesk</a>
            </h2>
            <nav class="nav-links">
                <ul>
                    <li><a href="login.php" class="btnLogin">Login</a></li>
                </ul>
            </nav>
        </header>
    </div>        

    <div class="wrapper">
        <span class="fa fa-times-circle-o" onclick="concealLogin()"></span>
        <div class="form-box register">
            <h2>Registration</h2>

            <?php if (!empty($errors)): ?>
                <div class="error-message-container">
                    <?php foreach ($errors as $error): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
            <?php endif; ?>
            
            <form action="register.php" method="POST">
                <div class="input-box">
                    <span class="icon"></span>
                    <input type="text" name="username" required>
                    <label>Username</label>
                </div>

                <div class="input-box">
                    <span class="icon"></span>
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <div class="input-box">
                    <span class="icon"></span>
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <div class="input-box">
                    <span class="icon"></span>
                    <input type="password" name="passwordRepeat" required>
                    <label>Password Confirmation</label>
                </div>

                <div class="t&c">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the terms & conditions</label>
                </div>

                <div>
                    <input type="hidden" name="register" value="1">
                    <button type="submit" class="btn">Register</button>
                    <div class="login-register">
                        <p>Already have an account? <a href="login.php" class="register-link">Login</a></p>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="js/java.js"></script>
<?php
    include('includes/footer.php'); 
?>
</body>
</html>
