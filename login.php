<?php 
session_start();
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); 
}

$errors = [];
try {
   mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
 include('config/db_connect.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password_hash, first_name FROM tbl_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hash, $fname);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['first_name'] = $fname;

        header("location: index.php");
        exit();
    } else {
        $errors = ["Invalid credentials."];
    }
}
} catch (Throwable $e) {
        $errors[] = "An error occured: " . $e->getMessage();
    }
?>
<!DOCTYPE html>
<html lang="en">
<?php include('templates/header.php'); ?>
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
    <div class="error-div">
        <?php if (count($errors) > 0) {
            ?> <h2>Errors</h2> <?php
             foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
        }?>
        <?php if ($flash_message !== null): ?>
            <p><?php echo htmlspecialchars($flash_message) ?></p>
            <p><a href="register.php">Click here</a> to create an account.</p>
        <?php endif; ?>
    </div>
    <form action="" method="post" class="standard-form">
        <table class="standard-table">
            <tr><th colspan=2>Login</th></tr>
            <tr>
                <td><label for="username">Username</label></td>
                <td><input type="text" name="username" id="username" value=""></td>
             </tr>
             <tr>
                <td><label for="password">Password</label></td>
                <td><input type="password" name="password" id="password"></td>
            </tr>
            <tr><td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Log In" class="button"></td></tr>
        </table>
    </form>

</div>
<?php include('templates/footer.php'); ?>
</html>