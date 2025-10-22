<?php
require_once 'config.php';

// ログイン済みの場合はトップページへ遷移
if (isset($_SESSION['member_id'])) {
    header('Location: top.php');
    exit;
}

$error_message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // バリデーション
    if (empty($email) || empty($password)) {
        $error_message = 'IDもしくはパスワードを入力してください';
    } else {
        $sql = $pdo->prepare("SELECT id, password, name_sei, name_mei FROM members WHERE email = ?");
        $sql->execute([$email]);
        $member = $sql->fetch();
            
        if ($member && password_verify($password, $member['password'])) {
            // ログイン成功の場合はトップページへ遷移
            $_SESSION['member_id'] = $member['id'];
            $_SESSION['member_name'] = $member['name_sei'] . ' ' . $member['name_mei'];
                
            header('Location: top.php');
            exit;
        } else {
            $error_message = 'IDもしくはパスワードが間違っています';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
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
            max-width: 500px;
            margin: 50px auto;
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
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
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .button-group {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        button {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .link-group {
            margin-top: 20px;
            text-align: center;
        }
        .link-group a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 14px;
        }
        .link-group a:hover {
            text-decoration: underline;
        }
        .separator {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ログイン</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>メールアドレス（ログインID）</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="email">
            </div>
            
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password" autocomplete="current-password">
            </div>
            
            <div class="button-group">
                <button type="submit">ログイン</button>
            </div>
        </form>
        
        <div class="link-group">
            <a href="top.php">トップに戻る</a>
            <div class="separator">|</div>
            <a href="member_regist.php">新規会員登録はこちら</a>
        </div>
    </div>
</body>
</html>