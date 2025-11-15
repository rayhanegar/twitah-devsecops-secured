<?php
require_once __DIR__ . '/../models/Tweet.php';

class TweetController {
    private $model;
    private $db;
    private $uploadDir;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Tweet($db);
        $this->uploadDir = __DIR__ . '/../uploads/';
    }

    public function index() {
        $query = $_GET['q'] ?? '';

        if ($query !== '') {
            $tweets = $this->model->searchTweets($query);
        } else {
            $tweets = $this->model->getAllTweets();
        }

        include __DIR__ . '/../views/home.php';
    }

    public function showAdd() {
        if(!isset($_SESSION['user'])) {
            header("Location: views/auth/login.php");
            exit;
        }
        include __DIR__ . '/../views/add.php';
    }

    public function showEdit() {
        if(!isset($_SESSION['user'])) {
            header("Location: views/auth/login.php");
            exit;
        }

        $id = $_GET['id'] ?? null;

        $tweet = $this->model->getTweetById($id);
        if (!$tweet) {
            echo "Gagal mengambil tweet.";
            return;
        }

        include __DIR__ . '/../views/edit.php';
    }

    public function store() {
        $user_id = $_SESSION['user']['id'] ?? 1;
        $content = $_POST['content'] ?? '';

        if ($content !== strip_tags($content)) {
            $error = "Terdapat Karakter Spesial yang tidak diperbolehkan!";
            include __DIR__ . '/../views/add.php';
            return;
        }

        $image_url = null;

        // FIX upload
        if (!empty($_FILES['image']['name'])) {

            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "File tidak valid. Hanya JPG, PNG, GIF yang diperbolehkan.";
                include __DIR__ . '/../views/add.php';
                return;
            }
            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = "File terlalu besar. Maksimal 2MB.";
                include __DIR__ . '/../views/add.php';
                return;
            }

            $check = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($check, $_FILES['image']['tmp_name']);
            finfo_close($check);

            if (!str_starts_with($mime, "image/")) {
                exit("Hanya boleh upload gambar!");
            }

            $newName = uniqid('img_', true).".".$ext;
            $dest = $this->uploadDir.$newName;
            move_uploaded_file($_FILES['image']['tmp_name'], $dest);
            $image_url = 'uploads/'.$newName;
        }

        $ok = $this->model->addTweet($user_id, $content, $image_url);

        if ($ok) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menyimpan tweet.";
            include __DIR__ . '/../views/add.php';
        }
    }

    public function updateTweet() {
        $id = $_POST['id'] ?? null;
        $content = $_POST['content'] ?? null;
        $oldImageUrl = $_POST['image_url'] ?? null;

        if ($content !== strip_tags($content)) {
            $error = "Terdapat Karakter Spesial yang tidak diperbolehkan!";
            include __DIR__ . '/../views/edit.php';
            return;
        }

        $tweet = $this->model->getTweetById($id);
        if (!$tweet) {
            $error = "Tweet tidak ditemukan.";
            include __DIR__ . '/../views/edit.php';
            return;
        }

        $image_url = $oldImageUrl;

        // Jika ada upload baru
        if (!empty($_FILES['image']['name'])) {

            $allowed = ['jpg','jpeg','png','gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "File tidak valid. Hanya JPG, PNG, GIF.";
                include __DIR__ . '/../views/edit.php';
                return;
            }

            if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = "File terlalu besar. Maksimal 2MB.";
                include __DIR__ . '/../views/edit.php';
                return;
            }

            $check = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($check, $_FILES['image']['tmp_name']);
            finfo_close($check);

            if (!str_starts_with($mime, "image/")) {
                $error = "File bukan gambar!";
                include __DIR__ . '/../views/edit.php';
                return;
            }

            if (!empty($tweet['image_url'])) {
                $oldPath = __DIR__ . '/../' . $tweet['image_url'];
                if (file_exists($oldPath)) unlink($oldPath);
            }

            $newName = uniqid('img_', true) . "." . $ext;
            $dest = $this->uploadDir . $newName;

            move_uploaded_file($_FILES['image']['tmp_name'], $dest);

            $image_url = 'uploads/' . $newName;
        }

        $res = $this->model->updateTweet($id, $content, $image_url);

        if ($res) {
            $_SESSION['flash'] = 'Edit tweet berhasil.';
            header("Location: index.php?action=profile");
        } else {
            $error = "Gagal memperbarui tweet.";
            include __DIR__ . '/../views/edit.php>';
        }
    }

    public function deleteTweet() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;

            $tweet = $this->model->getTweetById($id);

            if ($tweet['user_id'] !== $_SESSION['user']['id']) {
                http_response_code(403);
                exit("403 Forbidden");
            }
            if ($tweet && !empty($tweet['image_url'])) {
                $filePath = __DIR__ . '/../' . $tweet['image_url'];

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $stmt = $this->db->prepare("DELETE FROM tweets WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            $username = $_SESSION['user']['username'] ?? null;

            if ($username) {
                header("Location: index.php?action=profile&username={$username}&deleted=1");
            } else {
                header("Location: index.php?action=profile&deleted=1");
            }
            exit;
        }
    }

}
?>
