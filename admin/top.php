<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面トップ</title>
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
            background-color: #9E9E9E;
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
            background-color: #757575;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #616161;
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
        .admin-icon {
            font-size: 48px;
            margin-bottom: 20px;
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
                <h1>⚪︎⚪︎掲示板 管理画面</h1>
                <div class="welcome-message">ようこそ <?php echo htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8'); ?> 様</div>
            </div>
            <div class="header-right">
                <a href="logout.php" class="btn btn-logout">ログアウト</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <div class="admin-icon">🔐</div>
            <h2>管理画面トップ</h2>
            <p>
                管理者としてログイン中です。
            </p>
        </div>
    </div>
    
</body>
</html>