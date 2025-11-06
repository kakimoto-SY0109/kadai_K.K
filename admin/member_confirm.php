<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? '';

if (!isset($_SESSION['form_data'])) {
    header('Location: member.php');
    exit;
}

$form_data = $_SESSION['form_data'];
$edit_mode = $_SESSION['edit_mode'] ?? false;
$password_changed = $_SESSION['password_changed'] ?? false;
$error_message = '';

// POST時のみCSRFトークンを検証
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['csrf_token'] ?? '';
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), (string)$posted)) {
        $_SESSION['error_message'] = '不正なリクエストです。もう一度やり直してください。';
        header('Location: member.php');
        exit;
    }

    if (isset($_POST['back'])) {
        $_SESSION['return_from_confirm'] = true;
        header('Location: member_regist.php' . ($edit_mode ? '?id=' . $form_data['member_id'] : ''));
        exit;
    }

    // 登録/更新ボタン
    if (isset($_POST['submit'])) {
        try {
            $gender_value = ($form_data['gender'] === '男性') ? 1 : 2;
            
            if ($edit_mode) {
                if ($password_changed) {
                    // パスワード変更あり
                    $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                    
                    $sql = "UPDATE members SET 
                                name_sei = :name_sei,
                                name_mei = :name_mei,
                                gender = :gender,
                                pref_name = :pref_name,
                                address = :address,
                                email = :email,
                                password = :password,
                                updated_at = NOW()
                            WHERE id = :id AND deleted_at IS NULL";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
                } else {
                    // パスワード変更なし
                    $sql = "UPDATE members SET 
                                name_sei = :name_sei,
                                name_mei = :name_mei,
                                gender = :gender,
                                pref_name = :pref_name,
                                address = :address,
                                email = :email,
                                updated_at = NOW()
                            WHERE id = :id AND deleted_at IS NULL";
                    
                    $stmt = $pdo->prepare($sql);
                }
                
                $stmt->bindValue(':name_sei', $form_data['last_name'], PDO::PARAM_STR);
                $stmt->bindValue(':name_mei', $form_data['first_name'], PDO::PARAM_STR);
                $stmt->bindValue(':gender', $gender_value, PDO::PARAM_INT);
                $stmt->bindValue(':pref_name', $form_data['prefecture'], PDO::PARAM_STR);
                $stmt->bindValue(':address', $form_data['address'], PDO::PARAM_STR);
                $stmt->bindValue(':email', $form_data['email'], PDO::PARAM_STR);
                $stmt->bindValue(':id', $form_data['member_id'], PDO::PARAM_INT);
                $stmt->execute();
                
                unset($_SESSION['form_data']);
                unset($_SESSION['edit_mode']);
                unset($_SESSION['password_changed']);
                unset($_SESSION['edit_member_id']);
                $_SESSION['edit_complete'] = true;
                
                header('Location: member_complete.php');
                exit;
                
            } else {
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);
                
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
                $_SESSION['register_complete'] = true;

                header('Location: member_complete.php');
                exit;
            }
            
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            die('データベースエラーが発生しました。');
        }
    }
}

$csrf = $_SESSION['csrf_token'];

// 表示用
$page_title = $edit_mode ? '会員編集確認' : '会員登録確認';
$page_icon = $edit_mode ? '✏️' : '🔐';
$confirm_message = $edit_mode ? '以下の内容で更新します。よろしければ「更新する」ボタンを押してください。' : '以下の内容で登録します。よろしければ「登録する」ボタンを押してください。';
$submit_button_text = $edit_mode ? '更新する' : '登録する';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
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
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        .password-change-notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
            color: #856404;
            text-align: center;
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
            .button-group {
                flex-direction: column;
            }
            .btn-back,
            .btn-submit {
                width: 100%;
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
        <h1><span class="admin-icon"><?php echo $page_icon; ?></span><?php echo $page_title; ?></h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        
        <p style="margin-bottom: 20px; color: #666;"><?php echo $confirm_message; ?></p>

        <?php if ($edit_mode && $password_changed): ?>
            <div class="password-change-notice">
                ⚠️ パスワードも同時に変更されます
            </div>
        <?php endif; ?>

        <div class="confirm-section">
            <div class="confirm-label">ID</div>
            <div class="confirm-value"><?php echo htmlspecialchars($form_data['member_id'], ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

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
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="button-group">
                <button type="submit" name="back" class="btn-back">戻る</button>
                <button type="submit" name="submit" class="btn-submit"><?php echo $submit_button_text; ?></button>
            </div>
        </form>
    </div>
</body>
</html>