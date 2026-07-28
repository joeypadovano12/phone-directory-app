<?php
// public_html/project/admin/delete_phone.php
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$allowed_return_pages = [
    "list_phones.php" => project_url("list_phones.php"),
    "admin/list_phones.php" => project_url("admin/list_phones.php"),
];
$return_to = $allowed_return_pages["list_phones.php"];
if (isset($_GET["return_to"]) && is_string($_GET["return_to"])) {
    $requested_return_to = $_GET["return_to"];
    if (isset($allowed_return_pages[$requested_return_to])) {
        $return_to = $allowed_return_pages[$requested_return_to];
    }
}

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
    flash("Missing phone id.", "danger");
    header("Location: " . $return_to);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Use the phone list to delete a phone.", "warning");
    header("Location: " . $return_to);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM project_phones WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $id]);

    if ($stmt->rowCount() === 1) {
        flash("Phone deleted.", "success");
    } else {
        flash("Phone not found.", "warning");
    }
} catch (PDOException $e) {
    error_log("Phone deletion failed: " . $e->getMessage());
    flash("The phone could not be deleted.", "danger");
}

header("Location: " . $return_to);
exit;