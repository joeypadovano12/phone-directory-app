<?php
// UCID: jp2397, Date: 7/13/26
require_once(__DIR__ . "/../../../lib/app.php");
require_role("Admin");

$db = getDB();
$userSearch = trim($_GET["username"] ?? $_POST["username"] ?? "");
$phoneSearch = trim($_GET["phone_search"] ?? $_POST["phone_search"] ?? "");
$users = [];
$phones = [];

if (isset($_POST["action"]) && $_POST["action"] === "toggle_phones") {
    // array_map("intval", ...) converts checkbox values from strings to integers.
    // array_filter(...) removes invalid ids after conversion.
    // array_unique(...) avoids toggling the same pair twice from duplicate input.
    require_role("Admin");
    $userIds = array_unique(
        array_filter(
            array_map("intval", $_POST["users"] ?? []),
            function ($id) {
                return $id > 0;
            }
        )
    );

    // Role ids can include -1 for the seeded Admin role, so only 0 is rejected.
    $phoneIds = array_unique(
        array_filter(
            array_map("intval", $_POST["phones"] ?? []),
            function ($id) {
                return $id !== 0;
            }
        )
    );

    if (empty($userIds) || empty($phoneIds)) {
        flash("Select at least one user and one phone.", "warning");
    } else {
        try {
            $stmt = $db->prepare(
                "INSERT INTO user_phones (user_id, phone_id, is_active)
                 VALUES (:user_id, :phone_id, 1)
                 ON DUPLICATE KEY UPDATE
                    is_active = IF(is_active = 1, 0, 1)"
            );
            $toggledCount = 0;
            $failedCount = 0;

            foreach ($userIds as $userId) {
                foreach ($phoneIds as $phoneId) {
                    try {
                        $stmt->execute([
                            ":user_id" => $userId,
                            ":phone_id" => $phoneId,
                        ]);
                        $toggledCount++;
                    } catch (PDOException $e) {
                        $failedCount++;
                        error_log(
                            "Phone assignment toggle failed for user $userId"
                                . " and phone $phoneId: " . $e->getMessage()
                        );
                    }
                }
            }

            if ($toggledCount > 0) {
                flash("$toggledCount selected user-phone pair(s) were toggled.", "success");
            }

            if ($failedCount > 0) {
                flash("$failedCount selected pair(s) could not be updated.", "warning");
            }
        } catch (PDOException $e) {
            error_log("Phone assignment setup failed: " . $e->getMessage());
            flash("Could not update phone assignments.", "danger");
        }
    }

    $redirect = "admin/assign_phones.php";
    $queryParams = [];
    if ($userSearch !== "") {
        $queryParams[] = "username=" . rawurlencode($userSearch);
    }
    if ($phoneSearch !== "") {
        $queryParams[] = "phone_search=" . rawurlencode($phoneSearch);
    }
    if (!empty($queryParams)){
        $redirect .= "?" . implode("&", $queryParams);
    }

    header("Location: " . project_url($redirect));
    exit;
}

if ($userSearch !== "" || $phoneSearch !== "") {
    try {
        if ($userSearch !== "") {
            $stmt = $db->prepare(
            "SELECT Users.id, Users.username, Users.email,
                    GROUP_CONCAT(
                            CONCAT(
                            project_phones.phone_model,
                            ' (',
                            IF(user_phones.is_active = 1, 'active', 'inactive'),
                            ')'
                        )
                        ORDER BY project_phones.phone_model
                        SEPARATOR ', '
                    ) AS project_phones
            FROM Users
            LEFT JOIN user_phones
                ON Users.id = user_phones.user_id
            LEFT JOIN project_phones
                ON project_phones.id = user_phones.phone_id
            WHERE Users.username LIKE :username
            GROUP BY Users.id, Users.username, Users.email
            ORDER BY Users.username
            LIMIT 25"
        );
        $stmt->execute([":username" => "%$userSearch%"]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        if ($phoneSearch !== "") {
            $stmt = $db->prepare(
                "SELECT id, phone_brand,phone_model
                FROM project_phones
                WHERE phone_brand LIKE :b_search OR phone_model LIKE :m_search
                ORDER BY phone_model
                LIMIT 25"
            );
            $stmt->execute([":b_search" => "%$phoneSearch%", ":m_search" => "%$phoneSearch%"]);
            $phones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        error_log("Phone assignment page load failed: " . $e->getMessage());
        flash("Could not load users and phones.", "danger");
    }
}
?>
<!-- TODO add the assign roles markup snippet here. -->
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Phones</title>
</head>

<body>
    <?php render_nav(); ?>
    <h1>Assign Phones</h1>

    <form method="get">
        <label for="username">Find Users</label>
        <input id="username" name="username" value="<?php echo htmlspecialchars($userSearch); ?>">
        <label for="phone_search">Find Phones</label>
        <input id="phone_search" name="phone_search" value="<?php echo htmlspecialchars($phoneSearch); ?>">
        <button type="submit">Search</button>
    </form>

    <form id="toggleForm" method="post">
        <input type="hidden" name="action" value="toggle_phones">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($userSearch); ?>">
        <input type="hidden" name="phone_search" value="<?php echo htmlspecialchars($phoneSearch); ?>">

    </form>

    <?php if ($userSearch !== "" || $phoneSearch !== ""): ?>
        <table>
            <thead>
                <tr>
                    <th>Users</th>
                    <th>Phones To Toggle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php if (!empty($users)): ?>
                            <table>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <input form="toggleForm"
                                                id="user_<?php echo (int)$user["id"]; ?>"
                                                type="checkbox"
                                                name="users[]"
                                                value="<?php echo (int)$user["id"]; ?>">
                                            <label for="user_<?php echo (int)$user["id"]; ?>">
                                                <a href="../public_profile.php?id=<?php echo $user["id"]; ?>">
                                                    <?php echo htmlspecialchars($user["username"]); ?>
                                                </a>
                                            </label>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($user["project_phones"] ?? "No phones"); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php else: ?>
                            <p>No users found.</p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($phones)): ?>
                            <?php foreach ($phones as $phone): ?>
                                <div>
                                    <input form="toggleForm"
                                        id="phone_<?php echo (int)$phone["id"]; ?>"
                                        type="checkbox"
                                        name="phones[]"
                                        value="<?php echo (int)$phone["id"]; ?>">
                                    <label for="phone_<?php echo (int)$phone["id"]; ?>">
                                        <?php echo htmlspecialchars($phone["phone_brand"] . " " . $phone["phone_model"]); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No active phones found.</p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <button form="toggleForm" type="submit">Toggle Selected Pairs</button>
    <?php endif; ?>

    <?php render_flash_messages(); ?>
</body>

</html>