<?php
session_start();
$_SESSION["count"] = ($_SESSION["count"] ?? 0) + 1;

echo "Session ID: " . session_id() . "<br>";
echo "Count: " . $_SESSION["count"];
