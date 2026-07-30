<?php
// public_html/project/internal/toggle_saved_phone.php
/**
 * Usage contract:
 * - Accepts POST requests from the Save/Remove form in partials/guide_card.php.
 * - Requires a logged-in user.
 * - Expects guide_id as a positive integer and new_is_saved as 0 or 1.
 * - Accepts an optional local return_to path and query string for an approved page.
 * - Updates only the current session user's relationship, then flashes and redirects.
 */
require_once(__DIR__ . "/../../../lib/app.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . project_url("list_phones.php"));
    exit;
}

if (!is_logged_in()) {
    flash("Log in before saving a phone.", "warning");
    header("Location: " . project_url("login.php"));
    exit;
}

$return_url = "../admin/list_phones.php";
$requested_return = $_POST["return_to"] ?? "";
$allowed_return_paths = [
    project_url("list_phones.php"),
    project_url("admin/list_phones.php"),
    project_url("view_phone.php"),
    $requested_return
];
// Reject line breaks that could alter the redirect header, then allow only known local pages.
if (
    is_string($requested_return)
    && !str_contains($requested_return, "\r")
    && !str_contains($requested_return, "\n")
) {
    foreach ($allowed_return_paths as $allowed_path) {
        if (
            $requested_return === $allowed_path
            || str_starts_with($requested_return, $allowed_path . "?")
        ) {
            $return_url = $requested_return;
            break;
        }
    }
}

$phone_id = filter_input(INPUT_POST, "phone_id", FILTER_VALIDATE_INT);
if (!$phone_id) {
    flash("Choose a valid phone.", "warning");
    header("Location: " . $return_url);
    exit;
}

$new_is_saved = filter_input(
    INPUT_POST,
    "new_is_saved",
    FILTER_VALIDATE_INT
);
if (!in_array($new_is_saved, [0, 1], true)) {
    flash("Choose a valid saved-phone action.", "warning");
    header("Location: " . $return_url);
    exit;
}

$user_id = get_user_id();

try {
    $db = getDB();
    $query = "INSERT INTO user_phones (user_id, phone_id, is_active)
            VALUES (:user_id, :phone_id, :is_active)
            ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)";    
        
    $stmt = $db->prepare($query);
    $stmt->execute([
        ":user_id" => $user_id,
        ":phone_id" => $phone_id,
        ":is_active" => $new_is_saved,
    ]);
    if ($new_is_saved === 1) {
        flash("Phone saved.", "success");
    } else {
        flash("Phone removed from your saved list.", "success");
    }
} catch (Throwable $e) {
    error_log("Saved phone toggle failed: " . $e->getMessage());
    flash("The saved phone could not be updated.", "danger");
    flash("Database Error: " . $e->getMessage(), "danger");
}

header("Location: " . $return_url);
exit;