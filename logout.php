<?php
session_start();
session_destroy();
header("Location: viewhomepage.php");
exit;
?>
