<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $model;
    private $conn;

    public function __construct($db) {
        $this->model = new User($db);
        $this->conn = $db;
    }

    public function showLogin() {
        include __DIR__ . '/../views/auth/login.php';
    }

    public function showRegister() {
        include __DIR__ . '/../views/auth/register.php';
    }

    // ===============================================
    // PROSES LOGIN + BRUTE FORCE VALIDATION
    // ===============================================
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $result = $this->model->login($email, $password);

            // Jika account terkunci
            if (is_array($result) && isset($result['locked'])) {
                $error = "Akun Anda terkunci sampai: " . $result['until'];
                include __DIR__ . '/../views/auth/login.php';
                return;
            }

            if ($result) {
                // simpan user ke session (AMAN: password sudah hashed)
                unset($result['password']); // jangan simpan hash ke session

                session_regenerate_id(true);
                $_SESSION['user'] = $result;

                header("Location: index.php");
                exit;
            }

            $error = "Email atau password salah!";
            include __DIR__ . '/../views/auth/login.php';
        }

        // VULNERABLE
        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //     $email = $_POST['email'] ?? '';
        //     $password = $_POST['password'] ?? '';

        //     $user = $this->model->login($email, $password);
        //     if ($user) {
        //         // simpan seluruh row user ke session (termasuk password) — intentionally vulnerable
        //         $_SESSION['user'] = $user;
        //         header("Location: /index.php");
        //         exit;
        //     } else {
        //         $error = "Email atau password salah!";
        //         include __DIR__ . '/../views/auth/login.php';
        //     }
        // }
    }

    // ===============================================
    // PROSES REGISTER (AMAN)
    // ===============================================
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->model->register($username, $email, $password)) {
                header("Location: index.php?action=loginForm");
                exit;
            } else {
                $error = "Gagal mendaftar!";
                include __DIR__ . '/../views/auth/register.php';
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit;
    }
}
?>
