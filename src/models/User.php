<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ===============================
    // REGISTER — HASHED PASSWORD
    // ===============================
    public function register($username, $email, $password) {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // failed_attempts, last_attempt, locked_until
        $sql = "INSERT INTO {$this->table} 
                (username, email, password, role, failed_attempts, last_attempt, locked_until)
                VALUES (?, ?, ?, 'jelata', 0, NULL, NULL)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed);

        return $stmt->execute();
    }
    
    // Login Vulnerable
    // public function login($email, $password) {
    //     $sql = "SELECT * FROM {$this->table} WHERE email = '$email' AND password = '$password' LIMIT 1";
    //     error_log("SQL Query: " . $sql);
    //     $res = $this->conn->query($sql);
    //     error_log("Rows returned: " . ($res ? $res->num_rows : '0'));
    //     if ($res && $res->num_rows === 1) {
    //         return $res->fetch_assoc();
    //     }
    //     return false;
    // }


    // ===============================
    // LOGIN + BRUTE FORCE PROTECTION
    // ===============================
    public function login($email, $password) {

        // 1. Ambil user berdasarkan email
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $res = $stmt->get_result();
        if ($res->num_rows !== 1) return false;

        $user = $res->fetch_assoc();
        $userId = $user['id'];

        // 2. Jika user sedang di-lock
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            return [
                "locked" => true,
                "until" => $user['locked_until']
            ];
        }

        // 3. Verifikasi password
        if (password_verify($password, $user['password'])) {

            // Reset percobaan gagal
            $reset = "UPDATE {$this->table}
                      SET failed_attempts = 0, last_attempt = NULL, locked_until = NULL
                      WHERE id = ?";
            $s = $this->conn->prepare($reset);
            $s->bind_param("i", $userId);
            $s->execute();

            return $user;
        }

        // 4. Password salah → tambahkan failed_attempts
        $failed = $user['failed_attempts'] + 1;
        $now = date("Y-m-d H:i:s");

        // Batas percobaan gagal
        $MAX_ATTEMPTS = 3;

        if ($failed >= $MAX_ATTEMPTS) {
            $lockTime = date("Y-m-d H:i:s", time() + 180); // lock 1 menit
            $update = "UPDATE {$this->table}
                       SET failed_attempts = ?, last_attempt = ?, locked_until = ?
                       WHERE id = ?";
            $s = $this->conn->prepare($update);
            $s->bind_param("issi", $failed, $now, $lockTime, $userId);
            $s->execute();

        } else {
            $update = "UPDATE {$this->table}
                       SET failed_attempts = ?, last_attempt = ?
                       WHERE id = ?";
            $s = $this->conn->prepare($update);
            $s->bind_param("isi", $failed, $now, $userId);
            $s->execute();
        }

        return false;
    }

    // ===============================
    // GET USER BY ID
    // ===============================
    public function getById($id) {

        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            return $res->fetch_assoc();
        }

        return null;
    }
}
?>
