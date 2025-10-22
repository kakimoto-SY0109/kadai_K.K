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
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }
        .content {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .content p {
            color: #666;
            line-height: 1.8;
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
                <?php if ($login_flg): ?>
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
                    ログイン中です。<br>
                    会員専用のサービスをご利用いただけます。
                <?php else: ?>
                    ⚪︎⚪︎掲示板へようこそ。<br>
                    ログインまたは新規会員登録を行ってください。
                <?php endif; ?>
            </p>
        </div>
    </div>
</body>
</html>