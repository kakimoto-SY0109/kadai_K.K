<?php
require_once 'config.php';

// 未ログインの場合はトップページへ遷移
if (!isset($_SESSION['member_id'])) {
    header('Location: top.php');
    exit;
}

$sql = $pdo->prepare("SELECT name_sei, name_mei FROM members WHERE id = ? AND deleted_at IS NULL");
$sql->execute([$_SESSION['member_id']]);
$member = $sql->fetch();

if (!$member) {
    // 会員が見つからない場合
    session_destroy();
    header('Location: top.php');
    exit;
}

$member_name = $member['name_sei'] . ' ' . $member['name_mei'];

// 退会処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdrawal'])) {
    try {
        // ソフトデリート
        $sql = $pdo->prepare("UPDATE members SET deleted_at = NOW() WHERE id = ?");
        $sql->execute([$_SESSION['member_id']]);
        
        session_destroy();
        
        header('Location: top.php');
        exit;
    } catch (PDOException $e) {
        $error_message = '退会処理中にエラーが発生しました。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員退会</title>
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
        .btn-back {
            background-color: #9E9E9E;
            color: white;
        }
        .btn-back:hover {
            background-color: #757575;
        }
        .container {
            max-width: 600px;
            width: 100%;
            margin: 50px auto;
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
        }
        .content h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
            color: #856404;
            text-align: left;
        }
        .warning-alert {
            font-size: 18px;
            margin-bottom: 15px;
            text-align: center;
        }
        .warning-alert-2 {
            font-size: 18px;
            margin-bottom: 15px;
            margin-top: 35px;
            text-align: center; 
        }
        .warning-description {
            font-size: 15px;
            margin-bottom: 15px;
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }
        .warning-list {
            list-style: none;
            padding-left: 0;
            margin: 15px 0;
        }
        .warning-list li {
            padding: 12px 15px;
            margin-bottom: 10px;
            background-color: #fff;
            border-left: 4px solid #ff9800;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
        }
        .warning-list li:last-child {
            margin-bottom: 0;
        }
        .member-info {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 16px;
        }
        .button-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn-withdrawal {
            background-color: #ff5252;
            color: white;
            font-size: 16px;
        }
        .btn-withdrawal:hover {
            background-color: #ff1744;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        @media (max-width: 480px) {
            .container {
                margin: 30px 16px;
                width: calc(100% - 32px);
            }
            .content {
                padding: 30px 20px;
            }
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-left">
                <h1>⚪︎⚪︎掲示板</h1>
                <div class="welcome-message">ようこそ <?php echo htmlspecialchars($member_name, ENT_QUOTES, 'UTF-8'); ?> 様</div>
            </div>
            <div class="header-right">
                <a href="top.php" class="btn btn-back">トップに戻る</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="content">
            <h2>会員退会</h2>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="member-info">
                <?php echo htmlspecialchars($member_name, ENT_QUOTES, 'UTF-8'); ?> 様
            </div>

            <div class="warning-box">
                <p class="warning-alert"><strong>⚠️ 退会に関する注意事項</strong></p>
                <p class="warning-description">▼ 退会後は下記のようになります ▼</p>
                <ul class="warning-list">
                    <li>ログインができなくなります</li>
                    <li>スレッド作成・いいね機能が利用できなくなります</li>
                </ul>
                <p class="warning-alert-2"><strong>この操作は取り消すことができません</strong></p>
            </div>

            <form method="POST" action="">
                <div class="button-group">
                    <a href="top.php" class="btn btn-back">キャンセル</a>
                    <button type="submit" name="withdrawal" class="btn btn-withdrawal">退会する</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>