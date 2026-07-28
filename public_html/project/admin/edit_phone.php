<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// public_html/project/admin/edit_phone.php
require_once(__DIR__ . "/../../../lib/app.php");
require_once(__DIR__ . "/../../../lib/db_helpers.php");
require_role("Admin");

$errors = [];
$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing phone id", "danger");
    header("Location: list_phones.php");
    exit;
}

if (isset($_POST["save"])) {
    $updated_values = [];

    try {
        // Handle only the valid columns this brief edit form allows.
        foreach (["phone_brand", "phone_model", "screen_size", "camera_megapixels"] as $field_name) {
            $value = $_POST[$field_name] ?? "";
            // Reject unexpected array input before trimming and saving text.
            if (!is_string($value)) {
                throw new InvalidArgumentException("Enter a valid $field_name value.");
            }

            $updated_values[$field_name] = trim($value);
        }

        if ($updated_values["phone_brand"] === "" || $updated_values["phone_model"] === "") {
            throw new InvalidArgumentException("Enter both a brand and model.");
        }
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Edit phone input failed: " . $e->getMessage());
        $errors[] = "Unable to process the phone values.";
    }

    if (empty($errors)) {
        $data = [
            "id" => $id,
            "phone_brand" => $updated_values["phone_brand"],
            "phone_model" => $updated_values["phone_model"],
            "screen_size" => $updated_values["screen_size"],
            "camera_megapixels" => $updated_values["camera_megapixels"],

        ];

        try {
            update("project_phones", $data, ["id"], ["debug"=>true]);
            flash("Phone updated", "success");
            header("Location: edit_phone.php?id=$id");
            exit;
        } catch (Throwable $e) {
            error_log("Update phone failed: " . $e->getMessage());
            $errors[] = "Unable to update phone.";
        }
    }
}

flash_errors($errors);

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT `phone_brand`, `phone_model`, `screen_size`, `camera_megapixels` FROM project_phones WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);
    $phone = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Load phone for edit failed: " . $e->getMessage());
    flash("Unable to load that phone.", "danger");
    header("Location: list_phones.php");
    exit;
}
if (!$phone) {
    flash("Phone not found", "danger");
    header("Location: list_phones.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Phone</title>
</head>
<body>
    <?php render_nav(); ?>
    <main>
        <h1>Edit <?php echo htmlspecialchars($phone["phone_model"]); ?></h1>
        <form method="post">
            <label for="brand">Brand</label>
            <input id="brand" name="phone_brand" value="<?php echo htmlspecialchars($phone["phone_brand"]); ?>" required>

            <label for="model">Model</label>
            <input id="model" name="phone_model" value="<?php echo htmlspecialchars($phone["phone_model"]); ?>" required>

            <label for="screen">Screen Size</label>
            <input id="screen" name="screen_size" value="<?php echo htmlspecialchars($phone["screen_size"]); ?>">

            <label for="camera">Camera MP</label>
            <input id="camera" name="camera_megapixels" value="<?php echo htmlspecialchars($phone["camera_megapixels"]); ?>">

            <button name="save" value="1" type="submit">Save Phone</button>
        </form>
    </main>
    <?php render_flash_messages(); ?>
    <?php render_scripts(); ?>
</body>
</html>