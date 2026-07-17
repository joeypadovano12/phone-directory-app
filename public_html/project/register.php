/* 
UCID: jp2397
Date: 7/13/26
*/

<?php
require_once(__DIR__ . "/../../lib/app.php");
$errors = [];
$email = "";
$username = "";

if (isset($_POST["email"], $_POST["username"], $_POST["password"], $_POST["confirm_password"])) {
    $email = sanitize_email($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    validate_email($email, $errors);
    validate_username($username, $errors);
    validate_password($password, $errors);
    validate_passwords_match($password, $confirmPassword, $errors);

    if (empty($errors)) {
        // TODO: connect to the database, hash the password, and insert the user.
        try {
            $db = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare(
                "INSERT INTO Users (email, username, password_hash)
             VALUES (:email, :username, :password_hash)"
            );
            $stmt->execute([
                ":email" => $email,
                ":username" => $username,
                ":password_hash" => $hash,
            ]);

            error_log("Registration insert succeeded for user id " . $db->lastInsertId());
            flash("Account created. Please log in.", "success");
            $email = "";
            $username = "";
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                duplicate_user_detail($e, $errors);
            } else {
                error_log("Registration failed: " . $e->getMessage());
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
    // Any validation or PDO errors collected above redirect back to the form.
    flash_errors($errors);
    //header("Location: register.php");
    //exit;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>

<body>
    <?php render_nav(); ?>
    <h1>Register</h1>

    <form method="post" action="register.php" onsubmit="return validate(this);">

        <label for="email">Email</label>
        <input id="email" name="email" type="email"
            required autocomplete="email"
            value="<?php echo htmlspecialchars($email); ?>">
        <label for="username">Username</label>
        <input id="username" name="username"
            required minlength="3" maxlength="30" pattern="[a-z0-9_\-]{3,30}" 
            title="Must be lowercase alphanumeric and can use underscore or hyphens"
            autocomplete="username"
            value="<?php echo htmlspecialchars($username); ?>">
        <label for="password">Password</label>
        <input id="password" name="password" type="password"
            required minlength="8" autocomplete="new-password">

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" name="confirm_password" type="password"
            required minlength="8" autocomplete="new-password">

        <button type="submit">Register</button>
    </form>
    <script>
        function validate(form) {

            const errors = [];

            validate_email(form.email, errors);
            validate_username(form.username, errors);
            validate_password(form.password, errors);
            validate_passwords_match(form.password, form.confirm_password, errors);

            return show_validation_errors(errors);
        }
    </script>
    <!-- Last PHP inside <body> so it captures messages queued during this request. -->
    <?php render_flash_messages(); ?>
</body>

</html>