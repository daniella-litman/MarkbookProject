<?php

session_start();
session_unset();
session_destroy();

echo "Thank you for logging out";
header("Location: https://daniella.ihscompsci.com/markbook/markbookLogin.php");
?>