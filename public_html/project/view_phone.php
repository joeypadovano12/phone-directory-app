<?php
// public_html/project/view_phone.php
require_once(__DIR__ . "/../../lib/app.php");
require_once(__DIR__ . "/../../lib/db_helpers.php");


$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing phone id.", "danger");
    header("Location: list_phones.php");
    exit;
}

$phone = null;
try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT id, phone_brand, phone_model, screen_size,
        camera_megapixels, is_api, created, modified
         FROM project_phones
         WHERE id = :id
         LIMIT 1"
    );
    $stmt->execute([":id" => $id]);
    $phone = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Phone lookup failed: " . $e->getMessage());
    flash("DB Error: " . $e->getMessage(), "danger");
    header("Location: list_phones.php");
    exit;
}
if (empty($phone)) {
    flash("Phone not found.", "warning");
    header("Location: list_phones.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($phone["phone_brand"] . " " . $phone["phone_model"]); ?></title>
</head>
<body>
    <?php render_nav(); ?>
    <main class="container py-4">
        <article class="card">
            <div class="card-body">
                <h1 class="card-title"><?php echo htmlspecialchars($phone["phone_brand"] . " " . $phone["phone_model"]); ?></h1>

                <dl class="row">
                    <dt class="col-sm-3">Brand</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["phone_brand"] ?? "Unknown")); ?></dd>

                    <dt class="col-sm-3">Model</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["phone_model"] ?? "Unknown")); ?></dd>

                    <dt class="col-sm-3">Screen Size</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["screen_size"] ?? "Unknown")); ?></dd>

                    <dt class="col-sm-3">Camera</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["camera_megapixels"] ?? "Unknown")); ?></dd>

                    <dt class="col-sm-3">Data Source</dt>
                    <dd class="col-sm-9"><?php echo !empty($phone["is_api"]) ? "Imported from API" : "Manual Entry"; ?></dd>

                    <dt class="col-sm-3">Record Created</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["created"] ?? "Unknown")); ?></dd>

                    <dt class="col-sm-3">Last Modified</dt>
                    <dd class="col-sm-9"><?php echo htmlspecialchars((string) ($phone["modified"] ?? "Unknown")); ?></dd>
                </dl>

                <?php if (has_role("Admin")): ?>
                    <a class="btn btn-warning" href="<?php echo project_url("admin/edit_phone.php?id=" . $phone["id"]); ?>">Edit</a>
                    <form method="POST" action="<?php echo project_url('admin/delete_phone.php?id=' . $phone['id']); ?>" style="display: inline-block;">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                <?php endif; ?>

                <a class="btn btn-secondary" href="<?php echo project_url("list_phones.php"); ?>">Back To Phones</a>
            </div>
        </article>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>