<?php
include("funcs.php");
$pdo = db_conn();

$id = $_GET["id"];
// 修正点：gs_report_table に変更
$sql = "SELECT * FROM gs_report_table WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$status = $stmt->execute();

if($status==false) {
  sql_error($stmt);
}
$v = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>データ修正</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <link href="css/dragonball.css" rel="stylesheet">
</head>
<body>
<form method="POST" action="update.php">
    <div class="jumbotron">
        <fieldset>
            <legend>通報内容の修正</legend>
            <label>発生場所：<input type="text" name="location" value="<?=h($v["location"])?>"></label><br>
            <label>車両番号：<input type="text" name="car_number" value="<?=h($v["car_number"])?>"></label><br>
            <label>状況詳細：<textarea name="description" rows="4" cols="40"><?=h($v["description"])?></textarea></label><br>
            <input type="hidden" name="id" value="<?=h($v["id"])?>">
            <input type="submit" value="更新を実行">
        </fieldset>
    </div>
</form>
</body>
</html>