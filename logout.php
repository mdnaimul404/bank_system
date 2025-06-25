<?php
session_start();
session_unset();
session_destroy();

// Clear cookies by setting expiry in past
setcookie('user_id', '', time() - 3600, "/");
setcookie('role', '', time() - 3600, "/");
setcookie('name', '', time() - 3600, "/");

header("Location: index.php");
exit();
?>
