<?php
session_start();
session_destroy();
header("Location: /restaurante/index.php");
exit;
?>
