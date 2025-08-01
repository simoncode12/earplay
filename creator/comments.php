<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Logika untuk menghapus komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $comment_to_delete = (int)$_POST['delete_comment_id'];
    
    // Query keamanan: pastikan kreator hanya bisa menghapus komentar di videonya sendiri
    $stmt = $pdo->prepare(
        "DELETE c FROM comments c JOIN videos v ON c.video_id = v.id 
         WHERE c.id = ? AND v.uploader_id = ?"
    );
    $stmt->execute([$comment_to_delete, $creator_id]);
    
    $_SESSION['creator_message'] = "Comment has been deleted.";
    header("Location: comments.php");
    exit();
}

// Ambil semua komentar dari video-video milik kreator yang sedang login
$stmt = $pdo->prepare(
    "SELECT c.id, c.comment_text, c.created_at, u.username as commenter_name, v.title as video_title, v.youtube_id
     FROM comments c
     JOIN users u ON c.user_id = u.id
     JOIN videos v ON c.video_id = v.id
     WHERE v.uploader_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$creator_id]);
$comments = $stmt->fetchAll();

// Menggunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Comments</h1>

<?php
if (isset($_SESSION['creator_message'])) {
    echo '<div class="panel-info success" style="margin-bottom: 24px;">' . htmlspecialchars($_SESSION['creator_message']) . '</div>';
    unset($_SESSION['creator_message']);
}
?>

<div class="creator-section">
    <div class="content comment-list-container">
        <?php if (count($comments) > 0): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item-creator">
                    <div class="comment-author-avatar">
                        <?= strtoupper(substr($comment['commenter_name'], 0, 1)) ?>
                    </div>
                    <div class="comment-details">
                        <p class="comment-meta">
                            <strong><?= htmlspecialchars($comment['commenter_name']) ?></strong> commented on 
                            <a href="/videos/watch.php?v=<?= htmlspecialchars($comment['youtube_id']) ?>" target="_blank">
                                "<?= htmlspecialchars($comment['video_title']) ?>"
                            </a>
                            <span class="comment-date">• <?= date('M d, Y', strtotime($comment['created_at'])) ?></span>
                        </p>
                        <p class="comment-body">
                            <?= nl2br(htmlspecialchars($comment['comment_text'])) ?>
                        </p>
                    </div>
                    <div class="comment-actions">
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');">
                            <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                            <button type="submit" class="btn-icon btn-icon-delete" title="Delete Comment">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-content-message">
                <i class="fas fa-comment-slash"></i>
                <h3>No comments on your videos yet</h3>
                <p>Engage with your audience to start the conversation!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.comment-list-container { padding: 8px; }
.comment-item-creator {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);
}
.comment-item-creator:last-child { border-bottom: none; }
.comment-author-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #ddd;
    color: var(--text-primary);
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    flex-shrink: 0;
}
.comment-details { flex-grow: 1; }
.comment-meta {
    margin: 0 0 8px 0;
    font-size: 0.9em;
    color: var(--text-secondary);
}
.comment-meta a {
    color: var(--text-primary);
    font-weight: 500;
    text-decoration: none;
}
.comment-meta a:hover { text-decoration: underline; }
.comment-body { margin: 0; line-height: 1.6; }
</style>

<?php 
// Menggunakan template footer khusus kreator
include '../includes/templates/footer_creator.php'; 
?>