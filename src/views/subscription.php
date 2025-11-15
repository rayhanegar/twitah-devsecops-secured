<?php
$currentUser = $_SESSION['user'] ?? null;
$currentRole = $currentUser['role'] ?? 'jelata';
$currentUserId = $currentUser['id'] ?? null;

$csrf = $_SESSION['csrf_token'];
?>

<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>
<link rel="stylesheet" href="views/css/subs.css">

<main>
  <div class="upgrade-container">
    <h2>Upgrade Subscription</h2>
    <?php if ($currentRole === 'ningrat'): ?>
      <h3>Anda sudah diakui sebagai Ningrat</h3>
    <?php else: ?>
      <h3>Anda masih dianggap Jelata</h3>
    <?php endif; ?>
    <p>Pilih paket langganan Anda:</p>
  
    <?php if (isset($_GET['subscribed'])): ?>
      <script>
        alert("Berhasil upgrade ke Ningrat!");
        window.history.replaceState({}, document.title, window.location.pathname);
      </script>
    <?php elseif (isset($_GET['unsubscribed'])): ?>
      <script>
        alert("Berhasil downgrade ke Jelata");
        window.history.replaceState({}, document.title, window.location.pathname);
      </script>
    <?php endif; ?>
  
    <div class="subscription-cards">
      <!-- Card Jelata -->
      <div class="sub-card basic">
        <h3>Jelata (Basic)</h3>
        <p>Fitur:</p>
        <ul>
          <li>Tweet terbatas (maks 200 karakter)</li>
          <li>Tidak dapat mengedit tweet</li>
        </ul>
  
        <div style="margin-top:12px;">
          <?php if ($currentRole === 'jelata'): ?>
          <?php else: ?>
            <form method="POST" action="index.php?action=unsubs" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit" class="btn btn-muted">Downgrade ke Jelata</button>
          </form>

          <?php endif; ?>
        </div>
      </div>
  
      <!-- Card Ningrat -->
      <div class="sub-card premium">
        <h3>Ningrat (Premium)</h3>
        <p>Fitur tambahan:</p>
        <ul>
          <li>Bisa mengedit tweet</li>
          <li>Bisa membuat tweet lebih panjang hingga 500 karakter</li>
          <li>Dapat mengunggah video</li>
        </ul>
  
        <div style="margin-top:12px;">
          <?php if ($currentRole === 'ningrat'): ?>
          <?php else: ?>
            <form method="POST" action="index.php?action=subs" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
              <button type="submit" class="btn btn-primary">Upgrade ke Ningrat</button>
          </form>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>
