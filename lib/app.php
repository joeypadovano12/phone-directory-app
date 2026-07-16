/* 
UCID: jp239
Date: 7/13/26
*/

<?php
session_start();

require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/render_functions.php");
// url_helpers.php must load before helpers or partials that call project_url().
require_once(__DIR__ . "/url_helpers.php");
require_once(__DIR__ . "/validations.php");
// Keep user_helpers.php before role_helpers.php.
// has_role() depends on is_logged_in().
require_once(__DIR__ . "/user_helpers.php");
require_once(__DIR__ . "/flash_messages.php");
require_once(__DIR__ . "/duplicate_user_details.php");
// require_role() depends on flash() and project_url().
require_once(__DIR__ . "/role_helpers.php");
?>