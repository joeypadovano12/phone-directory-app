<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$search = trim($_GET["q"] ?? "");
$roles = [];

if (isset($_POST["role_id"])) {
    $role_id = (int)$_POST["role_id"];

    if ($role_id !== 0) {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "UPDATE Roles
                 SET is_active = !is_active
                 WHERE id = :id"
            );
            $stmt->execute([":id" => $role_id]);
            flash("Role status updated.", "success");
        } catch (PDOException $e) {
            error_log("Role status update failed: " . $e->getMessage());
            flash("Could not update the role status.", "danger");
        }
    }

    header("Location: " . project_url("admin/list_roles.php"));
    exit;
}

try {
    $params = [];
    $where = "";

    if ($search !== "") {
        $where = "WHERE name LIKE :search OR description LIKE :search";
        $params[":search"] = "%$search%";
    }

    $db = getDB();
    $stmt = $db->prepare(
        "SELECT id, name, description, is_active, created, modified
         FROM Roles
         $where
         ORDER BY modified DESC
         LIMIT 10"
    );
    $stmt->execute($params);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Role list failed: " . $e->getMessage());
    flash("Could not load roles.", "danger");
}
?>
<!-- TODO add the list roles HTML snippet here. -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Roles</title>
</head>
<body>
    <?php render_nav(); ?>
    <h1>List Roles</h1>

    <form method="get">
        <label for="q">Search</label>
        <input id="q" name="q" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?php echo htmlspecialchars($role["name"]); ?></td>
                    <td><?php echo htmlspecialchars($role["description"]); ?></td>
                    <td><?php echo $role["is_active"] ? "Active" : "Inactive"; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="role_id" value="<?php echo (int)$role["id"]; ?>">
                            <button type="submit"><?php echo $role["is_active"] ? "Deactivate" : "Activate"; ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php render_flash_messages(); ?>
</body>
</html>