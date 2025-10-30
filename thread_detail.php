<?php
require_once 'config.php';

$login_flg = isset($_SESSION['member_id']);
$member_id = $login_flg ? $_SESSION['member_id'] : null;

$thread_id = null;
if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $thread_id = (int)$_GET['id'];
} else {
    header('Location: thread_list.php');
    exit;
}

$page = 1;
if (isset($_GET['page']) && ctype_digit((string)$_GET['page']) && (int)$_GET['page'] >= 1) {
    $page = (int)$_GET['page'];
}

$thread = null;
$comments = [];
$error_message = '';
$success_message = '';

$perPage = 5;

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// スレッドと作成者取得
try {
    $stmt = $pdo->prepare("
        SELECT t.id, t.title, t.content, t.member_id, t.created_at,
               m.name_sei, m.name_mei
        FROM threads t
        LEFT JOIN members m ON t.member_id = m.id
        WHERE t.id = ?
        LIMIT 1
    ");
    $stmt->execute([$thread_id]);
    $thread = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$thread) {
        header('Location: thread_list.php');
        exit;
    }

    // コメント総数取得
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt
        FROM comments
        WHERE thread_id = ? AND deleted_at IS NULL
    ");
    $countStmt->execute([$thread_id]);
    $countRow = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalComments = (int)($countRow['cnt'] ?? 0);

    // ページ数
    $totalPages = (int)max(1, ceil($totalComments / $perPage));
    if ($page > $totalPages) $page = $totalPages;

    // コメント一覧取得
    $offset = ($page - 1) * $perPage;
    $cstmt = $pdo->prepare("
        SELECT c.id, c.thread_id, c.member_id, c.comment, c.created_at,
               m.name_sei, m.name_mei
        FROM comments c
        LEFT JOIN members m ON c.member_id = m.id
        WHERE c.thread_id = ? AND c.deleted_at IS NULL
        ORDER BY c.created_at ASC
        LIMIT ? OFFSET ?
    ");
    
    $cstmt->bindValue(1, $thread_id, PDO::PARAM_INT);
    $cstmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $cstmt->bindValue(3, $offset, PDO::PARAM_INT);
    $cstmt->execute();
    $comments = $cstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Thread detail DB error: ' . $e->getMessage());
    $error_message = 'データの取得中にエラーが発生しました。しばらくしてから再試行してください。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>スレッド詳細</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            background: #f5f5f5;
            color: #222;
        }
        header {
            background: #00897B;
            color: #fff;
            padding: 18px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 16px;
        }
        h1 {
            font-size: 20px;
        }
        .header-right {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            background: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-logout {
            background: #ff5252;
        }
        .container {
            max-width: 1100px;
            margin: 32px auto;
            padding: 0 16px;
        }
        .content-card {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }
        .title {
            font-size: 20px;
            margin-bottom: 8px;
        }
        .meta {
            color: #666;
            font-size: 13px;
            margin-bottom: 12px;
        }
        .content {
            white-space: pre-wrap;
            margin-bottom: 18px;
            line-height: 1.6;
        }
        .comments {
            margin-top: 8px;
        }
        .comment {
            border-top: 1px solid #eee;
            padding: 12px 0;
        }
        .comment .meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
        }
        .form-area {
            margin-top: 18px;
        }
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
        }
        .input-row {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 8px;
        }
        .submit {
            background: #00897B;
            color: #fff;
            padding: 10px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }
        .note {
            color: #b71c1c;
            padding: 8px 12px;
            border-radius: 6px;
            background: #fff0f0;
            margin-bottom: 12px;
        }
        .success {
            color: #2e7d32;
            padding: 8px 12px;
            border-radius: 6px;
            background: #f0fff0;
            margin-bottom: 12px;
        }
        .top-link {
            margin-top: 12px;
            display: inline-block;
            color: #777;
            text-decoration: none;
        }
        .pager {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .pager a, .pager span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            color: #444;
            border: 1px solid #ddd;
            background: #fff;
        }
        .pager a:hover {
            background: #f0f0f0;
        }
        .pager .disabled {
            color: #aaa;
            border-color: #eee;
            background: #f9f9f9;
            pointer-events: none;
            cursor: default;
        }
        @media (max-width: 600px) {
            .container {
                margin: 24px 16px;
            }
            .btn {
                padding: 8px 14px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div><h1>⚪︎⚪︎掲示板</h1></div>
            <div class="header-right">
                <a href="top.php" class="btn">トップに戻る</a>
                <?php if ($login_flg): ?>
                    <a href="thread_regist.php?clear=1" class="btn">新規スレッド作成</a>
                    <a href="logout.php" class="btn btn-logout">ログアウト</a>
                <?php else: ?>
                    <a href="login.php" class="btn">ログイン</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="content-card">
            <?php if (!empty($success_message)): ?>
                <div class="success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="note"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="title"><?php echo htmlspecialchars($thread['title'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="meta">
                作成者: <?php echo htmlspecialchars(($thread['name_sei'] ?? '') . ' ' . ($thread['name_mei'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                　|　作成日時: <?php echo htmlspecialchars($thread['created_at'], ENT_QUOTES, 'UTF-8'); ?>
            </div>

            <div class="content"><?php echo htmlspecialchars($thread['content'], ENT_QUOTES, 'UTF-8'); ?></div>

            <hr>

            <h3>コメント一覧（<?php echo $totalComments; ?>件）</h3>
            <div class="comments">
                <?php if (empty($comments)): ?>
                    <div class="empty" style="padding:12px;color:#666">まだコメントはありません。</div>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="comment">
                            <div class="meta">
                                <?php echo htmlspecialchars(($c['name_sei'] ?? '') . ' ' . ($c['name_mei'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                               　|　<?php echo htmlspecialchars($c['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="content"><?php echo htmlspecialchars($c['comment'], ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="pager" aria-label="コメントページネーション">
                <?php if ($page > 1): ?>
                    <a href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page - 1; ?>">前へ</a>
                <?php else: ?>
                    <span class="disabled">前へ</span>
                <?php endif; ?>

                <span>ページ <?php echo $page; ?> / <?php echo $totalPages; ?></span>

                <?php if ($page < $totalPages): ?>
                    <a href="thread_detail.php?id=<?php echo $thread_id; ?>&page=<?php echo $page + 1; ?>">次へ</a>
                <?php else: ?>
                    <span class="disabled">次へ</span>
                <?php endif; ?>
            </div>

            <?php if ($login_flg): ?>
                <div class="form-area">
                    <h4>コメントを投稿する</h4>
                    <form method="post" action="comment_post.php">
                        <input type="hidden" name="thread_id" value="<?php echo (int)$thread_id; ?>">
                        <textarea name="comment" placeholder="コメントを入力してください"></textarea>
                        <div class="input-row">
                            <button type="submit" class="submit">投稿する</button>
                        </div>
                        <div>
                            <a href="thread_list.php" class="top-link">スレッド一覧に戻る</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div style="margin-top:16px;color:#666">
                    コメント投稿はログイン時のみ可能です ▶︎ <a href="login.php">ログイン</a>
                </div>
                <div style="margin-top:12px">
                    <a href="thread_list.php" class="top-link">スレッド一覧に戻る</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>