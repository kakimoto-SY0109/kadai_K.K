<?php
require_once '../config.php';

// 未ログインの場合はログイン画面へ遷移
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? '';

// 検索条件取得
$search_id = $_GET['id'] ?? '';
$search_gender = $_GET['gender'] ?? [];
$search_pref = $_GET['pref_name'] ?? '';
$search_keyword = $_GET['keyword'] ?? '';

// ソート条件取得
$sort_column = $_GET['sort'] ?? 'id';
$sort_order = $_GET['order'] ?? 'DESC';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 都道府県リスト
$prefectures = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
    '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
    '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
    '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
    '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
];

// 性別変換
function getGenderText($gender) {
    if ($gender == 1) {
        return '男性';
    } elseif ($gender == 2) {
        return '女性';
    }
    return '';
}

function getFullAddress($member) {
    $address_parts = [];
    
    if (!empty($member['pref_name'])) {
        $address_parts[] = $member['pref_name'];
    }
    if (!empty($member['address'])) {
        $address_parts[] = $member['address'];
    }
    
    return implode('', $address_parts);
}

try {
    $where = [];
    $params = [];
    
    if ($search_id !== '') {
        $where[] = "id = :id";
        $params[':id'] = $search_id;
    }
    
    // 性別検索（OR）
    // チェックボックスの値（男性/女性）をDBの値（1/2）に変換
    if (!empty($search_gender)) {
        $gender_conditions = [];
        foreach ($search_gender as $index => $gender) {
            $key = ":gender{$index}";
            // 男性が1、女性が2
            $gender_value = ($gender === '男性') ? 1 : 2;
            $gender_conditions[] = "gender = {$key}";
            $params[$key] = $gender_value;
        }
        if (!empty($gender_conditions)) {
            $where[] = "(" . implode(" OR ", $gender_conditions) . ")";
        }
    }
    
    // 都道府県検索
    if ($search_pref !== '') {
        $where[] = "pref_name = :pref_name";
        $params[':pref_name'] = $search_pref;
    }
    
    // フリーワード検索（OR）
    if ($search_keyword !== '') {
        $where[] = "(name_sei LIKE :keyword1 OR name_mei LIKE :keyword2)";
        $params[':keyword1'] = "%{$search_keyword}%";
        $params[':keyword2'] = "%{$search_keyword}%";
    }
    
    // 退会済みユーザー除く
    $where[] = "deleted_at IS NULL";
    $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $allowed_sort = ['id', 'created_at'];
    if (!in_array($sort_column, $allowed_sort)) {
        $sort_column = 'id';
    }
    $sort_order = ($sort_order === 'ASC') ? 'ASC' : 'DESC';
    
    // 総件数取得
    $count_sql = "SELECT COUNT(*) FROM members {$where_sql}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_count = $count_stmt->fetchColumn();
    $total_pages = ceil($total_count / $per_page);
    
    // 会員データ取得
    $sql = "SELECT * FROM members {$where_sql} ORDER BY {$sort_column} {$sort_order} LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "データベースエラー: " . $e->getMessage();
    $members = [];
    $total_pages = 0;
}

// ページャー（3ページ）
$pager_start = max(1, $page - 1);
$pager_end = min($total_pages, $pager_start + 2);
if ($pager_end - $pager_start < 2) {
    $pager_start = max(1, $pager_end - 2);
}

// ソート用URL
function buildUrl($params) {
    return '?' . http_build_query($params);
}

$current_params = [
    'id' => $search_id,
    'gender' => $search_gender,
    'pref_name' => $search_pref,
    'keyword' => $search_keyword,
    'sort' => $sort_column,
    'order' => $sort_order,
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員一覧 - 管理画面</title>
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
            display: flex;
            flex-direction: column;
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
        .btn {
            display: inline-block;
            background-color: #757575;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #616161;
        }
        .btn-logout {
            background-color: #ff5252;
            color: white;
        }
        .btn-logout:hover {
            background-color: #ff1744;
        }
        .btn-top {
            background-color: #616161;
        }
        .btn-top:hover {
            background-color: #424242;
        }
        .container {
            max-width: 1200px;
            width: 100%;
            margin: 30px auto;
            padding: 20px;
            flex: 1;
        }
        .search-box {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .search-box h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .search-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .form-group label {
            min-width: 120px;
            font-weight: bold;
            color: #333;
        }
        .form-group input[type="text"],
        .form-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            flex: 1;
            max-width: 300px;
        }
        .checkbox-group {
            display: flex;
            gap: 15px;
        }
        .checkbox-group label {
            font-weight: normal;
            min-width: auto;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .btn-search {
            background-color: #2196F3;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            align-self: center;
            margin-top: 10px;
        }
        .btn-search:hover {
            background-color: #1976D2;
        }
        .btn-add {
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            align-self: center;
            margin-top: 10px;
        }
        .btn-add:hover {
            background-color: #1976D2;
        }
        
        .btn-edit {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 6px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        .btn-edit:hover {
            background-color: #45a049
        }

        .result-box {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .result-header h2 {
            color: #333;
            font-size: 20px;
        }
        .result-count {
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
            white-space: nowrap;
        }
        td {
            color: #666;
        }
        .sort-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: #2196F3;
            font-size: 12px;
            margin-left: 5px;
        }
        .sort-btn:hover {
            color: #1976D2;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: white;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .page-btn:hover {
            background-color: #f5f5f5;
        }
        .page-btn.active {
            background-color: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        .page-btn.disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .btn-detail {
            display: inline-block;
            background-color: #757575;
            color: white;
            padding: 6px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            text-align: center;
        }
        .btn-detail:hover {
            background-color: #616161;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .form-group {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-group label {
                min-width: auto;
            }
            .form-group input[type="text"],
            .form-group select {
                max-width: 100%;
                width: 100%;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px 4px;
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
                <a href="top.php" class="btn btn-top">トップに戻る</a>
                <a href="logout.php" class="btn btn-logout">ログアウト</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="search-box">
            <h2>会員検索</h2>
            <form method="GET" action="" class="search-form">
                <div class="form-group">
                    <label>ID</label>
                    <input type="text" name="id" value="<?php echo htmlspecialchars($search_id, ENT_QUOTES, 'UTF-8'); ?>" placeholder="IDを入力">
                </div>
                
                <div class="form-group">
                    <label>性別</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="gender[]" value="男性" <?php echo in_array('男性', $search_gender) ? 'checked' : ''; ?>>
                            男性
                        </label>
                        <label>
                            <input type="checkbox" name="gender[]" value="女性" <?php echo in_array('女性', $search_gender) ? 'checked' : ''; ?>>
                            女性
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>都道府県</label>
                    <select name="pref_name">
                        <option value="">選択してください</option>
                        <?php foreach ($prefectures as $pref): ?>
                            <option value="<?php echo htmlspecialchars($pref, ENT_QUOTES, 'UTF-8'); ?>" 
                                <?php echo $search_pref === $pref ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($pref, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>フリーワード</label>
                    <input type="text" name="keyword" value="<?php echo htmlspecialchars($search_keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="氏名で検索">
                </div>
                
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_column, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order, ENT_QUOTES, 'UTF-8'); ?>">
                
                <button type="submit" class="btn-search">検索する</button>
            </form>
        </div>

        <div class="result-box">
            <div class="result-header">
                <h2>会員一覧</h2>
                <div class="result-count">全 <?php echo number_format($total_count); ?> 件</div>
                <a href="member_regist.php" class="btn-add">新規会員登録</a>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if (!empty($members)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>
                                ID
                                <button type="button" class="sort-btn" onclick="location.href='<?php 
                                    $params = $current_params;
                                    $params['sort'] = 'id';
                                    $params['order'] = ($sort_column === 'id' && $sort_order === 'DESC') ? 'ASC' : 'DESC';
                                    $params['page'] = $page;
                                    echo buildUrl($params);
                                ?>'">▼</button>
                            </th>
                            <th>氏名</th>
                            <th>性別</th>
                            <th>住所</th>
                            <th>
                                登録日時
                                <button type="button" class="sort-btn" onclick="location.href='<?php 
                                    $params = $current_params;
                                    $params['sort'] = 'created_at';
                                    $params['order'] = ($sort_column === 'created_at' && $sort_order === 'DESC') ? 'ASC' : 'DESC';
                                    $params['page'] = $page;
                                    echo buildUrl($params);
                                ?>'">▼</button>
                            </th>
                            <th>編集</th>
                            <th>詳細</th>    
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><a href="member_detail.php?id=<?php echo urlencode($member['id']); ?>" class="name-link"><?php echo htmlspecialchars($member['name_sei'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($member['name_mei'], ENT_QUOTES, 'UTF-8'); ?></a></td>
                                <td><?php echo htmlspecialchars(getGenderText($member['gender']), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(getFullAddress($member), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($member['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><a href="member_regist.php?id=<?php echo urlencode($member['id']); ?>" class="btn-edit">編集</a></td>
                                <td><a href="member_detail.php?id=<?php echo urlencode($member['id']); ?>" class="btn-detail">詳細</a></td>
                                </a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="<?php 
                                $params = $current_params;
                                $params['page'] = $page - 1;
                                echo buildUrl($params);
                            ?>" class="page-btn">前へ</a>
                        <?php endif; ?>

                        <?php for ($i = $pager_start; $i <= $pager_end; $i++): ?>
                            <a href="<?php 
                                $params = $current_params;
                                $params['page'] = $i;
                                echo buildUrl($params);
                            ?>" class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="<?php 
                                $params = $current_params;
                                $params['page'] = $page + 1;
                                echo buildUrl($params);
                            ?>" class="page-btn">次へ</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-data">該当する会員が見つかりませんでした</div>
            <?php endif; ?>
        </div>
    </div>  
</body>
</html>