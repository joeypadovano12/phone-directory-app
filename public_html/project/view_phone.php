<?php
// public_html/project/view_phone.php
require_once(__DIR__ . "/../../lib/app.php");

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing phone id.", "danger");
    header("Location: " . project_url("list_phones.php"));
    exit;
}

$phone = null;
try {
    $phone = select(
        "SELECT id, phone_brand, phone_model, screen_size,
        camera_megapixels, is_api, created, modified
         FROM project_phones
         WHERE id = :id
         LIMIT 1",
        ["id" => $id]
    );
} catch (Throwable $e) {
    error_log("Phone lookup failed: " . $e->getMessage());
    flash("The phone details could not be loaded.", "danger");
    header("Location: " . project_url("list_phones.php"));
    exit;
}
if ($phone === null) {
    flash("Phone not found.", "warning");
    header("Location: " . project_url("list_phones.php"));
    exit;
}
?>
<!doctype html>
<html lang="en">
<head><?php render_head($phone["phone_brand"] . " " . $phone["phone_model"]); ?></head>
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
                    <a class="btn btn-danger" href="<?php echo project_url("admin/delete_phone.php?id=" . $phone["id"]); ?>">Delete</a>
                <?php endif; ?>

                <a class="btn btn-secondary" href="<?php echo project_url("list_phones.php"); ?>">Back To Phones</a>
            </div>
        </article>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>