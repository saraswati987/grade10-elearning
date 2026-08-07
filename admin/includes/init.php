<?php
// admin/includes/init.php - Boot + gate for the admin module. Emits no output,
// so pages can still redirect after handling a POST.
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../config/app.php';

require_admin();

$adminName = $_SESSION['admin_name'] ?? 'Administrator';
