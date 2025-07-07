<?php 
session_start();
$errors = [];
$flash_message = null;

try {
include('config/db_connect.php');

$errors = [];
$flash_message = null;
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); 
}
if (isset($_SESSION['first_name'])) {
    $first_name = $_SESSION['first_name'];
} 
} catch (Throwable $e) {
        $errors[] = "An error occured: " . $e->getMessage();
    }
?>
<!Doctype html>

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
    
    
    <?php if(!isset($_SESSION['user_id'])): ?>
        <h2>Cow check</h2>
        <p><i>All your herd treatments in one place.</i></p>
        <div class="buttons-container">
    <div class="cta-button"><a href="register.php">Create an account</a></div>
    <div class=cta-button><a href="login.php">Login</a></div>
    </div>
    <?php elseif (isset($_SESSION['first_name'])): ?>
        <h2>Welcome back to Cow Check, <?php echo htmlspecialchars($first_name)?>!</h2>
        <p><i>Manage your herd here!</i></p>
        <div class="buttons-container">
           <div class=cta-button><a href="view_cows.php">View all cows</a></div> 
    </div>
    <?php endif; ?>
    
    
    </div>
    <div class="text-content">
        <div>
        <h2>Welcome to Cow Check!</h2>
        <p><i></i></p>
        <p>Keep track of your herd treatments easily. Add your cow and the treatment receieved to the database and Cow Check will automatically calculate 
            the date that her milk can safely go back in the vat. 
        </p>
        <p>Follow the steps below to get started.</p>
        <h2 class="collapsible-toggle">Create an Account and Log In</h2>
            <div class="collapsible-content">
                <div class="images-steps">
                    <div>
                        <p><b>Step 1:</b> Navigate to the 'Create Account' page.</p>
                        <picture>
                            <img src="images/createaccount.webp" alt="Create account step 1">
                        </picture>
                    </div>
                    <div>
                        <p><b>Step 2:</b> Enter your details and select 'Create account'.</p>
                        <picture>
                            <img src="images/createaccount_step2.webp" alt="Create account step 2">
                        </picture>
                    </div>
                    <div>
                        <p><b>Step 3:</b> You will be redirected to the home page. From here, select 'Log in'.</p>
                        <picture>
                            <img src="images/login.webp" alt="Create account step 3">
                        </picture>
                    </div>
                    <div>
                        <p><b>Step 4:</b> Enter your details to log in and begin using Cow Check.</p>
                        <picture>
                            <img src="images/login2.webp" alt="Create account step 4">
                        </picture>
                    </div>
                </div>
            </div>
        <br>
        <h2 class="collapsible-toggle">Add cows to your herd.</h3>
            <div class="collapsible-content">
                <div class="images-steps">
                    <div>
                    <p><b>Step 1:</b> Select add cow from the hamburger menu.</p>
                    <picture>
                        <img src="images/addcow_steps1.webp" alt="Add Cow Step 1">
                    </picture>
                    </div>
                    <div>
                    <p><b>Step 2:</b> Fill in the form with your cow's details and click 'Add Cow' to save her details in your herd database. A name is required, just make something up.</p>
                    <picture>
                        <img src="images/addcow2.webp" alt="Add Cow Step 2">
                    </picture>
                    </div>
                </div>
            </div>
        </div>
</div>


<?php include('templates/footer.php'); ?>

</html>