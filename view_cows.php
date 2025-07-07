<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "You must be logged in to see your cows.";
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['user_id'])) {
    $first_name = $_SESSION['first_name'];
    $user_id = $_SESSION['user_id'];
}
$cows = [];
$errors = [];
$no_results = false;

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    include ('config/db_connect.php');

    $search = $_GET['search-bar'] ?? '';
    if ($search !== '') {
        $sql = "SELECT * FROM tblcows WHERE user_id = ? AND number LIKE ? ORDER BY number";
        $stmt = $conn->prepare($sql);
        $searchTerm = '%' . $search . '%';
        $stmt->bind_param("is", $user_id, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else { 
    $sql = "SELECT * from tblcows WHERE user_id = ? ORDER BY number";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    }

    while($row = $result->fetch_assoc()) {
        $cows[] = $row;
    }
    if (empty($cows)) {
        $no_results = true;
    }
} catch (Throwable $e) {
    $errors[] = "Error loading cows: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php 
include('templates/header.php');
?>
<div class="background-image">
    <picture>
        
        <source media="(min-width: 1300px)" srcset="images/herocow1600.webp">
        <img src="images/hero_cow.webp" alt="Cows Grazing">
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
    <h2 class="narro-text">All cows for <?php echo htmlspecialchars($first_name); ?></h2>
    <div class="search-bar">
        <form method="get">
            <label for="search-bar">Search cow number:</label>
            <input type="search" id="search-bar" name="search-bar" value="<?= htmlspecialchars($_GET['search-bar'] ?? '') ?>">
            <input type="submit" value="Search">
        </form>
    </div>
</div>
<div class="text-content white-bg">
    <div class="white-container">
        <table class="standard-table">
            <tr><th>Number</th><th>Name</th><th>Breed</th><th>Actions</th></tr>
            <?php if ($no_results): ?>
                <tr><?php if ($search !== ''):?>
                    <td colspan=4>No results for your search query.</td>
                    <?php else: ?>
                    <td colspan=4>You have not added any cows yet.</td>
                    <?php endif; ?>
                 </tr>
                <?php endif; ?>
            <?php foreach($cows as $cow): ?>
                <tr>
                    <td><?= htmlspecialchars($cow['number']);?></td>
                    <td><?= htmlspecialchars($cow['name']); ?></td>
                    <td><?= htmlspecialchars($cow['breed']); ?></td>
                    <td class="button-a"><a href="view_cow.php?id=<?= $cow['id'] ?>">View Treatments</a></td>
                </tr>
                <?php endforeach; ?>
        </table>
    </div>
</div>
<?php include('templates/footer.php');?>
</html>