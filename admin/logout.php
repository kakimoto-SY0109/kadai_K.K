<?php
require_once '../config.php';

// セッションを破棄
session_destroy();

// ログイン画面へリダイレクト
header('Location: login.php');
exit;
?>