<?php
// public_html/project/admin/list_phones.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$phones = [];

try {
    /*$db = getDB();
    $stmt = $db->prepare(
        "SELECT id, symbol, name, type, region, is_api
         FROM Companies
         ORDER BY modified DESC
         LIMIT 10"
    );
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);*/
    $phones = selectAll(
        "SELECT id, phone_brand, phone_model, screen_size, camera_megapixels, is_api
         FROM project_phones
         ORDER BY modified DESC
         LIMIT 10"
    );
} catch (PDOException $e) {
    error_log("List phones failed: " . $e->getMessage());
    flash("Unable to load phones    .", "danger");
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phones</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Phones</h1>
        <table>
            <thead>
                <tr><th>Brand</th><th>Model</th><th>Screen Size</th><th>Camera MP</th><th>Source</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($phones as $phone): ?>
                    <?php
                    $source_label = "Manual";
                    if ($phone["is_api"]) {
                        $source_label = "API";
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($phone["phone_brand"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["phone_model"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["screen_size"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["camera_megapixels"]); ?></td>
                        <td><?php echo $source_label; ?></td>
                        <td><a href="edit_phone.php?id=<?php echo urlencode($phone["id"]); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php render_flash_messages(); ?>
<?php render_scripts(); ?>
</body>
</html>