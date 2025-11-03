<?php
require_once '../config.php';

// ログイン済みの場合は管理画面トップへ遷移
if (isset($_SESSION['admin_id'])) {
    header('Location: top.php');
    exit;
}

$error_message = '';
$login_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // バリデーション
    if (empty($login_id) || empty($password)) {
        $error_message = 'ログインIDもしくはパスワードを入力してください';
    } elseif (!preg_match('/^[a-zA-Z0-9]{7,10}$/', $login_id)) {
        $error_message = 'ログインIDは半角英数字7～10文字以内で入力してください';
    } elseif (!preg_match('/^[a-zA-Z0-9]{8,20}$/', $password)) {
        $error_message = 'パスワードは半角英数字8～20文字以内で入力してください';
    } else {
        $sql = $pdo->prepare("SELECT id, password, name FROM administers WHERE login_id = ? AND deleted_at IS NULL");
        $sql->execute([$login_id]);
        $admin = $sql->fetch();
            
        if ($admin && password_verify($password, $admin['password'])) {
            // ログイン成功の場合は管理画面トップへ遷移
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_login_id'] = $login_id;
                
            header('Location: top.php');
            exit;
        } else {
            $error_message = 'ログインIDもしくはパスワードが間違っています';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理画面ログイン</title>
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
        .title-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }
        .admin-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        h1 {
            color: #333;
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
        .input-wrapper {
            position: relative;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #757575;
        }
        .button-group {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        button {
            width: 100%;
            background-color: #9E9E9E;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #757575;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title-wrapper">
            <div class="admin-icon">🔐</div>
            <h1>管理画面ログイン</h1>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>ログインID</label>
                <div class="input-wrapper">
                    <input type="text" name="login_id" value="<?php echo htmlspecialchars($login_id, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="username">
                </div>
            </div>
            
            <div class="form-group">
                <label>パスワード</label>
                <div class="input-wrapper">
                    <input type="password" name="password" autocomplete="current-password">
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit">ログイン</button>
            </div>
        </form>
    </div>
</body>
</html>