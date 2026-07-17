<?php
require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}
// Temporary debugging: check the server log to see the current session user shape.
error_log("Dashboard session user: " . var_export($_SESSION["user"] ?? [], true));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <?php render_nav(); ?>
    <h1>Dashboard</h1>
    <p>Welcome, <?php echo htmlspecialchars(get_user_username()); ?></p>
     <!-- Last PHP inside <body> so it captures messages queued during this request. -->
    <?php render_flash_messages(); ?>
</body>
</html>