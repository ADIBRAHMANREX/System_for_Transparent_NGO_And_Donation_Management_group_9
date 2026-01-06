<?php
declare(strict_types=1);
session_start();

$timeout = 900;

if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
    session_unset();
    session_destroy();
    header("Location: index.html");
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();