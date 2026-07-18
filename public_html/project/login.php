<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$identifier = "";
$user = false;

if (isset($_POST["identifier"], $_POST["password"])) {
    $identifier = trim($_POST["identifier"]);
    $password = $_POST["password"];
    $isEmailLogin = str_contains($identifier, "@");

    if ($identifier === "") {
        $errors[] = "Enter your email or username.";
    } elseif ($isEmailLogin) {
        $identifier = sanitize_email($identifier);
        validate_email($identifier, $errors);
    } else {
        $identifier = strtolower($identifier);
        validate_username($identifier, $errors);
    }
    validate_password($password, $errors);

    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare(
                "SELECT id AS user_id, email, username, password_hash
                 FROM Users
                    WHERE username = :identifier
                    OR email = :identifier
                 LIMIT 1"
            );
            $stmt->execute([":identifier" => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Login query failed: " . $e->getMessage());
            $errors[] = "Login failed. Please try again.";
        }
    }

    /*if (empty($errors) && !$user) {
        $errors[] = "Email not found.";
    } elseif (empty($errors) && !password_verify($password, $user["password_hash"])) {
        $errors[] = "Invalid password.";
    }*/
    if (
        empty($errors)
        && (!$user || !password_verify($password, $user["password_hash"]))
    ) {
        $errors[] = "Invalid email or password.";
    }

    if (empty($errors)) {
        session_regenerate_id(true);
        $user["user_id"] = (int) $user["user_id"];
        unset($user["password_hash"]);
        $user["roles"] = get_user_roles($user["user_id"]);
        $_SESSION["user"] = $user;
        // Add flash feedback before the existing redirect.
        flash("Welcome back.", "success");
        header("Location: dashboard.php");
        exit;
    }
    // Any validation, lookup, or password errors collected above redirect back to the form.
    flash_errors($errors);
    header("Location: login.php");
    exit;
}

$message = implode("<br>", array_map("htmlspecialchars", $errors));
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
    <?php render_nav(); ?>
    <h1>Login</h1>

    <form method="post" action="login.php" onsubmit="return validate(this)">
        <!-- Existing form structure stays the same. Replace only the login identifier field. -->
        <!-- JS validation is a better place to split email-vs-username checks. -->
        <label for="identifier">Email or Username</label>
        <input
            type="text"
            id="identifier"
            name="identifier"
            required
            autocomplete="username"
            pattern="(?:[a-z0-9_\-]{3,30}|[^@\s]+@[^@\s]+\.[^@\s]+)"
            title="Enter a username or email address"
            value="<?php echo htmlspecialchars($identifier ?? ""); ?>">

        <label for="password">Password</label>
        <input id="password" name="password" type="password"
            required minlength="8"
            autocomplete="current-password">

        <button type="submit">Login</button>
    </form>
    <script>
        function validate(form) {
            const errors = [];

            const identifier = form.identifier.value.trim();
            // Keep this client-side shape aligned with the server-side email check.
            // PHP validation is still the final authority after the form posts.
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const usernamePattern = /^[a-z0-9_-]{3,30}$/;

            if (!identifier) {
                errors.push("Enter your email or username.");
            } else if (identifier.includes("@")) {
                if (!emailPattern.test(identifier)) {
                    errors.push("Enter a valid email address.");
                }
            } else if (!usernamePattern.test(identifier)) {
                errors.push("Use 3-30 lowercase letters, numbers, underscores, or hyphens.");
            }
            validate_password(form.password, errors);

            return show_validation_errors(errors);
        }
    </script>
    <!-- Last PHP inside <body> so it captures messages queued during this request. -->
    <?php render_flash_messages(); ?>
</body>

</html>