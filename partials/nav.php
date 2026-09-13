<?php
// UCID: jp2397, Date: 7/13/26
// File: partials/nav.php
$isLoggedIn = is_logged_in();
?>
<link rel="stylesheet" href="/project/styles.css">
<nav>
    <ul>
        <?php if ($isLoggedIn): ?>
            <li><a href="/project/dashboard.php">Dashboard</a></li>
            <li><a href="/project/profile.php">Profile</a></li>
            <li><a href="/project/public_profile.php">Public Profile</a></li>
            <li><a href="<?php echo project_url('my_phones.php'); ?>">My Saved Phones</a></li>
            <li><a href="<?php echo project_url('list_phones.php'); ?>">List Phones</a></li>
            <?php if (has_role("Admin")): ?>
                <li><a href="<?php echo project_url('admin/create_phone.php'); ?>">Create Phone</a></li>
                <li><a href="<?php echo project_url('admin/list_phones.php'); ?>">List Phones</a></li>
                <li><a href="<?php echo project_url('admin/all_saved_phones.php'); ?>">All Saved Phones</a></li>
                <li><a href="<?php echo project_url('admin/unassociated_phones.php'); ?>">Unassociated Phones</a></li>
                <li><a href="<?php echo project_url('admin/assign_phones.php'); ?>">Assign Phones</a></li>
                <li><a href="<?php echo project_url('admin/create_role.php'); ?>">Create Role</a></li>
                <li><a href="<?php echo project_url('admin/list_roles.php'); ?>">List Roles</a></li>
                <li><a href="<?php echo project_url('admin/assign_roles.php'); ?>">Assign Roles</a></li>
            <?php endif; ?>
            <li><a href="/project/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="/project/login.php">Login</a></li>
            <li><a href="/project/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>
<script src="/project/helpers.js"></script>
