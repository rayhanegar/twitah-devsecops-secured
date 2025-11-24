<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<main>
  <?php $csrf = $_SESSION['csrf_token']; ?>

  <h2>Profile</h2>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <script>
      alert("Tweet updated successfully!");
      window.history.replaceState({}, document.title, window.location.pathname);
    </script>
  <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <script>
      alert("Tweet deleted successfully!");
      window.history.replaceState({}, document.title, window.location.pathname);
    </script>
  <?php elseif (isset($_GET['profile_updated']) && $_GET['profile_updated'] == 1): ?>
    <script>
      alert("Profile updated successfully!");
      window.history.replaceState({}, document.title, window.location.pathname);
    </script>

    <?php
    echo $_SESSION['flash'] ?? 'flash kosong jir';
    elseif (!empty($_SESSION['flash'])):
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo "<script>alert(" . json_encode($msg) . ");</script>";

    ?>
  <?php endif; ?>

  <?php
  // Apakah profil milik user yang sedang login?
  $isOwnProfile = isset($_SESSION['user']) && $_SESSION['user']['username'] === $profileUser['username'];
  ?>

  <!-- Informasi User -->
  <div class="profile-card" style="border:1px solid #ccc; border-radius:10px; padding:15px; margin-bottom:20px;">
    <strong>@<?= htmlspecialchars($profileUser['username']); ?></strong><br>
    <?php if($isOwnProfile): ?>
      <p><b>Email:</b> <?= htmlspecialchars($profileUser['email']); ?></p>
    <?php endif; ?>
    <p><b>Joined:</b> <?= date('Y-m-d', strtotime($profileUser['created_at'])); ?></p>

    <!-- Tombol Edit Username hanya untuk profil sendiri -->
    <?php if ($isOwnProfile): ?>
      <button onclick="openUsernameDialog('<?= htmlspecialchars(addslashes($profileUser['username'])); ?>')" 
              style="margin-top:10px;">Edit Profile</button>
    <?php endif; ?>
  </div>

  <h3><?= $isOwnProfile ? 'My Tweets' : $profileUser['username'] . "'s Tweets" ?></h3>

  <?php if (!empty($tweets)): ?>
    <?php foreach ($tweets as $tweet): ?>
      <div class="tweet" style="border:1px solid #ccc; border-radius:10px; padding:15px; margin-bottom:20px; background:#fff;">
        
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <strong>@<?= htmlspecialchars($profileUser['username']); ?></strong>

          <!-- Tombol Edit & Delete hanya muncul jika profil sendiri -->
          <?php if ($isOwnProfile): ?>
            <div style="display:flex; gap:10px; align-items:center;">
              
              <!-- Tombol Edit -->
              <?php if($profileUser['role'] === 'ningrat'): ?>
                <a href="#" 
                  title="Edit Tweet" 
                  style="color:#ffc107; text-decoration:none; font-size:18px;"
                  onclick="window.location.href='index.php?action=showEdit&id=<?= $tweet['id']; ?>'">
                  <i class="fas fa-edit"></i>
                </a>
              <?php endif; ?>

              <!-- Tombol Delete -->
              <form method="POST" action="index.php?action=deleteTweet" style="display:inline;" 
                    onsubmit="return confirm('Yakin ingin menghapus tweet ini?');">
                <input type="hidden" name="id" value="<?= $tweet['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" title="Hapus Tweet"
                        style="background:none; border:none; color:#dc3545; font-size:18px; cursor:pointer;">
                  <i class="fas fa-trash"></i>
                </button>
              </form>

            </div>
          <?php endif; ?>
        </div>

        <p style="margin-top:10px;"><?= htmlspecialchars($tweet['content']); ?></p>

        <?php if (!empty($tweet['image_url'])): ?>
          <img src="/<?= htmlspecialchars($tweet['image_url']); ?>" alt="tweet image"
               style="max-width:100%; border-radius:8px; margin-top:10px;">
        <?php endif; ?>

        <small style="color:#777;"><?= date('Y-m-d', strtotime($tweet['created_at'])); ?></small>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No tweets yet.</p>
  <?php endif; ?>
</main>

<!-- Dialog Edit Tweet -->
<!-- <div id="editDialog" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
     background:#fff; border:1px solid #ccc; border-radius:8px; padding:20px; z-index:1000; width:400px;">
  <h3>Edit Tweet</h3>
  <form id="editForm" method="POST" action="index.php?action=updateTweet">
    <input type="hidden" name="id" id="editTweetId">
    <textarea name="content" id="editTweetContent" rows="3" style="width:100%;"></textarea>
    <div style="text-align:right; margin-top:10px;">
      <button type="button" onclick="closeEditDialog()" style="margin-right:10px;">Cancel</button>
      <button type="submit">Save</button>
    </div>
  </form>
</div> -->

<!-- Dialog Edit Profile -->
<div id="usernameDialog" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
     background:#fff; border:1px solid #ccc; border-radius:8px; padding:20px; z-index:1000; width:400px;">
  <h3>Edit Profile</h3>
  <form method="POST" action="index.php?action=updateUsername">
    <label for="newUsername">New Username:</label>
    <input type="text" name="username" id="newUsername" style="width:100%; margin-top:5px;" required>
    <div style="text-align:right; margin-top:10px;">
      <button type="button" onclick="closeUsernameDialog()" style="margin-right:10px;">Cancel</button>
      <button type="submit">Save</button>
    </div>
  </form>
</div>

<!-- Overlay -->
<div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
     background:rgba(0,0,0,0.5); z-index:999;" onclick="closeAllDialogs()"></div>

<script>
  // function openEditDialog(id, content) {
  //   document.getElementById('editTweetId').value = id;
  //   document.getElementById('editTweetContent').value = content;
  //   document.getElementById('editDialog').style.display = 'block';
  //   document.getElementById('overlay').style.display = 'block';
  // }

  // function closeEditDialog() {
  //   document.getElementById('editDialog').style.display = 'none';
  //   document.getElementById('overlay').style.display = 'none';
  // }

  function openUsernameDialog(currentUsername) {
    document.getElementById('newUsername').value = currentUsername;
    document.getElementById('usernameDialog').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
  }

  function closeUsernameDialog() {
    document.getElementById('usernameDialog').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
  }

  function closeAllDialogs() {
    // closeEditDialog();
    closeUsernameDialog();
  }
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
