<?php include __DIR__ . '/layout/header.php'; ?>
<?php include __DIR__ . '/layout/sidebar.php'; ?>

<main>
    <h2>Tweets</h2>

    <!-- 🔍 Search Form (Aman dari Reflected XSS) -->
    <form method="GET" action="index.php" class="search-form">
        <input 
            type="text" 
            name="q" 
            placeholder="Search tweets..."
            value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8') : '' ?>"
        >
        <button type="submit">Search</button>
    </form>

    <?php if ($tweets && $tweets->num_rows > 0): ?>
        <?php while ($row = $tweets->fetch_assoc()): ?>

            <?php
                // sanitize ALL output
                $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
                $content  = nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8'));
                $created  = htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8');
                $imageUrl = !empty($row['image_url']) 
                            ? htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8') 
                            : null;
            ?>

            <div class="tweet-content">
                <strong>@<?= $username ?></strong><br>

                <!-- SAFE: Content sudah escaped -->
                <p><?= $content ?></p>

                <!-- SAFE: Image URL tidak bisa disisipi JS -->
                <?php if ($imageUrl): ?>
                    <img src="/<?= $imageUrl ?>" alt="tweet image" style="max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px;">
                <?php endif; ?>

                <small><?= $created ?></small>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <p>No tweets yet.</p>
    <?php endif; ?>

</main>
