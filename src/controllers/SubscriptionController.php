<?php
class SubscriptionController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function validateCsrf($token) {
        return !empty($token) 
            && !empty($_SESSION['csrf_token']) 
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function subscribe() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=subscription');
            exit;
        }

        // CSRF check
        if (!$this->validateCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Invalid CSRF token";
            exit;
        }

        if (!isset($_SESSION['user']['id'])) {
            header('Location: index.php?action=loginForm');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->db->prepare("UPDATE users SET role = 'ningrat' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $_SESSION['user']['role'] = 'ningrat';
            header('Location: index.php?action=subscription&subscribed=1');
            exit;
        }

        header('Location: index.php?action=subscription&error=1');
        exit;
    }

    public function unsubscribe() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=subscription');
            exit;
        }

        // CSRF check
        if (!$this->validateCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(400);
            echo "Invalid CSRF token";
            exit;
        }

        if (!isset($_SESSION['user']['id'])) {
            header('Location: index.php?action=loginForm');
            exit;
        }

        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->db->prepare("UPDATE users SET role = 'jelata' WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $_SESSION['user']['role'] = 'jelata';
            header('Location: index.php?action=subscription&unsubscribed=1');
            exit;
        }

        header('Location: index.php?action=subscription&error=1');
        exit;
    }
}
?>
