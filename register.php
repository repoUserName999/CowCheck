<?php 
session_start();
$errors = [];
$newuser = "";
$firstname = $lastname = $emailaddress = "";

try {
include('config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newuser = htmlspecialchars(trim($_POST['newusername']));
    $firstname =  htmlspecialchars(trim($_POST['newfirstname']));
    $lastname =  htmlspecialchars(trim($_POST['newlastname']));
    $emailaddress =  htmlspecialchars(trim($_POST['newemailaddress']));

    // Basic validation
    if (empty($newuser)) $errors[] = "Username is required.";
    if (empty($firstname)) $errors[] = "First name is required.";
    if (empty($lastname)) $errors[] = "Last name is required.";
    if (empty($emailaddress) || !filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }
   

    //check username is available
    if (empty($errors)) {
        $stmtCheckUser = $conn->prepare("SELECT user_id from tbl_users WHERE username = ?");
        $stmtCheckUser->bind_param("s", $newuser);
        $stmtCheckUser->execute();
        $stmtCheckUser->store_result();
        if ($stmtCheckUser->num_rows > 0) {
            $errors[] = "Username is already taken.";
        }
        $stmtCheckUser->close();

        $stmtCheckEmail = $conn->prepare("SELECT user_id FROM tbl_users WHERE email = ?");
        $stmtCheckEmail->bind_param("s", $emailaddress);
        $stmtCheckEmail->execute();
        $stmtCheckEmail->store_result();
        if ($stmtCheckEmail->num_rows > 0) {
        $errors[] = "An account with this email already exists.";
    }
    $stmtCheckEmail->close();

    }
    if (empty($errors)) {
        $password_raw = $_POST['newpassword'];
        if (empty($password_raw)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password_raw) < 6) {
            $errors[] = "Password must contain at least 6 characters.";
        } else {
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $created_at = date('Y-m-d H:i:s');

            $stmtNewUser = $conn->prepare("INSERT INTO tbl_users (username, first_name, last_name, email, password_hash, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtNewUser->bind_param("ssssss", $newuser, $firstname, $lastname, $emailaddress, $password_hash, $created_at);
            $stmtNewUser->execute();
            $_SESSION['flash_message'] = "Your account was created successfully. Please log in.";
            header("Location: index.php");
            exit();
        }
        
    }
}  

}catch (Throwable $e) {
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
    </div>
    
    <form action="" class="standard-form" method="post">
        <h2>Please create an account to use this site.</h2>
        <table class="standard-table">
            <tr><th colspan=2>Register new user</th></tr>
            <tr>
                <td><label for="newusername">User Name</label></td>
                <td><input type="text" name="newusername" id="newusername" value="<?php echo $newuser?>"></td>
             </tr>
             <tr>
                <td><label for="newpassword">Password</label></td>
                <td><input type="password" name="newpassword" id="newpassword"></td>
            </tr>
             <tr>
                <td><label for="newfirstname">First Name</label></td>
                <td><input type="text" name="newfirstname" id="newfirstname" value="<?php echo $firstname?>"></td>
            </tr>
            <tr>
                <td><label for="newlastname">Last Name</label></td>
                <td><input type="text" name="newlastname" id="newlastname" value="<?php echo $lastname?>"></td>
            </tr>
            <tr>
                <td><label for="newemailaddress">Email Address</label></td>
                <td><input type="text" name="newemailaddress" id="newemailaddress" value="<?php echo $emailaddress?>"></td>
            </tr>
            <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Create Account" class="button"></td>
        </tr>
        </table>
    </form>
</div>

<?php include('templates/footer.php'); ?>
</html>