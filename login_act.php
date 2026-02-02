<?php

session_start(); // セッション開始 [cite: 28, 85]
$lid = $_POST["lid"];
$lpw = $_POST["lpw"];

include("funcs.php");
$pdo = db_conn();

// 1. ユーザーを抽出
$sql = "SELECT * FROM gs_user_table WHERE lid=:lid AND life_flg=0";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
$status = $stmt->execute();

if($status==false){
    sql_error($stmt);
}

$val = $stmt->fetch();

// 2. 該当ユーザーがいるか確認（パスワード照合）
// ※本来は password_verify を推奨しますが、まずは直接一致でテスト
if( password_verify($_POST["lpw"] ,$val["lpw"])){
    $_SESSION["chk_ssid"] = session_id(); // 認証OKの証 [cite: 209, 215]
    $_SESSION["kanri_flg"] = $val["kanri_flg"];
    $_SESSION["name"] = $val["name"];
    header("Location: select.php");
} else {
    header("Location: login.php");
}
exit();
?>