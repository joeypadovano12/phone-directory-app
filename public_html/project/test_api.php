<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../lib/app.php");
require_once(__DIR__ . "/../../lib/project_api.php");

$search = "Apple";
if (isset($_POST["search"])) {
    $submitted_search = $_POST["search"];
    if (is_string($submitted_search)) {
        $search = trim($submitted_search);
    } else {
        $search = "";
    }
}
$decoded = null;
$errors = [];

if (isset($_POST["source"])) {
    $source = $_POST["source"];

    if ($source !== "live" && $source !== "sample") {
        $errors[] = "Choose a valid API source.";
    } elseif ($source === "live" && $search === "") {
        $errors[] = "Enter search text before sending the request.";
    }

    if (empty($errors)) {
        $decoded = fetch_phone_data($search, $source, $errors);
    }
}

flash_errors($errors);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Phone API</title>
</head>

<body>
    <?php render_nav(); ?>
    <main>
        <h1>Search Phone API</h1>
        <form method="post">
            <label for="search">Search text</label>
            <input id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" required>
            <button name="source" value="live" type="submit">Search Live API</button>
            <button name="source" value="sample" type="submit">Use Cached Sample</button>
        </form>

        <pre><?php var_dump($decoded); ?></pre>
    </main>
    <?php render_flash_messages(); ?>
</body>

</html>