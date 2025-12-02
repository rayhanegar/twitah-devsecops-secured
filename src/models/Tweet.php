<?php
class Tweet {
    private $conn;
    private $table = "tweets";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllTweets() {
        $sql = "SELECT t.id, t.content, t.image_url, t.created_at, u.username
                FROM tweets t
                JOIN users u ON t.user_id = u.id
                ORDER BY t.created_at DESC";

        return $this->conn->query($sql); // aman karena tidak pakai user input
    }

    public function addTweet($user_id, $content, $image_url = null) {
        $sql = "INSERT INTO {$this->table} (user_id, content, image_url)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        // bind_param: i = integer, s = string
        $stmt->bind_param("iss", $user_id, $content, $image_url);

        return $stmt->execute();
    }

    public function searchTweets($keyword) {
        $sql = "SELECT t.id, t.content, t.image_url, t.created_at, u.username
                FROM tweets t
                JOIN users u ON t.user_id = u.id
                WHERE t.content LIKE ? OR u.username LIKE ?
                ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        $like = "%{$keyword}%";

        $stmt->bind_param("ss", $like, $like);

        $stmt->execute();
        return $stmt->get_result();
    }

    // public function searchTweets($keyword) {
    //     // VULNERABLE: raw query concatenation (SQL Injection possible)
    //     $sql = "SELECT tweets.id, tweets.content, tweets.image_url, tweets.created_at, users.username FROM tweets JOIN users ON tweets.user_id = users.id WHERE tweets.content LIKE '%$keyword%' OR users.username LIKE '%$keyword%' ORDER BY tweets.created_at DESC";
    //     error_log("Executed Search Tweets SQL: $sql");
    //     return $this->conn->query($sql);
    // }

    public function getTweetById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateTweet($id, $content, $image_url) {

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ningrat') {
        http_response_code(403);
        echo "<h3>403 Forbidden — Anda tidak punya akses.</h3>";
        exit;
        }

        $sql = "UPDATE {$this->table}
                SET content = ?, image_url = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("ssi", $content, $image_url, $id);

        return $stmt->execute();
    }

    public function getByUserId($user_id) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ?
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $user_id);

        $stmt->execute();
        $res = $stmt->get_result();

        if ($res) {
            return $res->fetch_all(MYSQLI_ASSOC);
        }

        return [];
    }
}
?>
