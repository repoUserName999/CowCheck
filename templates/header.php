
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cow Check | Manage your herd's Health </title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" type="image/x-icon" href="images/cow_logo.svg">
</head>

<body>
    
<nav>
    <div id="logo-container"><!--Logo-->
        <div class="logo-bg">
            <a href="index.php">
                <img src="images/cow_logo.svg" alt="Cow Check Logo">
            </a>
        </div>
        <h1>Cow Check</h1>
    </div>
    <div id="buttons-container">
        <div><a href="medication.php">Treat a Cow</a></div>
        <div><a href="addcow.php">Add a cow</a></div>
        <div><a href="addmedication.php">Add a Medication</a></div>
        <?php if (isset ($_SESSION['user_id'])):?>
        <div><a href="logout.php">Log Out</a></div>
        <?php endif;?>
    </div>
    <div class="ham-menu">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>
<div class="off-screen-menu"><ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="medication.php">Treat a cow</a></li>
    <li><a href="view_cows.php">View all cows</a></li>
    <li><a href="view_medications.php">View all medications</a></li>
    <li><a href="addmedication.php">Add a medication</a></li>
    <li><a href="addcow.php">Add a cow</a></li>
    <?php if (isset ($_SESSION['user_id'])): ?>
    <li><a href="logout.php">Log Out</a></li>
    <?php elseif (!isset ($_session['user_id'])): ?>
    <li><a href="register.php">Create Account</a></li>
    <li><a href="login.php">Log In</a></li>
    <li><a href="forgot_password.php">Reset Password</a></li>
    <?php endif; ?>
</ul></div>
