<?php
// public_html/project/admin/all_saved_phones.php
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

$username_search = $_GET["username_search"] ?? "";

$sort = $_GET["sort"] ?? "modified";
$order_by = "ORDER BY up.modified DESC";
if ($sort === "brand"){
    $order_by = "ORDER BY p.phone_brand ASC";
}
elseif ($sort === "username"){
    $order_by = "ORDER BY u.username ASC";
}

$where = "WHERE up.is_active = 1";
$params = [];

if ($brand_search){
    $where .= " AND p.phone_brand LIKE :brand";
    $params[":brand"] = "%$brand_search%";
}

if ($username_search){
    $where .= " AND u.username LIKE :username";
    $params[":username"] = "%$username_search%";
}

$matching_count = 0;
$phones = [];

try {
    $db = getDB();

    $count_stmt = $db->prepare(
        "SELECT COUNT(*) AS total
         FROM user_phones up
         JOIN project_phones p ON p.id = up.phone_id
         JOIN Users u ON u.id = up.user_id
         $where"
    );

    $count_stmt->execute($params);
    $count_row = $count_stmt->fetch(PDO::FETCH_ASSOC);
    $matching_count = (int)($count_row["total"] ?? 0);

    $stmt = $db->prepare(
        "SELECT p.id, p.phone_brand, p.phone_model, p.screen_size, p.camera_megapixels,
         up.id AS relationship_id, up.created, up.modified AS saved_on,
         u.id AS user_id, u.username
         FROM user_phones up
         JOIN project_phones p ON p.id = up.phone_id
         JOIN Users u ON u.id = up.user_id
         $where
         $order_by
         LIMIT $limit"
    );
    $stmt->execute($params);
    $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Saved phone list failed: " . $e->getMessage());
    flash("Saved phones could not be loaded.", "danger");
}
$shown_count = count($phones);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Saved Phones</title></head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <div class="d-flex flex-wrap justify-content-between gap-2 align-items-center">
            <h1>All Saved Phones</h1>
        </div>

        <form method="GET">
            <label>Search Brand: <input type="text" name="brand_search" value="<?php echo htmlspecialchars($brand_search); ?>"></label>
            <label>Username: <input type="text" name="username_search" value="<?php echo htmlspecialchars($username_search); ?>"></label>
            <label>Sort By:
                <select name="sort">
                    <option value="modified" <?php echo $sort === 'modified' ? 'selected' : ''; ?>>Date Saved (Default)</option>
                    <option value="brand" <?php echo $sort === 'brand' ? 'selected' : ''; ?>>Brand Name </option>
                    <option value="username" <?php echo $sort === 'username' ? 'selected' : ''; ?>>Username </option>
                </select>
            </label>
            <label>Limit: <input type="number" name="limit" value="<?php echo $limit; ?>" min="1" max="100"></label>
            <button type="submit">Filter</button>
        </form>

        <p>Showing <?php echo $shown_count; ?> of <?php echo $matching_count; ?> saved phones.</p>

        <?php if ($matching_count === 0): ?>
            <p>No saved phones match the selected filters.</p>
        <?php else: ?>
            <table class="table">
                <tr><th>User</th><th>Rel. ID</th><th>Brand</th><th>Model</th><th>Created</th><th>Saved On</th><th>Action</th></tr>
                <?php foreach ($phones as $phone): ?>
                    <tr>
                        <td><a href="<?php echo project_url('profile.php?id=' . $phone["user_id"]); ?>"><?php echo htmlspecialchars($phone["username"]); ?></a></td>
                        <td><?php echo htmlspecialchars($phone["relationship_id"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["phone_brand"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["phone_model"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["created"]); ?></td>
                        <td><?php echo htmlspecialchars($phone["saved_on"]); ?></td>
                        <td>
                            <a href="<?php echo project_url('view_phone.php?id=' . $phone["id"]); ?>">View</a> |
                            <form method="POST" action="<?php echo project_url('internal/toggle_saved_phone.php'); ?>" style="display:inline;">
                                <input type="hidden" name="phone_id" value="<?php echo $phone["id"]; ?>">
                                <input type="hidden" name="target_user_id" value="<?php echo $phone["user_id"]; ?>">
                                <input type="hidden" name="new_is_saved" value="0">
                                <input type="hidden" name="return_to" value="<?php echo $_SERVER["REQUEST_URI"]; ?>">
                                <button type="submit" style="background:none; border:none; color:blue; text-decoration:underline; cursor:pointer; padding:0;">Unsave</button>
                            </form>
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