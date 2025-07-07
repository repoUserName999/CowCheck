<?php 
session_start();
$errors = [];
$messages = [];
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 

try {
include('config/db_connect.php');


require_once 'functions/sendemail.php';


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['forgotemail'] ?? '');

    if (empty($email)) {
    $errors[] = "Email is requried.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
    } else {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmtDeleteOld = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmtDeleteOld->bind_param("s", $email);
        $stmtDeleteOld->execute();
        $stmtDeleteOld->close();

        $stmtNewToken = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmtNewToken->bind_param("sss", $email, $token, $expires);
        $stmtNewToken->execute();

        

        $result = sendResetEmail($email, $token);
            if ($result === true) {
                $messages[] = "Password reset link has been sent.";
            } else {
                $errors[] = $result;
            }
    }
}
} catch (Throwable $e) {
        $errors[] = "Throwable errors: " . $e->getMessage();
    }




?>
<!DOCTYPE html>
<html lang="en">
<?php include ('templates/header.php');?>
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
    <h2>Reset Your Password</h2>
    <div class="error-div">
        <?php if (count($errors) > 0) {
            ?> <h2>Errors</h2> <?php
             foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
        } elseif (count($messages) > 0) { ?>
        <h2 class="success">Password reset link set</h2>
        <?php foreach ($messages as $m) { 
            echo "<p>" . htmlspecialchars($m) . "</p>";
        }
    } ?>
    </div>
    <form action="" class="standard-form" method="post">
        <table class="standard-table">
            <tr><th colspan=2>Enter your email below to reset your password.</th></tr>
            <tr><td><label for="forgotemail">Email Address</label></td><td><input type="email" name="forgotemail" required></td></tr>
            <tr><td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Get Reset Link" class="button"></td></tr>
        </table>
    </form>
</div>
<?php include ('templates/footer.php');?>
</html>