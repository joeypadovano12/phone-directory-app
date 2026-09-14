<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}
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
