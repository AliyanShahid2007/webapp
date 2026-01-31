<?php
require_once __DIR__ . '/includes/functions.php';
initSession();
session_destroy();
header('Location: login.php');
exit;
