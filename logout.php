<?php
session_start();
session_destroy();

//  Redirect back to your shared homepage after logout
header("Location: index.php");
exit();
?>
