<?php
require_once 'config.php';

$posted = $_POST['csrf_token'] ?? '';
if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)$posted)) {
    $_SESSION['error_message'] = '不正なリクエストです。もう一度やり直してください。';
    header('Location: member_regist.php');
    exit;
}
unset($_SESSION['csrf_token']);
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
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 50px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            color: #4CAF50;
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .button-group {
            margin-top: 30px;
        }
        a {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 40px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }
        a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>会員登録が完了しました</h1>
        <div class="message">
            会員登録ありがとうございます。<br>
            登録いただいたメールアドレスとパスワードでログインできます。
        </div>
        <div class="button-group">
            <a href="top.php">トップに戻る</a>
        </div>
    </div>
</body>
</html>