<?php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // change this to your login page
exit;
