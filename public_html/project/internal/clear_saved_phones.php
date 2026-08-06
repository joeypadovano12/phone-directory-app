<?php
// public_html/project/internal/clear_saved_phones.php
require_once(__DIR__ . "/../../../lib/app.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("That saved-phone action is not available.", "warning");
    header("Location: " . project_url("my_phones.php"));
    exit;
}

if (!is_logged_in()) {
    flash("Log in to update saved phones.", "warning");
    header("Location: " . project_url("login.php"));
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare(
        "UPDATE user_phones
         SET is_active = 0
         WHERE user_id = :user_id AND is_active = 1"
    );
    $stmt->execute([":user_id" => get_user_id()]);
    flash("All saved phones were removed.", "success");
} catch (Throwable $e) {
    error_log("Clear saved phones failed: " . $e->getMessage());
    flash("Saved phones could not be cleared.", "danger");
}

header("Location: " . project_url("my_phones.php"));
exit;