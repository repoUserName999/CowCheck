<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to add a treatment.";
    header("Location: login.php");
    exit();
} else {
    $user_id = $_SESSION['user_id'];
}
$errors = [];

try {
include ('config/db_connect.php');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); //automatically throw exceptions


$sickness = "";
//Form validation
if(isset($_POST['submit'])) {
    //check sickness
    if(empty($_POST['sickness'])) {
        $errors[] = "Sickness is required";
    } else {
        $sickness = htmlspecialchars(trim($_POST['sickness']));
    }
    if(empty($_POST['treatment_date'])) {
        $errors[] = "Treatment date is required";
    } else {
        $treatment_date = htmlspecialchars(trim($_POST['treatment_date']));
    }
    if(empty($_POST['cow'])) {
        $errors[] = "Cow number required.";
    } else {
        $cow_number = htmlspecialchars(trim($_POST['cow']));
    }
    if(empty($_POST['medication'])) {
        $errors[] = "Medication is required";
    } else {
        $medication = htmlspecialchars(trim($_POST['medication']));
    }
    if(array_filter($errors)) {
    } else {
        $_SESSION['medication_form_data'] = [
            'sickness' => htmlspecialchars($sickness),
            'treatment_date' => htmlspecialchars($treatment_date),
            'cow' => htmlspecialchars($cow_number),
            'medication' => htmlspecialchars($medication),
            'user_id' => $user_id,
        ];
        header('Location: processform.php');
        exit();
    }

} //end of post check

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
        $user_id = $_SESSION['user_id'];
        $stmtCow = $conn->prepare("SELECT id, number FROM tblcows WHERE user_id = ? ORDER BY number");
        $stmtCow->bind_param("i", $user_id);
        $stmtCow->execute();
        $resultCows = $stmtCow->get_result();

        $stmtMed = $conn->prepare("SELECT id, name, wh_period FROM medication WHERE user_id = ? ORDER BY name");
        $stmtMed->bind_param("i", $user_id);
        $stmtMed->execute();
        $resultMedication = $stmtMed->get_result();
        
            
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Database error [{$e->getCode()}]: " . $e->getMessage();
        }

    } catch (Throwable $e) {
        $errors[] = "An error occured: " . $e->getMessage();
    }




?>
<!DOCTYPE html>

<html lang="en">
<?php  
include('templates/header.php');
 ?>
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
    <form action="" method="post" id="treatment-form" class="standard-form">
    <table id="meds" class="standard-table">
        <tr style="background: #cccccc;">
            <th colspan="2">Cow Treatment</th>
        </tr>
        <tr>
            <td><label for="sickness">Cow Sickness</label></td>
            <td><input type="text" name="sickness" id="sickness" value="<?php echo $sickness?>"></td>
        </tr>
        <tr>
    <td>Treatment Date</td>
    <td><input type="date" name="treatment_date" required></td>
</tr>
        
        
        <tr>
            <td>Enter Cow Number</td>
            <td><select name="cow" id="cow">
                <option value="">Select a cow</option>
                <?php if (isset($resultCows) && $resultCows->num_rows > 0): ?>
                <?php 
                    $cowArray = [];
                    while ($row = $resultCows->fetch_assoc()) {
                        $cowArray[] = $row;
                    } ?>
                    <?php foreach ($cowArray as $cow): ?>
                    <option value="<?= htmlspecialchars($cow['id']) ?>">
                    <?= htmlspecialchars($cow['number']) ?>
                    </option>
                <?php endforeach; ?>
                <?php else: ?>
                <option>No cows found</option>
            <?php endif; ?>
            </select>
            </td>


        </tr>

        <tr>
    <td>Enter Treatment</td>
    <td>
        <select name="medication" id="medication">
            <option value="">Select a Treatment</option>
            <?php 
            if (isset($resultMedication) && $resultMedication->num_rows > 0):
                $medicationArray = [];
                while ($row = $resultMedication->fetch_assoc()) {
                    $medicationArray[] = $row;
                }
                foreach ($medicationArray as $row): ?>
                    <option value="<?php echo htmlspecialchars($row['id']); ?>">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php endforeach;
            else: ?>
                <option value="">No medication found</option>
            <?php endif; ?>
        </select>
    </td>
</tr>
        <tr>
            <td colspan="2" style="text-align: center;"><input type="submit" name="submit" value="Add Treatment" class="button"></td>
        </tr>
        
    </table>
    </form>
</div>
<?php include('templates/footer.php');?>
</html>