<?php
require_once 'config.php';

// ログイン状態
$login_flg = isset($_SESSION['member_id']);
$member_name = '';

if ($login_flg) {
    $sql = $pdo->prepare("SELECT name_sei, name_mei FROM members WHERE id = ?");
    $sql->execute([$_SESSION['member_id']]);
    $member = $sql->fetch();

    if ($member) {
        $member_name = $member['name_sei'] . ' ' . $member['name_mei'];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トップページ</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: #00897B;
            color: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left h1 {
            font-size: 24px;
        }
        .welcome-message {
            font-size: 14px;
            margin-top: 5px;
        }
        .header-right {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .btn-logout {
            background-color: #ff5252;
            color: white;
        }
        .btn-logout:hover {
            background-color: #ff1744;
        }

        .btn-delete {
            background-color: #9E9E9E;
            color: white;
        }

        .btn-delete:hover {
            background-color: #757575;
        }
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 50px auto auto auto;

            padding: 20px;
            box-sizing: border-box;
            flex: 1;
        }
        .content {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .content p {
            color: #666;
            line-height: 1.8;
        }

        .delete-section {
            background-color: #00897B;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: auto;
            text-align: right;
            width: 100%;
            box-sizing: border-box;
            min-height: 72px;
        }
        .delete-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        @media (max-width: 480px) {
            .container { 
                margin: 30px 16px auto 16px;
                width: calc(100% - 32px);
            }
            .btn {
                padding: 10px 18px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <h1>⚪︎⚪︎掲示板</h1>
                <?php if ($login_flg): ?>
                    <div class="welcome-message">ようこそ <?php echo htmlspecialchars($member_name, ENT_QUOTES, 'UTF-8'); ?> 様</div>
                <?php endif; ?>
            </div>
            <div class="header-right">
                <a href="thread_list.php" class="btn">スレッド一覧</a>
                <?php if ($login_flg): ?>
                    <a href="thread_regist.php?clear=1" class="btn">新規スレッド作成</a>
                    <a href="logout.php" class="btn btn-logout">ログアウト</a>
                <?php else: ?>
                    <a href="login.php" class="btn">ログイン</a>
                    <a href="member_regist.php" class="btn">新規会員登録</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <h2>トップページ</h2>
            <p>
                <?php if ($login_flg): ?>
                    ログイン中です。
                <?php else: ?>
                    ⚪︎⚪︎掲示板へようこそ。<br>
                    ログインまたは新規会員登録を行ってください。
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="delete-section">
            <div class="delete-container">
                <?php if ($login_flg): ?>
                    <a href="member_leave.php" class="btn btn-delete">退会</a>
                <?php endif; ?>
            </div>
    </div>
    
</body>
</html>