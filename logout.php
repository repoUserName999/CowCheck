<?php 
session_start();
session_unset();
session_destroy();

session_start();
$_SESSION['flash_message'] = "You have been logged out succesfully.";

header("Location: index.php");
exit();
?>