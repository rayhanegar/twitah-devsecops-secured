<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Twita</title>
    <link rel="stylesheet" href="/views/css/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST" action="../../index.php?action=login">
        <input type="text" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Belum punya akun? <a href="index.php?action=registerForm">Register</a></p>
</div>
</body>
</html>
