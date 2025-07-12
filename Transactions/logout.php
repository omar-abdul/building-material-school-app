<?php
// logout.php
session_start();
session_unset(); // Tirtir dhammaan session variables
session_destroy(); // Tirtir session-ka
header('Location: /backend/dashbood/index.php'); // Ku celi login form
exit();
?>
