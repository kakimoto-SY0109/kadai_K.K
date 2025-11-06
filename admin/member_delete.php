<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// 会員ID取得
$member_id = $_GET['id'] ?? '';

if ($member_id === '') {
    header('Location: member.php');
    exit;
}

try {
    // ソフトデリート実行
    $sql = "UPDATE members SET deleted_at = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $member_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // 会員一覧へリダイレクト
    header('Location: member.php');
    exit;
    
} catch (PDOException $e) {
    // エラー時も一覧に戻る
    header('Location: member.php');
    exit;
}
?>