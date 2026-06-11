<?php

require_once(__DIR__ . "/../lib/db.php");

$query = "SELECT 'It worked!' AS `From Database`";

try {
	$db = getDB(); // available from lib/db.php
	$stmt = $db->query($query); // run the query above
	$result = $stmt->fetch(); // get the result
    // dump result to screen
	echo "<pre>" . var_export($result, true) . "</pre>";
} catch (Throwable $e) {
	error_log("test_db.php error: " . var_export($e, true));
	echo "Database test failed. Check logs/terminal for details.";
}
