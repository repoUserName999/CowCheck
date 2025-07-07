<?php 
session_start();

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
}
$errors = [];
$messages = [];
$showLink = false;
$showForm = false;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    include('config/db_connect.php');
    $token = $_GET['token'] ?? '';

    if ($token) {
        $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $resetData = $result->fetch_assoc();
        $stmt->close();

        if (!$resetData || strtotime($resetData['expires_at']) < time()) {
        $errors[] = "This password reset link is invalid or has expired.";
        $showForm = false;
        } else {
        $email = $resetData['email'];
        $showForm = true;
        }

    } else {
        $errors[] = "No reset token provided.";
        $showForm = false;
        $showLink = true;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $newPwd = trim($_POST['new_pwd']);
        $confirmPwd = trim($_POST['confirm_pwd']);

        //error checking

        if (empty($newPwd) || empty($confirmPwd)) {
            $errors[] = "Please confirm your new password.";
        } elseif ($newPwd !== $confirmPwd) {
            $errors[] = "Passwords do not match.";
        } elseif (strlen($newPwd) < 6) {
            $errors[] = "Password length must be at least 6 characters.";
        } else {
            $hashedPwd = password_hash($newPwd, PASSWORD_DEFAULT);

            $stmtNewPwd = $conn->prepare("UPDATE tbl_users SET password_hash = ? WHERE email = ?");
            $stmtNewPwd->bind_param("ss", $hashedPwd, $email);
            $stmtNewPwd->execute();
            $stmtNewPwd->close();

            $stmtDeleteToken = $conn->prepare("DELETE FROM password_resets WHERE token = ? AND email = ?");
            $stmtDeleteToken->bind_param("ss", $token, $email);
            $stmtDeleteToken->execute();
            $stmtDeleteToken->close();

            $messages[] = "Your password has been successfully reset.";
            $showForm = false;
        }
    }
} catch (Throwable $e) {
    $errors[] = "Throwables errors: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html>
    <?php include ('templates/header.php'); ?>
    <div class="background-image">
    <picture>
        <source media="(min-width: 1300px)" srcset="images/background_1920_1080.webp">
        <source media="(min-width: 1000px)" srcset="images/background_1000_700.webp">
        <source media="(max-width: 999px) and (orientation: landscape)" srcset="images/background_large_phone_landscape.webp">
        <source media="(min-width: 481px) and (max-width: 767px)" srcset="images/background_large_phone.webp">
        <source media="(max-width: 480px)" srcset="images/background_standard_phone.webp">
        <img src="images/background_standard_phone.webp" alt="Cows Grazing">
    </picture>
</div>
<div class="overlay">
    <?php if (count($errors) == 0): ?>
        <h2>Reset your password</h2>
        <?php endif; ?>
    <div class="error-div">
        <?php if (count($errors) > 0) {
            ?> <h2>Errors</h2> <?php
             foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
        } elseif (count($messages) > 0) { ?>
        <h2 class="success">Password Reset</h2>
        <?php foreach ($messages as $m) { 
            echo "<p>" . htmlspecialchars($m) . "</p>"; ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 5000);
            </script>
       <?php }
    } ?>
    <?php if (count($errors) > 0 && $showLink): ?>
        <h2>Errors</h2>
        <?php foreach ($errors as $e): ?>
            <p><?php echo htmlspecialchars($e); ?></p>
        <?php endforeach;?>
        <p>Click <a href="forgot_password.php">here</a> to return to the forgotten password page.</p>
        <?php endif; ?>
    </div>

    <!-- Form -->
     <?php if ($showForm): ?>
        <form action="" method="post" class="standard-form">
            <table class="standard-table">
                <tr><th colspan=2>Set New Password</th></tr>
                <tr><td colspan=2>Password must be at least 6 characters in length.</td></tr>
                <tr><td><label for="new_pwd"></label></td><td><input type="password" name="new_pwd"></td></tr>
                <tr><td><label for="confirm_pwd">Enter password again.</label></td><td><input type="password" name="confirm_pwd"></td></tr>
                <tr><td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Reset Password" class="button"></td></tr>          
            </table>
        </form>
        <?php endif; ?>
</div>
    <?php include ('templates/footer.php'); ?>
</html>