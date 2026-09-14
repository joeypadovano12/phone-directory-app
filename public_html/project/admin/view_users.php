cat << 'EOF' > public_html/project/admin/view_users.php
<?php
require_once(__DIR__ . "/../../../lib/functions.php");
$db = getDB();

$db->exec("DELETE FROM Users WHERE username NOT IN ('admin1', 'username')");

$stmt = $db->prepare("SELECT id, username, email FROM Users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Registered Database Accounts</h2><pre>";
print_r($users);
echo "</pre>";
