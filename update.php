<?php
$location    = $_POST["location"];
$car_number  = $_POST["car_number"];
$description = $_POST["description"];
$id          = $_POST["id"];

include("funcs.php");
$pdo = db_conn();

// 修正点：gs_report_table を更新するように変更
$sql = "UPDATE gs_report_table SET location=:location, car_number=:car_number, description=:description WHERE id=:id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':location',    $location,    PDO::PARAM_STR);
$stmt->bindValue(':car_number',  $car_number,  PDO::PARAM_STR);
$stmt->bindValue(':description', $description, PDO::PARAM_STR);
$stmt->bindValue(':id',          $id,          PDO::PARAM_INT);
$status = $stmt->execute();

if($status==false){
    sql_error($stmt);
}else{
    redirect("select.php");
}
?>