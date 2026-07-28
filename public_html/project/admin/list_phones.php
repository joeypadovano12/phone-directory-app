<?php
// public_html/project/admin/list_phones.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$phones = [];
$brand_search = $_GET["brand_search"] ?? "";
$limit = (int)($_GET["limit"] ?? 10);

if($limit < 1 || $limit > 100) {
    $limit = 10;
}

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
    $query = "SELECT id, phone_brand, phone_model, screen_size, camera_megapixels, is_api
            FROM project_phones ";
    if ($brand_search){
        $query .= "WHERE phone_brand LIKE :brand";
    }

    $query .= " ORDER BY modified DESC LIMIT :limit";

    $db = getDB();
    $stmt = $db->prepare($query);
    if ($brand_search){
        $stmt->bindValue(":brand", "%$brand_search%");
    }
    $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmt->execute();
    $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <form method="GET">
            Search Brand:
            <input name="brand_search" value="<?php echo htmlspecialchars($brand_search); ?>">
            Limit:
            <input type="number" name="limit" value="<?php echo $limit; ?>" min="1" max="100" >
            <button type="submit">Filter</button>
        </form>
        <br>

        <?php if (count($phones) === 0): ?>
            <p>No phones found matching your search</p>
        <?php else: ?>
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
                        <td>
                            <a href="view_phone.php?id=<?php echo urlencode($phone["id"]); ?>">View</a> |
                            <a href="edit_phone.php?id=<?php echo urlencode($phone["id"]); ?>">Edit</a> |
                            <a href="delete_phone.php?id=<?php echo urlencode($phone["id"]); ?>">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </main>
    <?php render_flash_messages(); ?>
<?php render_scripts(); ?>
</body>
</html>