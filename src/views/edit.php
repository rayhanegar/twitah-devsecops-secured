<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ningrat') {
    echo "<script>
            alert('Akses ditolak! Hanya pengguna dengan role NINGRAT yang dapat mengakses halaman ini.');
            window.location.href = 'index.php';
          </script>";
    exit;
}
?>


<main>
  <?php $currentRole = $_SESSION['user']['role'] ?? 'jelata'; ?>
  <h2>Edit Tweet</h2>

  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <div class="tweet-edit">
    <form method="POST" action="index.php?action=updateTweet" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $tweet['id']; ?>">
      <textarea name="content" rows="4" style="width:100%;" 
        maxlength="<?= ($currentRole === 'ningrat') ? 1000 : 200; ?>" required><?= $tweet['content']; ?></textarea>
      <br><br>

      <label>Current Image:</label>
      <?php if (!empty($tweet['image_url'])): ?>
        <p><?= basename($tweet['image_url']); ?></p>
        <input type="hidden" name="image_url" value="<?= $tweet['image_url']; ?>">
      <?php else: ?>
        <p><em>No image attached</em></p>
        <input type="hidden" name="image_url" value="">
      <?php endif; ?>

      <label>Upload New Image (optional):</label>
      <input type="file" name="image" accept="image/*">
      <br><br>
      <button type="submit">Update</button>
    </form>
  </div>
</main>
