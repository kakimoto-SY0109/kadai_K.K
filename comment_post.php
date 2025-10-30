<?php
require_once 'config.php';

if (!isset($_SESSION['member_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: thread_list.php');
    exit;
}

$thread_id = !empty($_POST['thread_id']) && ctype_digit($_POST['thread_id']) ? (int)$_POST['thread_id'] : 0;
$comment   = isset($_POST['comment']) ? trim($_POST['comment']) : '';


if ($comment === '') {
    $_SESSION['error_message'] = '※コメントを入力してください';
    header('Location: thread_detail.php?id=' . $thread_id);
    exit;
}

if (mb_strlen($comment) > 500) {
    $_SESSION['error_message'] = 'コメントは500文字以内で入力してください';
    header('Location: thread_detail.php?id=' . $thread_id);
    exit;
}

try {
    $check = $pdo->prepare("SELECT id FROM threads WHERE id = ? LIMIT 1");
    $check->execute([$thread_id]);
    if (!$check->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['error_message'] = '指定されたスレッドが存在しません';
        header('Location: thread_list.php');
        exit;
    }

    // コメント挿入
    $stmt = $pdo->prepare("
        INSERT INTO comments (thread_id, member_id, comment, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([$thread_id, $_SESSION['member_id'], $comment]);

    $_SESSION['success_message'] = 'コメントを投稿しました';
} catch (PDOException $e) {
    error_log('Comment insert error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'コメントの投稿に失敗しました。しばらくしてから再試行してください。';
}

// 投稿後に該当スレッドへ戻す
header('Location: thread_detail.php?id=' . $thread_id);
exit;
