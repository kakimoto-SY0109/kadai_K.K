<?php
session_start();
require_once 'config.php';

// 直接URLで表示
if (!isset($_SESSION['form_data'])) {
    header('Location: member_regist.php');
    exit;
}

$form_data = $_SESSION['form_data'];
$error_message = '';

if (isset($_POST['back'])) {
    // 確認画面フラグ
    $_SESSION['return_from_confirm'] = true;
    header('Location: member_regist.php');
    exit;
}

if (isset($_POST['submit'])) {
    try {
        $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
        $gender_value = ($form_data['gender'] === '男性') ? 1 : 2;
        
        $sql = "INSERT INTO members (
                    name_sei, 
                    name_mei, 
                    gender, 
                    pref_name, 
                    address, 
                    email, 
                    password, 
                    created_at,
                    updated_at
                ) VALUES (
                    :name_sei, 
                    :name_mei, 
                    :gender, 
                    :pref_name, 
                    :address, 
                    :email, 
                    :password, 
                    NOW(),
                    NOW()
                )";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->bindValue(':name_sei', $form_data['last_name'], PDO::PARAM_STR);
        $stmt->bindValue(':name_mei', $form_data['first_name'], PDO::PARAM_STR);
        $stmt->bindValue(':gender', $gender_value, PDO::PARAM_INT);
        $stmt->bindValue(':pref_name', $form_data['prefecture'], PDO::PARAM_STR);
        $stmt->bindValue(':address', $form_data['address'], PDO::PARAM_STR);
        $stmt->bindValue(':email', $form_data['email'], PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->execute();
    
        unset($_SESSION['form_data']);
        
        // 完了画面へ遷移
        header('Location: member_complete.php');
        exit;
        
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            die('データベースエラーが発生しました。');
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録確認</title>
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
            padding: 30px;
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
        }
        .confirm-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .confirm-section:last-of-type {
            border-bottom: none;
        }
        .confirm-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 8px;
        }
        .confirm-value {
            color: #333;
            font-size: 16px;
            padding: 8px 0;
        }
        .security-notice {
            color: #999;
            font-style: italic;
        }
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        button {
            padding: 12px 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-back {
            background-color: #999;
            color: white;
        }
        .btn-back:hover {
            background-color: #777;
        }
        .btn-submit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>会員登録確認</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-bottom: 20px; color: #666;">以下の内容で登録します。よろしければ「登録する」ボタンを押してください。</p>

        <div class="confirm-section">
            <div class="confirm-label">氏名（姓）</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['last_name'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">氏名（名）</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['first_name'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">性別</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['gender'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">住所（都道府県）</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['prefecture'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">住所（それ以降の住所）</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['address'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">メールアドレス（ログインID）</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <div class="confirm-section">
            <div class="confirm-label">パスワード</div>
            <div class="confirm-value security-notice">セキュリティのため非表示</div>
        </div>

        <form method="POST" action="">
            <div class="button-group">
                <button type="submit" name="back" class="btn-back">戻る</button>
                <button type="submit" name="submit" class="btn-submit">登録する</button>
            </div>
        </form>
    </div>
</body>
</html>