<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$admin_name = $_SESSION['admin_name'] ?? '';

$errors = [];
$form_data = [
    'member_id' => '',
    'last_name' => '',
    'first_name' => '',
    'gender' => '',
    'prefecture' => '',
    'address' => '',
    'email' => '',
    'password' => '',
    'password_confirm' => ''
];

// 47都道府県リスト
$prefectures = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
    '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
    '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
    '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
];

$edit_mode = false;
$edit_id = $_GET['id'] ?? '';

if (!empty($edit_id) && is_numeric($edit_id)) {
    $edit_mode = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['return_from_confirm']) && $_SESSION['return_from_confirm'] === true) {
        $form_data = $_SESSION['form_data'];
        $form_data['password'] = '';
        $form_data['password_confirm'] = '';
        $edit_mode = $_SESSION['edit_mode'] ?? false;
        unset($_SESSION['return_from_confirm']);
    } else {
        unset($_SESSION['form_data']);
        
        if ($edit_mode) {
            try {
                $sql = "SELECT * FROM members WHERE id = :id AND deleted_at IS NULL";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
                $stmt->execute();
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$member) {
                    header('Location: member.php');
                    exit;
                }
                
                $form_data['member_id'] = $member['id'];
                $form_data['last_name'] = $member['name_sei'];
                $form_data['first_name'] = $member['name_mei'];
                $form_data['gender'] = ($member['gender'] == 1) ? '男性' : '女性';
                $form_data['prefecture'] = $member['pref_name'];
                $form_data['address'] = $member['address'];
                $form_data['email'] = $member['email'];
                
                $_SESSION['edit_member_id'] = $member['id'];
                
            } catch (PDOException $e) {
                $errors[] = 'データの取得に失敗しました。';
            }
        } else {
            $form_data['member_id'] = '登録後に自動採番';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['member_id'] = trim($_POST['member_id'] ?? '');
    $form_data['last_name'] = trim($_POST['last_name'] ?? '');
    $form_data['first_name'] = trim($_POST['first_name'] ?? '');
    $form_data['gender'] = $_POST['gender'] ?? '';
    $form_data['prefecture'] = $_POST['prefecture'] ?? '';
    $form_data['address'] = trim($_POST['address'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['password_confirm'] = $_POST['password_confirm'] ?? '';

    if ($form_data['member_id'] !== '登録後に自動採番' && is_numeric($form_data['member_id'])) {
        $edit_mode = true;
    }

    // バリデーション
    if ($edit_mode && (empty($form_data['member_id']) || !is_numeric($form_data['member_id']))) {
        $errors[] = '会員IDが取得できませんでした。';
    }

    if (empty($form_data['last_name'])) {
        $errors[] = '氏名（姓）を入力してください。';
    } elseif (mb_strlen($form_data['last_name']) > 20) {
        $errors[] = '氏名（姓）は20文字以内で入力してください。';
    }

    if (empty($form_data['first_name'])) {
        $errors[] = '氏名（名）を入力してください。';
    } elseif (mb_strlen($form_data['first_name']) > 20) {
        $errors[] = '氏名（名）は20文字以内で入力してください。';
    }

    if (empty($form_data['gender'])) {
        $errors[] = '性別を選択してください。';
    } elseif ($form_data['gender'] !== '男性' && $form_data['gender'] !== '女性') {
        $errors[] = '性別の値が不正です。';
    }

    if (empty($form_data['prefecture'])) {
        $errors[] = '都道府県を選択してください。';
    } elseif (!in_array($form_data['prefecture'], $prefectures, true)) {
        $errors[] = '都道府県の値が不正です。';
    }

    if (!empty($form_data['address']) && mb_strlen($form_data['address']) > 100) {
        $errors[] = '住所は100文字以内で入力してください。';
    }

    if (empty($form_data['email'])) {
        $errors[] = 'メールアドレスを入力してください。';
    } elseif (mb_strlen($form_data['email']) > 200) {
        $errors[] = 'メールアドレスは200文字以内で入力してください。';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'メールアドレスの形式が正しくありません。';
    } else {
        if ($edit_mode) {
            $sql = "SELECT COUNT(*) FROM members WHERE email = :email AND id != :id AND deleted_at IS NULL";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $form_data['email'], PDO::PARAM_STR);
            $stmt->bindValue(':id', $form_data['member_id'], PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
        } else {
            $sql = "SELECT COUNT(*) FROM members WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $form_data['email'], PDO::PARAM_STR);
            $stmt->execute();
            $count = $stmt->fetchColumn();
        }

        if ($count > 0) {
            $errors[] = 'このメールアドレスは既に登録されています。';
        }
    }

    $password_changed = !empty($form_data['password']) || !empty($form_data['password_confirm']);
    
    if ($edit_mode) {
        if ($password_changed) {
            if (empty($form_data['password'])) {
                $errors[] = 'パスワードを入力してください。';
            } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $form_data['password'])) {
                $errors[] = 'パスワードは半角英数字で入力してください。';
            } elseif (mb_strlen($form_data['password']) < 8 || mb_strlen($form_data['password']) > 20) {
                $errors[] = 'パスワードは8文字以上20文字以内で入力してください。';
            }

            if (empty($form_data['password_confirm'])) {
                $errors[] = 'パスワード確認を入力してください。';
            } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $form_data['password_confirm'])) {
                $errors[] = 'パスワード確認は半角英数字で入力してください。';
            } elseif (mb_strlen($form_data['password_confirm']) < 8 || mb_strlen($form_data['password_confirm']) > 20) {
                $errors[] = 'パスワード確認は8文字以上20文字以内で入力してください。';
            } elseif ($form_data['password'] !== $form_data['password_confirm']) {
                $errors[] = 'パスワードとパスワード確認が一致しません。';
            }
        }
    } else {
        if (empty($form_data['password'])) {
            $errors[] = 'パスワードを入力してください。';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $form_data['password'])) {
            $errors[] = 'パスワードは半角英数字で入力してください。';
        } elseif (mb_strlen($form_data['password']) < 8 || mb_strlen($form_data['password']) > 20) {
            $errors[] = 'パスワードは8文字以上20文字以内で入力してください。';
        }

        if (empty($form_data['password_confirm'])) {
            $errors[] = 'パスワード確認を入力してください。';
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $form_data['password_confirm'])) {
            $errors[] = 'パスワード確認は半角英数字で入力してください。';
        } elseif (mb_strlen($form_data['password_confirm']) < 8 || mb_strlen($form_data['password_confirm']) > 20) {
            $errors[] = 'パスワード確認は8文字以上20文字以内で入力してください。';
        } elseif ($form_data['password'] !== $form_data['password_confirm']) {
            $errors[] = 'パスワードとパスワード確認が一致しません。';
        }
    }

    if (empty($errors)) {
        $_SESSION['form_data'] = $form_data;
        $_SESSION['edit_mode'] = $edit_mode;
        if ($edit_mode) {
            $_SESSION['password_changed'] = $password_changed;
        }
        header('Location: member_confirm.php');
        exit;
    } else {
        $_SESSION['form_data'] = $form_data;
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// 表示用
$page_title = $edit_mode ? '会員編集' : '会員登録';
$page_icon = $edit_mode ? '✏️' : '🔐';
$form_title = $edit_mode ? '会員編集フォーム' : '会員登録フォーム';
$password_placeholder = $edit_mode ? '変更しない場合は空欄' : '';
$password_label_suffix = $edit_mode ? '（変更する場合のみ入力）' : '';
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
        .auth-block {
            max-width: 1200px;
            width: 100%;
            margin: 30px auto;
            padding: 0 20px;
            box-sizing: border-box;
        }
        .auth-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border: 2px solid #00897B;
                    }
        .auth-container h3 {
            color: #00897B;
            margin-bottom: 15px;
            font-size: 22px;
        }
        .auth-container p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .auth-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-login {
            background-color: #00897B;
        }
        .btn-login:hover {
            background-color: #00695C;
        }
        .btn-register {
            background-color: #4CAF50;
        }
        .btn-register:hover {
            background-color: #45a049;
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
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .error-messages {
            background-color: #ffebee;
            border: 1px solid #ef5350;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .error-messages ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .error-messages li {
            color: #c62828;
            margin-bottom: 5px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group input[readonly] {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        .radio-group {
            display: flex;
            gap: 20px;
        }
        .radio-group label {
            font-weight: normal;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .button-group {
            text-align: center;
            margin-top: 30px;
        }
        .button-group a {
            background-color: #00897B;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .button-group a:hover {
            background-color: #00695C;
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
            .auth-block {
                margin: 20px 16px;
                width: calc(100% - 32px);
                padding: 0;
            }
            .auth-container {
                padding: 30px 20px;
            }
            .auth-container h3 {
                font-size: 18px;
            }
            .auth-buttons {
                flex-direction: column;
                gap: 10px;
            }
            .auth-buttons .btn {
                width: 100%;
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
        <h1><span class="admin-icon"><?php echo $page_icon; ?></span><?php echo $form_title; ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>ID</label>
                <input type="text" name="member_id" value="<?php echo htmlspecialchars($form_data['member_id'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
            </div>

            <div class="form-group">
                <label>氏名（姓）</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($form_data['last_name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label>氏名（名）</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($form_data['first_name'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label>性別</label>
                <div class="radio-group">
                    <label>
                        <input type="radio" name="gender" value="男性" <?php echo ($form_data['gender'] === '男性') ? 'checked' : ''; ?>>
                        男性
                    </label>
                    <label>
                        <input type="radio" name="gender" value="女性" <?php echo ($form_data['gender'] === '女性') ? 'checked' : ''; ?>>
                        女性
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>住所（都道府県）</label>
                <select name="prefecture">
                    <option value="">選択してください</option>
                    <?php foreach ($prefectures as $pref): ?>
                        <option value="<?php echo htmlspecialchars($pref, ENT_QUOTES, 'UTF-8'); ?>" 
                            <?php echo ($form_data['prefecture'] === $pref) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pref, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>住所（それ以降の住所）</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($form_data['address'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="例：〇〇市〇〇町1-2-3">
            </div>

            <div class="form-group">
                <label>メールアドレス（ログインID）</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label>パスワード<?php echo $password_label_suffix; ?></label>
                <input type="password" name="password" value="<?php echo htmlspecialchars($form_data['password'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $password_placeholder; ?>">
            </div>

            <div class="form-group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirm" value="<?php echo htmlspecialchars($form_data['password_confirm'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?php echo $password_placeholder; ?>">
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-member">確認画面へ</button>
            </div>

        </form>
    </div>
</body>
</html>