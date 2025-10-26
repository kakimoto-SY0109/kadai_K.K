<?php
require_once 'config.php';

// 未ログイン、URL直接入力の場合はトップページへ遷移
if (!isset($_SESSION['member_id'])) {
    header('Location: top.php');
    exit;
}

if (!isset($_SESSION['form_data'])) {
    header('Location: thread_regist.php');
    exit;
}

$thread_title = $_SESSION['form_data']['thread_title'];
$comment = $_SESSION['form_data']['comment'];

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    // 二重送信防止
    if ($_POST['token'] === $_SESSION['token']) {
        try {
            $member_id = $_SESSION['member_id'];
            
            $sql = $pdo->prepare(
                "INSERT INTO threads (member_id, title, content, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())"
                );
            $sql->execute([$member_id, $thread_title, $comment]);
            
            unset($_SESSION['form_data']);
            unset($_SESSION['token']);

            $_SESSION['success_message'] = 'スレッドを作成しました✓';
            
            header('Location: top.php');
            exit;
            
        } catch (PDOException $e) {
            error_log('Thread creation error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'スレッドの作成に失敗しました。しばらくしてから再試行してください。';
            
            header('Location: thread_regist.php');
            exit;
        }
    } else {
        header('Location: thread_regist.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スレッド作成確認</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .content {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            border-bottom: 2px solid #00897B;
            padding-bottom: 10px;
        }
        .notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #856404;
        }
        .confirm-box {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 30px;
            background-color: #fafafa;
            border-radius: 4px;
        }
        .confirm-item {
            margin-bottom: 25px;
        }
        .confirm-item:last-child {
            margin-bottom: 0;
        }
        .confirm-label {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            font-size: 14px;
        }
        .confirm-value {
            padding: 15px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
            color: #333;
        }
        .button-group {
            margin-top: 30px;
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
        .btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .btn-secondary {
            background-color: #9E9E9E;
        }
        .btn-secondary:hover {
            background-color: #757575;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>スレッド作成確認</h1>
            
            <div class="notice">
                以下の内容でスレッドを作成します。<br>
                よろしければ【スレッドを作成する】ボタンをクリックしてください。
            </div>
            
            <div class="confirm-box">
                <div class="confirm-item">
                    <div class="confirm-label">スレッドタイトル</div>
                    <div class="confirm-value"><?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                
                <div class="confirm-item">
                    <div class="confirm-label">コメント</div>
                    <div class="confirm-value"><?php echo nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')); ?></div>
                </div>
            </div>
            
            <form action="thread_confirm.php" method="post" id="submitForm">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="button-group">
                    <button type="submit" class="btn" id="submitBtn">スレッドを作成する</button>
                    <button type="button" class="btn btn-secondary" onclick="goBack()">前に戻る</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function goBack() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = 'thread_regist.php';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'back';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
        
        // 二重送信防止
        document.getElementById('submitForm').addEventListener('submit', function(e) {
            var btn = document.getElementById('submitBtn');
            if (btn.disabled) {
                e.preventDefault();
                return false;
            }
            btn.disabled = true;
            btn.textContent = '送信中...';
        });
        
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                var btn = document.getElementById('submitBtn');
                btn.disabled = false;
                btn.textContent = 'スレッドを作成する';
            }
        });
    </script>
</body>
</html>