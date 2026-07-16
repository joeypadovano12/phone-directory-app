<?php
// UCID: jp239, Date: 7/13/26

require_once(__DIR__ . "/../../lib/app.php");

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>