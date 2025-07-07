<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "User not logged in.";
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['first_name'];

$cow = null;
$treatments = [];
$errors = [];
$max_wh_period = 0;
$treatment_count = 0;
$cow_id = null;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    include('config/db_connect.php');

    // Handle POST requests (delete treatment or delete cow)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['treatment_id_to_delete'])) {
            $treatment_id_to_delete = (int) $_POST['treatment_id_to_delete'];

            if (isset($_POST['cow_id'])) {
                $cow_id = (int) $_POST['cow_id'];
            } else {
                throw new Exception("Missing or invalid cow ID during deletion.");
            }

            $stmtDel = $conn->prepare("DELETE FROM tblcowtreatments WHERE id = ? AND user_id = ?");
            $stmtDel->bind_param("ii", $treatment_id_to_delete, $user_id);
            $stmtDel->execute();

            header("Location: view_cow.php?id=" . $cow_id);
            exit();

        } elseif (isset($_POST['cow_id_to_delete'])) {
            $cow_id_to_delete = (int) $_POST['cow_id_to_delete'];

            // Check treatment count
            $stmtCheck = $conn->prepare("SELECT COUNT(*) as treatment_count FROM tblcowtreatments WHERE cowID = ? AND user_id = ?");
            $stmtCheck->bind_param("ii", $cow_id_to_delete, $user_id);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();
            $checkRow = $resultCheck->fetch_assoc();

            if ($checkRow['treatment_count'] > 0) {
                throw new Exception("Cannot delete a cow with existing treatments.");
            }

            // Get cow number
            $stmtCowNum = $conn->prepare("SELECT number FROM tblcows WHERE id = ? AND user_id = ?");
            $stmtCowNum->bind_param("ii", $cow_id_to_delete, $user_id);
            $stmtCowNum->execute();
            $resultCowNum = $stmtCowNum->get_result();
            $cowDataDelete = $resultCowNum->fetch_assoc();

            if (!$cowDataDelete) {
                throw new Exception("Cow not found.");
            }

            $_SESSION['flash_message'] = "Cow with number " . $cowDataDelete['number'] . " from " . $user_name . "'s herd has been deleted.";

            $stmtDelCow = $conn->prepare("DELETE FROM tblcows WHERE id = ? AND user_id = ?");
            $stmtDelCow->bind_param("ii", $cow_id_to_delete, $user_id);
            $stmtDelCow->execute();

            header("Location: index.php");
            exit();

        } else {
            throw new Exception("Missing or invalid POST data during deletion.");
        }
    }

    // Handle GET request to view cow and treatments
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("Invalid Cow ID.");
    }

    $cow_id = (int) $_GET['id'];

    // Fetch cow info
    $stmt = $conn->prepare("SELECT * FROM tblcows WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cow_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cow = $result->fetch_assoc();

    if (!$cow) {
        throw new Exception("Cow not found.");
    }

    // Fetch treatments
    $sql = "
        SELECT t.id AS treatment_id, m.name AS medication, t.date_given, t.sickness, m.wh_period
        FROM tblcowtreatments t
        JOIN medication m ON t.medicationID = m.id
        WHERE t.cowID = ? AND t.user_id = ?
        ORDER BY t.date_given DESC
    ";

    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("ii", $cow_id, $user_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    $latest_vat_date = null;

    while ($row = $result2->fetch_assoc()) {
        $treatments[] = $row;
        $treatment_count++;

        $treatment_date = $row['date_given'];
        $wh_period = (int)$row['wh_period'];

        // Calculate vat release date
        $vat_date = date('Y-m-d', strtotime($treatment_date . " +$wh_period days"));

        if ($latest_vat_date === null || $vat_date > $latest_vat_date) {
            $latest_vat_date = $vat_date;
        }
    }

    $is_safe_to_milk = true;
    if ($latest_vat_date !== null) {
        $today = date('Y-m-d');
        $is_safe_to_milk = $today >= $latest_vat_date;
    }

} catch (Throwable $e) {
    $errors[] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('templates/header.php')?>
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
<div class="overlay narrow">
    <div class="error-div">
        <?php if (count($errors) > 0) {
            ?> <h2>Errors</h2> <?php
             foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error) . "</p>";
    }
        }?>
    </div>
        <h2 class="narro-text">Treatment records for <?php echo htmlspecialchars($cow['name']);?></h2>

    <div class="warning-box">
        <h3>Cow number: <?php echo htmlspecialchars($cow['number'])?></h3>
        <p>Total treatments given: <?php echo htmlspecialchars($treatment_count)?></p>
        <?php if ($is_safe_to_milk == true && $treatment_count > 0): ?>
            
                <span><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                            <rect width="32" height="32" rx="4" fill="white"></rect>
                                <path fill="#39b54a" d="M13.2 21.6L7.6 16l2.4-2.4 3.2 3.2 8.8-8.8L24 10.4l-10.8 11.2z"></path>
                        </svg>Cow is safe to be milked.</span>
                <p>Milk was safe to return to vat on: <?php echo date('d-m-Y', strtotime($latest_vat_date));?></p>
            <?php elseif ($is_safe_to_milk == false && $treatment_count > 0): ?>
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">
                    <rect width="32" height="32" rx="4" fill="white"></rect>
                    <path fill="#ff0000" d="M20.5 11.5L18.5 9.5 16 12 13.5 9.5 11.5 11.5 14 14 11.5 16.5 13.5 18.5 16 16 18.5 18.5 20.5 16.5 18 14z"></path>
                    </svg> 
        DO NOT MILK.</span>
        <p>Milk will be safe to return to the vat on: <?php echo date('d-m-Y', strtotime($latest_vat_date));?> </p>
        <?php endif; ?>
    </div>


</div>
<div class="text-content white-bg">
    <div class="white-container">
    <?php if ($treatment_count > 0): ?>
        <table class="standard-table">
            <tr><th>Sickness</th><th>Medication</th><th>Treatment Date (DD-MM-YYYY)</th><th>Withholding Period (In days)</th><th>Actions</th></tr>
            <?php foreach ($treatments as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['sickness'])?></td>
                <td><?php echo htmlspecialchars($t['medication'])?></td>
                <td><?php echo date('d-m-Y', strtotime($t['date_given']));?></td>
                <td><?php echo htmlspecialchars($t['wh_period'])?></td>
                <td>
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this treatment?');">
                    <input type="hidden" name="treatment_id_to_delete" value="<?php echo $t['treatment_id'];?>">
                    <input type="hidden" name="cow_id" value="<?php echo $cow_id; ?>">
                    <input type="submit" name="delete" value="Delete" class="btn-delete">
                    </form>
                    
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <table class="standard-table">
            <tr><th>Sickness</th><th>Medication</th><th>Treatment Date</th></tr>
            <tr><td colspan=4>No treatments have been given to this cow yet.</td></tr>
            <tr><td colspan=2>Would you like to delete this cow?</td>
        <td>
            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this cow?');">
                    <input type="hidden" name="cow_id_to_delete" value="<?php echo htmlspecialchars($cow_id); ?>">
                    <input type="submit" name="delete_cow" value="Delete Cow" class="btn-delete">
                    </form>
        </td></tr>
        </table>
        <?php endif; ?>
        </div>
    </div>

<?php include('templates/footer.php')?>
</html>