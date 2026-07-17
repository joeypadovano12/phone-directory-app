<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$errors = [];
$name = "";
$description = "";

if (isset($_POST["name"], $_POST["description"])) {
    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if (strlen($name) < 2 || strlen($name) > 30) {
        $errors[] = "Role name must be 2-30 characters.";
    }

    if (!preg_match('/^[A-Za-z][A-Za-z0-9 ._-]*$/', $name)) {
        $errors[] = "Role name must start with a letter and use only letters, numbers, spaces, periods, underscores, or hyphens.";
    }

    if (strlen($description) > 255) {
        $errors[] = "Description must be 255 characters or less.";
    }

    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "INSERT INTO Roles (name, description)
                 VALUES (:name, :description)"
            );
            $stmt->execute([
                ":name" => $name,
                ":description" => $description,
            ]);

            flash("Role created.", "success");
            header("Location: " . project_url("admin/list_roles.php"));
            exit;
        } catch (PDOException $e) {
            $errorInfo = $e->errorInfo ?? [];

            if (($errorInfo[0] ?? "") === "23000" && ($errorInfo[1] ?? 0) === 1062) {
                $errors[] = "That role name already exists.";
            } else {
                error_log("Create role failed: " . $e->getMessage());
                $errors[] = "Could not create the role.";
            }
        }
    }

    flash_errors($errors);
}
?>
<!-- TODO add the create role HTML snippet here. -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Role</title>
</head>
<body>
    <?php render_nav(); ?>
    <h1>Create Role</h1>

    <form method="post">
        <label for="name">Role Name</label>
        <input id="name" name="name" required minlength="2" maxlength="30"
            pattern="[A-Za-z][A-Za-z0-9 ._\-]*"
            value="<?php echo htmlspecialchars($name); ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" maxlength="255"><?php
            echo htmlspecialchars($description);
        ?></textarea>

        <button type="submit">Create Role</button>
    </form>

    <?php render_flash_messages(); ?>
</body>
</html>