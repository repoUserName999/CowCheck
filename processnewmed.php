<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to add a medication.";
    header("Location: login.php");
    exit();
}

$no_data = [];
$success = [];


try {
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include('config/db_connect.php');
if(!isset($_SESSION['new_med_data'])) {
    $no_data = ["Now new medication data found."];
} else {

    //clear session
    $new_med_data = $_SESSION['new_med_data'];
    unset($_SESSION['new_med_data']);

    $m_name = $new_med_data['medname'];
    $wh_p = $new_med_data['wh_period'];
    $user_id = $new_med_data['user_id'];

    $sqlInsert = "INSERT INTO medication (name, wh_period, user_id) VALUES (?, ?, ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("sii", $m_name, $wh_p, $user_id);
    $stmtInsert->execute();
    if ($stmtInsert->affected_rows > 0) {
        $success = ["Medication recorded successfully. You will be redirected shortly."];
    } else {
        $no_data = ["Failed to medication."];
    }
     

}
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) {
        $no_data = ["Duplicate entry: A medication with the same name already exists."];
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
                    window.location.href = 'addmedication.php';
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