<?php
require_once(__DIR__ . "/../../lib/app.php");

$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if(!$user_id && is_logged_in()){
    $user_id = get_user_id();
}

if (!$user_id) {
    flash("Invalid user profile requested.", "warning");
    header("Location: dashboard.php");
    exit;
}

$db = getDB();
$stmt = $db->prepare(
    "SELECT username
     FROM Users
     WHERE id = :user_id
     LIMIT 1"
);
$stmt->execute([":user_id" => $user_id]);
$profileUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profileUser) {
    flash("User not found.", "warning");
    header("Location: dashboard.php");
    exit;
}

$phones = [];
try {
    $stmt = $db->prepare(
        "SELECT p.id, p.phone_brand, p.phone_model, p.screen_size, p.camera_megapixels
         FROM project_phones p
         JOIN user_phones up ON p.id = up.phone_id
         WHERE up.user_id = :user_id
         LIMIT 10"
    );
    $stmt->execute([":user_id" => $user_id]);
    $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e){
    error_log("Public profile phones fetch failed: " . $e->getMessage());
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profileUser["username"]); ?>'s Profile</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1><?php echo htmlspecialchars($profileUser["username"]); ?>'s Profile</h1>
        <p><strong>User ID:</strong> <?php echo htmlspecialchars((string)$user_id); ?></p>
        <p><strong>Username</strong> <?php echo htmlspecialchars($profileUser["username"]); ?></p>

        <h2>Associated Phones</h2>
        <?php if (empty($phones)): ?>
            <p>No public phones associated with this user.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Screen Size</th>
                        <th>Camera MP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($phones as $phone): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($phone["phone_brand"]); ?></td>
                            <td><?php echo htmlspecialchars($phone["phone_model"]); ?></td>
                            <td><?php echo htmlspecialchars($phone["screen_size"]); ?></td>
                            <td><?php echo htmlspecialchars($phone["camera_megapixels"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
    <?php render_flash_messages(); ?>
</body>
</html>