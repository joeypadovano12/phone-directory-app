<?php
// public_html/project/admin/_phones.php
require_once(__DIR__ . "/../../../lib/app.php");

if (!has_role("Admin")) {
    flash("You do not have permission to view this page.", "warning");
    header("Location: " . project_url("index.php"));
    exit;
}

$brand_search = $_GET["brand_search"] ?? "";
$limit = (int)($_GET["limit"] ?? 10);
if($limit < 1 || $limit > 100){
    $limit = 10;
}

$sort = $_GET["sort"] ?? "brand";
$order_by = "ORDER BY p.phone_brand DESC";
if ($sort === "model"){
    $order_by = "ORDER BY p.phone_model ASC";
}

$where = "WHERE p.id NOT IN (SELECT phone_id FROM user_phones WHERE is_active = 1)";
$params = [];

if ($brand_search){
    $where .= " AND p.phone_brand LIKE :brand";
    $params[":brand"] = "%$brand_search%";
}

$matching_count = 0;
$phones = [];

try {
    $db = getDB();

    $count_stmt = $db->prepare(
        "SELECT COUNT(*) AS total
         FROM project_phones p
         $where"
    );

    $count_stmt->execute($params);
    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $matching_count = (int)($count_row["total"] ?? 0);

    $stmt = $db->prepare(
        "SELECT p.id, p.phone_brand, p.phone_model, p.screen_size, p.camera_megapixels, p.created
         FROM project_phones p
         $where
         $order_by
         LIMIT $limit"
    );
    $stmt->execute($params);
    $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Unassociated phone list failed: " . $e->getMessage());
    flash("Unassociated phones could not be loaded.", "danger");
}
$shown_count = count($phones);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unassociated Phones</title></head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
            <h1>Unassociated Phones</h1>
        </div>

        <form method="GET">
            <label>Search Brand: <input type="text" name="brand_search" value="<?php echo htmlspecialchars($brand_search); ?>"></label>
            <label>Sort By:
                <select name="sort">
                    <option value="brand" <?php echo $sort === 'brand' ? 'selected' : ''; ?>>Brand Name (Default)</option>
                    <option value="model" <?php echo $sort === 'model' ? 'selected' : ''; ?>>Model Name </option>
                </select>
            </label>
            <label>Limit: <input type="number" name="limit" value="<?php echo $limit; ?>" min="1" max="100"></label>
            <button type="submit">Filter</button>
        </form>

        <p>Showing <?php echo $shown_count; ?> of <?php echo $matching_count; ?> unassociated phones.</p>

        <?php if ($matching_count === 0): ?>
            <p>No unassociated phones match the selected filters.</p>
        <?php else: ?>
            <table class="table">
                <tr><th>ID</th><th>Brand</th><th>Model</th><th>Created</th><th>Action</th></tr>
                <?php foreach ($phones as $phone): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($phone["id"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["phone_brand"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["phone_model"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["created"]); ?></td>
                        <td>
                            <a href="<?php echo project_url('view_phone.php?id=' . urlencode($phone["id"])); ?>">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>