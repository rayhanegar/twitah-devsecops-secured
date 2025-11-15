<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Twita</title>
  <link rel="stylesheet" href="views/css/style.css">
</head>
<body>
  <header>
    <div class="logo">Twita</div>
    <?php if ($user): ?>
      <div class="user-menu">
        <button id="userButton" class="user-button">
          Halo, <?= htmlspecialchars($user['username']); ?>
        </button>
        <div id="dropdownMenu" class="dropdown-content">
          <a href="index.php?action=logout">Logout</a>
        </div>
      </div>
    <?php else: ?>
        <button onclick="location.href='index.php?action=loginForm'" class="login-btn">Login</button>
      </a>
    <?php endif; ?>
  </header>

  <script>
    // Dropdown muncul saat tombol diklik
    document.addEventListener("DOMContentLoaded", function() {
      const userButton = document.getElementById("userButton");
      const dropdown = document.getElementById("dropdownMenu");

      if (userButton && dropdown) {
        userButton.addEventListener("click", function(e) {
          e.stopPropagation();
          dropdown.classList.toggle("show");
        });

        // Klik di luar area akan menutup dropdown
        document.addEventListener("click", function(e) {
          if (!dropdown.contains(e.target) && !userButton.contains(e.target)) {
            dropdown.classList.remove("show");
          }
        });
      }
    });
  </script>
