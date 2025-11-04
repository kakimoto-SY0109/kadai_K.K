<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録完了</title>
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
        .admin-icon {
            font-size: 48px;
            margin-bottom: 10px;
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
        .btn-member {
            background-color: #616161;
        }
        .btn-member:hover {
            background-color: #424242;
        }
        
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 50px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .container h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .error-message {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .security-notice {
            color: #999;
            font-style: italic;
        }
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        @media (max-width: 480px) {
            .header-container {
                flex-direction: column;
                gap: 15px;
            }
            .header-right {
                flex-direction: column;
                width: 100%;
            }
            .btn {
                padding: 10px 18px;
                font-size: 14px;
                text-align: center;
            }
            .container {
                margin: 30px 16px;
                padding: 20px 16px;
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
                <a href="member.php" class="btn btn-member">会員一覧に戻る</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="success-icon">✓</div>
        <h1>会員登録が完了しました</h1>
    </div>
</body>
</html>