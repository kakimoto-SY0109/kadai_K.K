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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['form_data'])) {
        header('Location: thread_regist.php');
        exit;
    }

    // （二重送信防止）セッションデータ破棄
    $form = $_SESSION['form_data'];
    unset($_SESSION['form_data']);

    try {
        $member_id = $_SESSION['member_id'];

        $sql = $pdo->prepare(
            "INSERT INTO threads (member_id, title, content, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())"
        );
        $sql->execute([$member_id, $form['thread_title'], $form['comment']]);

        $_SESSION['success_message'] = 'スレッドを作成しました✓';

        header('Location: thread_list.php');
        exit;

    } catch (PDOException $e) {
        error_log('Thread creation error: ' . $e->getMessage());
        $_SESSION['error_message'] = 'スレッドの作成に失敗しました。しばらくしてから再試行してください。';
        header('Location: thread_regist.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>スレッド作成確認</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: sans-serif;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        .content {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
            border-bottom: 2px solid #00897B;
            padding-bottom: 8px;
        }
        .notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 4px;
            color: #856404;
        }
        .confirm-box {
            border: 1px solid #ddd;
            padding: 16px;
            background: #fafafa;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .confirm-label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
        }
        .confirm-value {
            background: #fff;
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            white-space: pre-wrap;
            line-height: 1.6;
            color: #333;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .btn {
            background: #4CAF50;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .btn-secondary {
            background: #9E9E9E;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 30px 16px;
                padding: 12px;
            }
            .content {
                padding: 20px;
            }
            h1 {
                font-size: 20px;
            }
            .button-group {
                flex-direction: column;
            }
            .btn,.btn-secondary {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>スレッド作成確認</h1>

            <div class="notice">
                以下の内容でスレッドを作成します。よろしければ「スレッドを作成する」をクリックしてください。
            </div>

            <div class="confirm-box">
                <div class="confirm-label">スレッドタイトル</div>
                <div class="confirm-value"><?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?></div>

                <div style="height:12px;"></div>

                <div class="confirm-label">コメント</div>
                <div class="confirm-value"><?php echo nl2br(htmlspecialchars($comment, ENT_QUOTES, 'UTF-8')); ?></div>
            </div>

            <form action="thread_confirm.php" method="post" id="submitForm">
                <div class="button-group">
                    <button type="submit" class="btn" id="submitBtn">スレッドを作成する</button>
                    <button type="submit" class="btn-secondary" name="back" value="1" formaction="thread_regist.php" formmethod="post">前に戻る</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        var form = document.getElementById('submitForm');
        var btn = document.getElementById('submitBtn');

        form.addEventListener('submit', function(e) {
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.textContent = '送信中...';
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                btn.disabled = false;
                btn.textContent = 'スレッドを作成する';
            }
        });
    })();
    </script>
</body>
</html>