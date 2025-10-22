<?php
require_once 'config.php';

$_SESSION = array();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

// トップページへ遷移
header('Location: top.php');
exit;
?>