<?php 
session_start();

try {
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include('config/db_connect.php');

$no_data = [];
$success =[];



if (!isset($_SESSION['cow_data'])) {
    $no_data = ["No new cow data found."];
} else { 

$new_cow_data = $_SESSION['cow_data'];
unset($_SESSION['cow_data']);

$c_name = $new_cow_data['new_cow_name'];
$c_num = $new_cow_data['new_cow_number'];
$c_breed = $new_cow_data['new_cow_breed'];
$user_id = $new_cow_data['user_id'];


    
    $sqlInsert = "INSERT INTO tblcows (number, name, breed, user_id) VALUES (?, ?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("sssi", $c_num, $c_name, $c_breed, $user_id);
    $stmtInsert->execute();
    if ($stmtInsert->affected_rows > 0) {
        $success = ["Cow recorded successfully. You will be redirected shortly."];
    } else {
        $no_data = ["Failed to record treatment."];
    }

} 

}
 catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        $no_data = ["Duplicate entry: A cow with the same number already exists."];
    } else {
        $no_data = ["Database error: " . $e->getMessage()];
    }
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
    <div class="error-div">
        <?php if (count($no_data) > 0) {
            ?> <h2>Errors.</h2> <?php
             foreach ($no_data as $nd) {
        echo "<p>" . htmlspecialchars($nd) . "</p>";
    } ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'addcow.php';
                }, 5000);
            </script>
      <?php  }?>
        <?php if (count($success) > 0) { ?>
            <h2 class="success">Success!</h2>
            <?php foreach ($success as $s) {
                echo "<p class='success'>" . htmlspecialchars($s) . "</p>";
            } ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 5000);
            </script>
           <?php }?>
    </div>

   
</div>
<?php include('templates/footer.php'); ?>
</html>