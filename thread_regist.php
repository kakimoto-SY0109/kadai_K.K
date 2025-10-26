<?php
require_once 'config.php';

// 未ログイン、URL直接入力の場合はトップページへ遷移
if (!isset($_SESSION['member_id'])) {
    header('Location: top.php');
    exit;
}

$errors = [];
$thread_title = '';
$comment = '';

if (isset($_POST['back']) && isset($_SESSION['form_data'])) {
    $thread_title = $_SESSION['form_data']['thread_title'];
    $comment = $_SESSION['form_data']['comment'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['back'])) {
    $thread_title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    
    // バリデーション
    if ($thread_title === '') {
        $errors[] = 'スレッドタイトルを入力してください。';
    } elseif (mb_strlen($thread_title) > 100) {
        $errors[] = 'スレッドタイトルは100文字以内で入力してください。';
    }
    
    if ($comment === '') {
        $errors[] = 'コメントを入力してください。';
    } elseif (mb_strlen($comment) > 500) {
        $errors[] = 'コメントは500文字以内で入力してください。';
    }
    
    if (empty($errors)) {
        $_SESSION['form_data'] = [
            'thread_title' => $thread_title,
            'comment' => $comment
        ];
        header('Location: thread_confirm.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新規スレッド作成</title>
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
            margin-bottom: 25px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: sans-serif;
        }
        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: #00897B;
        }
        input[type="text"]::placeholder,
        textarea::placeholder {
            color: #999;
            opacity: 1;
        }
        textarea {
            height: 200px;
            resize: vertical;
        }
        small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
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
            <h1>新規スレッド作成</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="thread_regist.php" method="post">
                <div class="form-group">
                    <label for="title">
                        スレッドタイトル
                    </label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($thread_title, ENT_QUOTES, 'UTF-8'); ?>" maxlength="101" placeholder="100文字以内で入力してください">
                </div>
                
                <div class="form-group">
                    <label for="comment">
                        コメント
                    </label>
                    <textarea id="comment" name="comment" maxlength="501" placeholder="500文字以内で入力してください"><?php echo htmlspecialchars($comment, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn">確認画面へ</button>
                    <a href="top.php" class="btn btn-secondary">トップに戻る</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>