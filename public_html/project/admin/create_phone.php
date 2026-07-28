<?php
// public_html/project/admin/create_phone.php
require_once(__DIR__ . "/../../../lib/app.php");
require_once(__DIR__ . "/../../../lib/project_api.php");
require_once(__DIR__ . "/../../../lib/db_helpers.php");
require_role("Admin");

$errors = [];
$rows = [];
$active_form = "fetch";

if (isset($_POST["fetch_phones"])) {
    $active_form = "fetch";
    $search = "";
    if (isset($_POST["search"])) {
        $submitted_search = $_POST["search"];
        if (is_string($submitted_search)) {
            $search = trim($submitted_search);
        }
    }

    if ($search === "") {
        $errors[] = "Enter search text before calling the API.";
    }

    if (empty($errors)) {
        try {
            $rows = fetch_phone_data($search, "api", $errors);
        } catch (Throwable $e) {
            error_log("Fetch phones failed: " . $e->getMessage());
            $errors[] = "Unable to search for phones right now.";
        }

        if (empty($errors) && !$rows) {
            $errors[] = "No phones matched that search.";
        }
    }
} elseif (isset($_POST["create_phone"])) {
    $active_form = "create";
    try {
        $brand = $_POST["phone_brand"] ?? "";
        $model = $_POST["phone_model"] ?? "";
        $screen = $_POST["screen_size"] ?? "";
        $camera = $_POST["camera_megapixels"] ?? "";

        if (
            !is_string($brand) || !is_string($model) || !is_string($screen)
            || !is_string($camera)) 
        {
            throw new InvalidArgumentException("Enter both a brand and model.");
        }
        if (trim($brand) === "" || trim($model) === "") {
            throw new InvalidArgumentException("Enter both a brand and model.");
        }

        // Wrap one manual row so API and manual paths use the same insert loop.
        $rows[] = [
            "phone_brand" => trim($brand),
            "phone_model" => trim($model),
            "screen_size" => trim($screen),
            "camera_megapixels" => trim($camera),
            "is_api" => 0,
        ];
    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (Throwable $e) {
        error_log("Create phone input failed: " . $e->getMessage());
        $errors[] = "Unable to process the phone values.";
    }
}

if ($rows && empty($errors)) {
    $saved = 0;
    $duplicates = 0;
    try {
        foreach ($rows as $row) {
            try {
                // insert($table_name, $data, $opts): table name, one row, optional settings.
                $result = insert("project_phones", $row);
                $saved += $result["rowCount"];
            } catch (PDOException $e) {
                $error_code = 0;
                if (isset($e->errorInfo[1])) {
                    $error_code = (int)$e->errorInfo[1];
                }

                // Skip this duplicate, then continue saving later rows.
                if ($error_code === 1062) {
                    $duplicates++;
                    continue;
                }
                throw $e;
            }
        }

        if ($saved > 0) {
            flash("Saved $saved phone record(s).", "success");
        }
        if ($duplicates > 0) {
            flash("Skipped $duplicates duplicate phone record(s).", "warning");
        }
        header("Location: list_phones.php");
        exit;
    } catch (Throwable $e) {
        error_log("Phone insert helper failed: " . $e->getMessage());
        flash("CRASH REASON: " . $e->getMessage(), "danger");
    }
}

flash_errors($errors);
?>
<!-- TODO add the create company form snippet here. -->
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Phone</title>
</head>

<body>
    <?php render_nav(); ?>
    <main>
        <h1>Create Phone</h1>

        <div aria-label="Phone creation mode" role="group">
            <button type="button" data-form-mode-button="fetch">Search The API</button>
            <button type="button" data-form-mode-button="create">Create Manually</button>
        </div>

        <section data-form-mode-panel="fetch" <?php if ($active_form !== "fetch") { echo "hidden"; } ?>>
            <form method="post">
                <h2>Search The API</h2>
                <label for="search">Search text</label>
                <input id="search" name="search" required>
                <button name="fetch_phones" value="1" type="submit">Search Phones</button>
            </form>
        </section>

        <section data-form-mode-panel="create" <?php if ($active_form !== "create") { echo "hidden"; } ?>>
            <form method="post">
                <h2>Create Manually</h2>
                <label for="brand">Brand</label>
                <input id="brand" name="phone_brand" required>

                <label for="model">Model</label>
                <input id="model" name="phone_model" required>

                <label for="screen">Screen Size</label>
                <input id="screen" name="screen_size" maxlength="50">

                <label for="camera">Camera MP</label>
                <input id="camera" name="camera_megapixels" maxlength="50">

                <button name="create_phone" value="1" type="submit">Create Phone</button>
            </form>
        </section>
    </main>
    <?php render_flash_messages(); ?>
<?php render_scripts(); ?>
    <script>
            const phoneFormButtons = document.querySelectorAll("[data-form-mode-button]");
            const phoneFormPanels = document.querySelectorAll("[data-form-mode-panel]");

            function showPhoneForm(mode) {
               phoneFormPanels.forEach(function(panel) {
                    const matches = panel.dataset.formModePanel === mode;
                    panel.hidden = !matches;
                    panel.style.display = matches ? "block" : "none";
                });
                phoneFormButtons.forEach(function(button) {
                    button.setAttribute("aria-pressed", button.dataset.formModeButton === mode ? "true" : "false");
                });
            }

            phoneFormButtons.forEach(function(button) {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    showPhoneForm(button.dataset.formModeButton);
                });
            });
            showPhoneForm("<?php echo $active_form ?? 'fetch'; ?>");
    </script>
</body>

</html>