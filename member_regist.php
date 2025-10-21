<?php
session_start();
// db接続
require_once 'config.php';

$errors = [];
$form_data = [
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

// 初期表示or確認画面からの遷移
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 確認画面
    if (isset($_SESSION['return_from_confirm']) && $_SESSION['return_from_confirm'] === true) {
        $form_data = $_SESSION['form_data'];
        $form_data['password'] = '';
        $form_data['password_confirm'] = '';
        unset($_SESSION['return_from_confirm']);
    } else {
        unset($_SESSION['form_data']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data['last_name'] = trim($_POST['last_name'] ?? '');
    $form_data['first_name'] = trim($_POST['first_name'] ?? '');
    $form_data['gender'] = $_POST['gender'] ?? '';
    $form_data['prefecture'] = $_POST['prefecture'] ?? '';
    $form_data['address'] = trim($_POST['address'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['password'] = $_POST['password'] ?? '';
    $form_data['password_confirm'] = $_POST['password_confirm'] ?? '';

    // バリデーション
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
    }

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

    // 確認画面への遷移
    if (empty($errors)) {
        $_SESSION['form_data'] = $form_data;
        header('Location: member_confirm.php');
        exit;
    } else {
        $_SESSION['form_data'] = $form_data;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>
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
        .error-messages {
            background-color: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error-messages ul {
            margin-left: 20px;
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
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #4CAF50;
        }
        .radio-group {
            display: flex;
            gap: 20px;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            font-weight: normal;
        }
        .radio-group input[type="radio"] {
            margin-right: 5px;
        }
        .button-group {
            margin-top: 30px;
            text-align: center;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>会員登録フォーム</h1>

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
                    <!--
                    <label>
                        <input type="radio" name="gender" value="その他">
                        その他
                    </label>
                    -->
                </div>
            </div>

            <div class="form-group">
                <label>住所（都道府県）</label>
                <select name="prefecture">
                    <option value="">選択してください</option>
                    <!--
                    <option value="不正な県">不正な県（テスト用）</option>
                    -->
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
                <!--
                <input type="text" name="email" value="<?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?>">
                -->
                <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password" value="<?php echo htmlspecialchars($form_data['password'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="form-group">
                <label>パスワード確認</label>
                <input type="password" name="password_confirm" value="<?php echo htmlspecialchars($form_data['password_confirm'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>

            <div class="button-group">
                <button type="submit">確認画面へ</button>
            </div>
        </form>
    </div>
</body>
</html>