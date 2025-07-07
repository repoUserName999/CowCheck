<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to add a medication.";
    header("Location: login.php");
    exit();
}
$errors = [];
$medname = "";
$wh_period = "";

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    include('config/db_connect.php');
    if(isset($_POST['submit'])) {
        //validate name
        if(empty($_POST['medname'])) {
            $errors[] = "Medicine name must be defined.";
        } else {
            $medname = htmlspecialchars(trim($_POST['medname']));
            if (!preg_match("/^[a-zA-Z0-9\s'\-]+$/", $medname)) {
            $errors[] = "Medicine name can only contain letters, numbers, spaces, apostrophes and hyphens."; }
        }
        if(empty($_POST['wh_period'])) {
            $errors[] = "Withholding period must be defined.";
        } else {
            $wh_period = htmlspecialchars(trim($_POST['wh_period']));
            if (!preg_match('/^\d+$/', $wh_period)) {
                $errors[] = "Withholding period must only contain digits.";
            }
        }
        if(array_filter($errors)) {
    } else {
        $_SESSION['new_med_data'] = [
            'medname' => htmlspecialchars($medname),
            'wh_period' => htmlspecialchars($wh_period),
            'user_id' => $_SESSION['user_id'],
        ];
        header('Location: processnewmed.php');
        exit();
    }
    } //end post check
} catch (Throwable $e) {
        $errors[] = "An error occured: " . $e->getMessage();
    }


?>

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
    <div class="error-div">
        <?php if (count($errors) > 0) {
            ?> <h2>Errors</h2> <?php
             foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
        }?>
    </div>
    <form action="" method="post" id="addmed-form" class="standard-form">
    <table class="standard-table">
        <tr style="background: #cccccc;">
            <th colspan="2">Add a Medication</th>
        </tr>
        <tr>
            <td><label for="medname">Medication Name (Can contain numbers, spaces, apostrophes and hyphens)</label></td>
            <td><input type="text" name="medname" id="medname" value="<?php echo $medname?>"></td>
        </tr>
        <tr>
            <td><label for="wh_period">Withholding Period (In days)</label></td>
            <td><input type="text" name="wh_period" id="wh_period" value="<?php echo $wh_period ?>" min="1" max="999" maxlength="3" required></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Add Medication"></td>
        </tr>
    </table>
    </form>
</div>
<?php include('templates/footer.php');?>
</html>