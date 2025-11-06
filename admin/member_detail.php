<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? '';

// 会員ID取得
$member_id = $_GET['id'] ?? '';

if ($member_id === '') {
    header('Location: member.php');
    exit;
}

// 性別変換
function getGenderText($gender) {
    if ($gender == 1) {
        return '男性';
    } elseif ($gender == 2) {
        return '女性';
    }
    return '';
}

try {
    // 会員データ取得（退会済みも含む）
    $sql = "SELECT * FROM members WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        $error_message = "指定された会員が見つかりませんでした。";
    }
    
} catch (PDOException $e) {
    $error_message = "データベースエラー: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員詳細 - 管理画面</title>
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
        .deleted-notice {
            background-color: #ffebee;
            border: 1px solid #f44336;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
            color: #c62828;
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
        .btn-edit {
            padding: 12px 40px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-edit:hover {
            background-color: #45a049;
        }
        .btn-delete {
            padding: 12px 40px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-delete:hover {
            background-color: #d32f2f;
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
            .btn-edit,
            .btn-delete {
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
        <h1><span class="admin-icon">👤</span>会員詳細</h1>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php elseif (isset($member)): ?>
            
            <?php if ($member['deleted_at'] !== null): ?>
                <div class="deleted-notice">
                    ⚠️ この会員は退会済みです
                </div>
            <?php endif; ?>

            <div class="confirm-section">
                <div class="confirm-label">ID</div>
                <div class="confirm-value"><?php echo htmlspecialchars($member['id'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <div class="confirm-section">
                <div class="confirm-label">氏名</div>
                <div class="confirm-value"><?php echo htmlspecialchars($member['name_sei'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($member['name_mei'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <div class="confirm-section">
                <div class="confirm-label">性別</div>
                <div class="confirm-value"><?php echo htmlspecialchars(getGenderText($member['gender']), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <div class="confirm-section">
                <div class="confirm-label">住所</div>
                <div class="confirm-value"><?php echo htmlspecialchars($member['pref_name'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($member['address'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <div class="confirm-section">
                <div class="confirm-label">メールアドレス</div>
                <div class="confirm-value"><?php echo htmlspecialchars($member['email'], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>

            <div class="confirm-section">
                <div class="confirm-label">パスワード</div>
                <div class="confirm-value security-notice">セキュリティのため非表示</div>
            </div>

            <div class="button-group">
                <a href="member_regist.php?id=<?php echo urlencode($member['id']); ?>" class="btn-edit">編集</a>
                <?php if ($member['deleted_at'] === null): ?>
                    <a href="member_delete.php?id=<?php echo urlencode($member['id']); ?>" class="btn-delete">削除</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>