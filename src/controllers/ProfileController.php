<?php 
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Tweet.php';

class ProfileController {
    private $userModel;
    private $tweetModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->tweetModel = new Tweet($db);
    }

    public function show() {
        if (!isset($_SESSION['user'])) {
            header('Location: views/auth/login.php');
            exit;
        }

        if (isset($_GET['id'])) {
            $id = $_GET['id'];

            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $profileUser = $result->fetch_assoc();
            $stmt->close();

            if (!$profileUser) {
                echo "<p>User not found.</p>";
                return;
            }

            $stmt = $this->db->prepare("SELECT * FROM tweets WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->bind_param("i", $profileUser['id']);
            $stmt->execute();
            $tweetsResult = $stmt->get_result();
            $tweets = [];
            while ($row = $tweetsResult->fetch_assoc()) {
                $tweets[] = $row;
            }
            $stmt->close();
        } 
        else {
            $profileUser = $_SESSION['user'];
            $tweets = $this->tweetModel->getByUserId($profileUser['id']);
        }

        require __DIR__ . '/../views/profile.php';
    }

    public function updateUsername() {
        if (!isset($_SESSION['user'])) {
            header('Location: views/auth/login.php');
            exit;
        }

        $newUsername = $_POST['username'];
        $userId = (int)$_SESSION['user']['id'];

        // Cek duplikasi username
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $newUsername, $userId);
        $stmt->execute();
        $check = $stmt->get_result();
        if ($check->num_rows > 0) {
            $stmt->close();
            echo "<script>alert('Username sudah digunakan!'); window.location='index.php?action=profile';</script>";
            exit;
        }
        $stmt->close();

        // Update username dengan prepared statement
        $stmt = $this->db->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $userId);
        $stmt->execute();
        $stmt->close();

        // Update session
        $_SESSION['user']['username'] = $newUsername;

        header('Location: index.php?action=profile&profile_updated=1');
        exit;
    }

}

?>
