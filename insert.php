<?php
// 1. POSTデータ取得（空チェック付き）
$location      = isset($_POST['location']) ? $_POST['location'] : '';
$lat           = isset($_POST['lat']) ? $_POST['lat'] : '';
$lng           = isset($_POST['lng']) ? $_POST['lng'] : '';
$car_number    = isset($_POST['car_number']) ? $_POST['car_number'] : '';
$incident_type = isset($_POST['incident_type']) ? $_POST['incident_type'] : '未設定';
$description   = isset($_POST['description']) ? $_POST['description'] : '';

// 種別を詳細に埋め込む
$full_description = "【" . $incident_type . "】" . $description;

// 2. DB接続
include "funcs.php";
$pdo = db_conn();

// 3. データ登録SQL作成
$sql = "INSERT INTO gs_report_table(location, lat, lng, car_number, description, indate) 
        VALUES(:a1, :lat, :lng, :a2, :a3, sysdate())";
$stmt = $pdo->prepare($sql);

// 4. バインド変数
$stmt->bindValue(':a1',  $location,         PDO::PARAM_STR);
$stmt->bindValue(':lat', $lat,              PDO::PARAM_STR);
$stmt->bindValue(':lng', $lng,              PDO::PARAM_STR);
$stmt->bindValue(':a2',  $car_number,       PDO::PARAM_STR);
$stmt->bindValue(':a3',  $full_description, PDO::PARAM_STR);

// 5. 実行
$status = $stmt->execute();

// 6. 処理後
if($status == false){
    // エラーがある場合は詳細を表示して止める
    $error = $stmt->errorInfo();
    exit("QueryError:".$error[2]);
} else {
    // 成功したら一覧へ飛ばす（絶対パスに近い形に）
    header("Location: select.php");
    exit();
}
?>