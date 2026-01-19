<?php
require_once __DIR__ . '/../models/user_model.php';

final class AdminController {

    public static function updateNgoStatus(): void {
        session_start();

        $me = $_SESSION["user"] ?? null;
        if (!$me || ($me["role"] ?? "") !== "admin") {
            header("Location: login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = (int)($_POST["id"] ?? 0);
            $status = $_POST["status"] ?? "pending";

            if ($id > 0 && in_array($status, ["approved", "rejected", "pending"], true)) {
                UserModel::setStatus($id, $status);
            }
        }

        header("Location: admin");
        exit;
    }
}
