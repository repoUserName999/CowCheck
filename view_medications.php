<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to manage your medications.";
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['user_id'])) {
    $first_name = $_SESSION['first_name'];
    $user_id = $_SESSION['user_id'];
}
$flash_message = null;
$errors = [];
$meds = [];
$no_results = false;
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    include ('config/db_connect.php');
    //handle medication deletions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['medication_id_to_delete'])) {
            $med_id_to_delete = $_POST['medication_id_to_delete'];

            //check treatment count
            $stmtCheckMed = $conn->prepare("SELECT COUNT(*) as treatment_count FROM tblcowtreatments WHERE medicationID = ? AND user_id = ?");
            $stmtCheckMed->bind_param("ii", $med_id_to_delete, $user_id);
            $stmtCheckMed->execute();
            $resultCheck = $stmtCheckMed->get_result();
            $checkRow = $resultCheck->fetch_assoc();

            if ($checkRow['treatment_count'] > 0) {
                throw new Exception("Cannot delete a medication that has been recorded in a treatment. Please delete related treatment records and try again.");
            }

            //get treatment name
            $stmtMedName = $conn->prepare("SELECT name FROM medication WHERE id = ? AND user_id = ?");
            $stmtMedName->bind_param("ii", $med_id_to_delete, $user_id);
            $stmtMedName->execute();
            $resultMedName = $stmtMedName->get_result();
            $medDataDelete = $resultMedName->fetch_assoc();

            if(!$medDataDelete) {
                throw new Exception("Cow not found.");
            }

            $_SESSION['flash_message'] = "Medication with name  " . $medDataDelete['name'] . " from " . $user_name . "'s records has been deleted.";

            $stmtDelete = $conn->prepare("DELETE FROM medication WHERE id = ? AND user_id = ?");
            $stmtDelete->bind_param("ii", $med_id_to_delete, $user_id);
            $stmtDelete->execute();

            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Missing or invalid POST data during deletion.");
        }
    } } catch (throwable $e) {
        $errors[] = "Error deleting medication: " . $e->getMessage();
    }

    try {
    //search bar
    $search = $_GET['med-search-bar'] ?? '';
    if ($search !== '') {
        $sqlMeds = "SELECT * FROM medication WHERE user_id = ? AND name LIKE ? ORDER BY name";
        $stmtMeds = $conn->prepare($sqlMeds);
        $searchTerm = '%' . $search . '%';
        $stmtMeds->bind_param("is", $user_id, $searchTerm);
        $stmtMeds->execute();
        $resultsMeds = $stmtMeds->get_result();
    } else {
        $sqlMeds = ("SELECT * FROM medication WHERE user_id = ? ORDER BY name");
        $stmtMeds = $conn->prepare($sqlMeds);
        $stmtMeds->bind_param("i", $user_id);
        $stmtMeds->execute();
        $resultsMeds = $stmtMeds->get_result();
    }

    while ($row = $resultsMeds->fetch_assoc()) {
        $meds[] = $row;
    }
    if (empty($meds)) {
        $no_results = true;
    }
} catch (Throwable $e) {
    $errors[] = "Error loading medications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php include('templates/header.php'); ?>
<div class="background-image">
    <picture>
        
        <source media="(min-width: 1300px)" srcset="images/herocow1600.webp">
        <img src="images/hero_cow.webp" alt="Cows Grazing">
    </picture>
</div>
<div class="overlay narrow">
    <div class="error-div">
        <?php if ($flash_message !== null): ?>
            <h2 class="success"><?php echo htmlspecialchars($flash_message);?></h2>
        <?php endif; ?>
        <?php if (count($errors) > 0) { ?> 
        <h2>Errors</h2> 
        <?php foreach ($errors as $nd) {
        echo "<p>" . htmlspecialchars($nd) . "</p>";
        }
        }?>
    </div>
    <h2 class="narro-text">All medications for <?php echo htmlspecialchars($first_name); ?></h2>
    <div class="search-bar">
        <form method="get">
            <label for="med-search-bar">Search medication name:</label>
            <input type="search" id="med-search-bar" name="med-search-bar" value="<?= htmlspecialchars($_GET['med-search-bar'] ?? '') ?>">
            <input type="submit" value="Search">
        </form>
    </div>
</div><!-- end overlay div-->
<div class="text-content white-bg">
    <div class="white-container">
        <table class=standard-table>
            <tr><th colspan=3>Medications</th>
            </tr>
            <tr><td><b>Name</b></td><td><b>Withholding Period</b></td><td><b>Actions</b></td></tr>
            <?php if ($no_results): ?>
                <?php if ($search !== ''): ?>
                    <tr><td colspan=3>No results for your search <?php echo htmlspecialchars($search);?></td></tr>
                <?php else: ?>
                <tr><td colspan=3>You have not added any medications yet.</td></tr>
                <?php endif; ?>
            <?php endif; ?>
            <?php foreach ($meds as $m):?>
                <tr><td><?php echo htmlspecialchars($m['name']);?></td>
                    <td><?php echo htmlspecialchars($m['wh_period']);?> days</td>
                    <td>
                    <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this medication? If this medication has been recorded in one or more treatments, you must delete the treatment records first.');">
                    <input type="hidden" name="medication_id_to_delete" value="<?php echo $m['id'];?>">
                    <input type="submit" name="delete" value="Delete" class="btn-delete">
                    </form>
                </td>
                </tr>
            <?php endforeach; ?>
            <tr> </tr>
        </table>
    </div> <!-- end white-container-->
</div> <!-- end text-content div-->
<?php include('templates/footer.php'); ?>
</html>