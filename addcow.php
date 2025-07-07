<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to add a cow.";
    header("Location: login.php");
    exit();
}


$errors = [];
$cowname = "";
$cownumber = "";
$cowbreed = "";

try {
 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include('config/db_connect.php');
if(isset($_POST['submit'])) {

    //Validate cow name
    if(empty($_POST['cowname'])) {
        $errors[] = "Cow name is required. Just make something up.";
    } else {
        $cowname = htmlspecialchars(trim($_POST['cowname']));
    }
    if(empty($_POST['cownumber'])) {
        $errors[] = "Cow number is required." ;
    } else {
        $cownumber = htmlspecialchars(trim($_POST['cownumber']));
    }
    if(empty($_POST['cowbreed'])) {
        $errors[] = "A breed is required.";
    } else {
        $cowbreed = htmlspecialchars(trim($_POST['cowbreed']));
    }
    if (array_filter($errors)) {
    } else {
        $_SESSION['cow_data'] = [
        'new_cow_name' => htmlspecialchars($cowname),
        'new_cow_number' => htmlspecialchars($cownumber),
        'new_cow_breed' => htmlspecialchars($cowbreed),
        'user_id' => $_SESSION['user_id'],
        ];
        header('Location: processnewcow.php');
        exit();
    }
}
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
    <form action="" method="post" id="addcow-form" class="standard-form">
    <table class="standard-table">
        <tr style="background: #cccccc;">
            <th colspan="2">Add a Cow</th>
        </tr>
        <tr>
            <td><label for="cowname">Cow Name</label></td>
            <td><input type="text" name="cowname" id="cowname" value="<?php echo $cowname?>"></td>
        </tr>
        <tr>
            <td><label for="cownumber">Cow Number</label></td>
            <td><input type="text" name="cownumber" id="cownumber" value="<?php echo $cownumber ?>"></td>
        </tr>
        <tr>
            <td><label for="breed">Breed</label></td>
            <td><input type="text" name="cowbreed" id="cowbreed" value="<?php echo $cowbreed ?>"></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Add Cow"></td>
        </tr>
    </table>
    </form>
</div>
<?php include('templates/footer.php');?>
</html>