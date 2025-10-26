<?php
require_once 'config.php';

$login_flg = isset($_SESSION['member_id']);

$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$keyword = '';
if (isset($_GET['keyword'])) {
    $keyword = trim($_GET['keyword']);
}

try {
    $stmt = $pdo->prepare("
        SELECT id, title, created_at
        FROM threads
        WHERE ? = '' OR BINARY title LIKE ? OR BINARY content LIKE ?
        ORDER BY created_at DESC
    ");
    
    $like_keyword = '%' . $keyword . '%';
    $stmt->execute([$keyword, $like_keyword, $like_keyword]);
    
    $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Thread list error: ' . $e->getMessage());
    $threads = [];
    $error_message = 'スレッドの取得に失敗しました。しばらくしてから再試行してください。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スレッド一覧</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            background: #f5f5f5;
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
        .content {
            background: #fff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
            position: relative;
        }
        .search-form {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .search-form input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 10px 16px;
            border: none;
            background: #00897B;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th,td {
            padding: 12px 8px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #fafafa;
            color: #333;
            font-weight: 600;
        }
        .empty {
            padding: 20px;
            color: #666;
        }
        .top-link {
            margin-top: 16px;
            display: inline-block;
            color: #777;
            text-decoration: none;
        }
        .page-message {
            display: block;
            width: 100%;
            max-width: 720px;
            margin: 0 auto 16px;
            border-radius: 10px;
            text-align: center;
            padding: 12px 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
            font-size: 15px;
            line-height: 1.4;
            overflow: hidden;
            opacity: 1;
            max-height: 200px;
            transition: opacity 0.6s ease, max-height 0.6s ease, margin-bottom 0.6s ease, padding 0.6s ease;
        }
        .page-message.hidden {
            opacity: 0;
            max-height: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            pointer-events: none;
        }
        .success-message {
            background-color: #dff0d8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .error-message {
            background-color: #f8d7da;
            color: #b71c1c;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 600px) {
            .search-form {
                flex-direction: column;
            }
            .search-form button {
                width: 100%;
            }
            .page-message {
                max-width: 90%;
                font-size: 14px;
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
        <div class="content">
            <h2>スレッド一覧</h2>

            <form class="search-form" method="get" action="thread_list.php">
                <input type="text" name="keyword" placeholder="スレッドタイトル・コメントを検索" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit">検索</button>
            </form>

            <?php if (!empty($success_message) || !empty($error_message)): ?>
                <?php if (!empty($success_message)): ?>
                    <div class="page-message success-message" id="pageMessage"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php else: ?>
                    <div class="page-message error-message" id="pageMessage"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($threads)): ?>
                <div class="empty">該当するスレッドはありません。</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width:80px;">スレッドID</th>
                            <th>スレッドタイトル</th>
                            <th style="width:180px;">登録日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($threads as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($t['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($t['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($t['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="top.php" class="top-link">トップに戻る</a>
        </div>
    </div>

    <script>
        (function() {
            var msg = document.getElementById('pageMessage');
            if (!msg) return;

            // 表示継続時間（setTimeoutはミリ秒で指定する）
            var displayDuration = 10000;

            // 指定時間後非表示
            setTimeout(function() {
                msg.classList.add('hidden');
                setTimeout(function() {
                    if (msg && msg.parentNode) {
                        msg.parentNode.removeChild(msg);
                    }
                // フェード開始後、完了まで0.7秒
                }, 700);
            }, displayDuration);

            // ユーザーがスレッド作成成功メッセージをクリックしたら閉じる
            msg.addEventListener('click', function() {
                msg.classList.add('hidden');
                setTimeout(function() {
                    if (msg && msg.parentNode) {
                        msg.parentNode.removeChild(msg);
                    }
                }, 700);
            });
        })();
    </script>
</body>
</html>