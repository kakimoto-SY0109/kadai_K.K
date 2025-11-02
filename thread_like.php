<?php
require_once 'config.php';

// 未ログインの場合は会員登録フォームへ遷移
if (!isset($_SESSION['member_id'])) {
    header('Location: member_regist.php');
    exit;
}

$member_id = $_SESSION['member_id'];
$comment_id = null;
$thread_id = null;

if (isset($_POST['comment_id']) && ctype_digit((string)$_POST['comment_id'])) {
    $comment_id = (int)$_POST['comment_id'];
}
if (isset($_POST['thread_id']) && ctype_digit((string)$_POST['thread_id'])) {
    $thread_id = (int)$_POST['thread_id'];
}

$page = 1;
if (isset($_POST['page']) && ctype_digit((string)$_POST['page']) && (int)$_POST['page'] >= 1) {
    $page = (int)$_POST['page'];
}

if (!$comment_id || !$thread_id) {
    header('Location: thread_list.php');
    exit;
}

try {
    // いいね済みか確認
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE member_id = ? AND comment_id = ?");
    $stmt->execute([$member_id, $comment_id]);
    $like = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($like) {
        // いいね解除
        $del = $pdo->prepare("DELETE FROM likes WHERE member_id = ? AND comment_id = ?");
        $del->execute([$member_id, $comment_id]);
    } else {
        // いいね追加
        $ins = $pdo->prepare("INSERT INTO likes (member_id, comment_id) VALUES (?, ?)");
        $ins->execute([$member_id, $comment_id]);
    }
} catch (PDOException $e) {
    error_log('Like DB error: ' . $e->getMessage());
}

header("Location: thread_detail.php?id={$thread_id}&page={$page}");
exit;