<?php
// 1. エラー表示（開発時のみ）
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include("funcs.php");
sschk(); // 管理者認証

// 2. TCPDFの読み込み
// フォルダ名が「tcpdf」であることを確認してください
require_once('tcpdf/tcpdf.php');

// 3. データ取得
$id = $_GET["id"];
$pdo = db_conn();
$stmt = $pdo->prepare("SELECT * FROM gs_report_table WHERE id=:id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$v = $stmt->fetch();

if (!$v) {
    exit("データが見つかりませんでした。");
}

// 4. PDF初期設定
// P=縦向き, mm=単位ミリ, A4サイズ
$pdf = new TCPDF("P", "mm", "A4", true, "UTF-8" );
$pdf->SetCreator('まちの目システム');
$pdf->SetAuthor($_SESSION["name"]);
$pdf->SetTitle('地域安全状況報告書');

// ヘッダー・フッターを非表示にする
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// ★重要：日本語フォントの設定（小塚明朝を指定）
$pdf->SetFont('kozminproregular', '', 12);

// ページ追加
$pdf->AddPage();

// 5. HTMLコンテンツの作成（PDFのレイアウト）
$html = '
<style>
    h1 { text-align: center; color: #2563eb; border-bottom: 2px solid #2563eb; }
    .info-table { width: 100%; }
    .label { background-color: #f8fafc; font-weight: bold; width: 100px; }
    .content { width: 400px; }
    .image-box { text-align: center; margin-top: 20px; }
</style>

<h1>地域安全状況報告書（まちの目）</h1>
<p style="text-align:right;">報告ID: '.$id.' / 発行日: '.date("Y年m月d日").'</p>

<table border="1" cellpadding="8" cellspacing="0" class="info-table">
    <tr>
        <th class="label">発生場所</th>
        <td class="content">'.$v["location"].'</td>
    </tr>
    <tr>
        <th class="label">発生日時</th>
        <td class="content">'.$v["indate"].'</td>
    </tr>
    <tr>
        <th class="label">車両番号</th>
        <td class="content">'.($v["car_number"] ? $v["car_number"] : "なし").'</td>
    </tr>
    <tr>
        <th class="label">状況詳細</th>
        <td class="content">'.nl2br($v["description"]).'</td>
    </tr>
</table>

<h2 style="margin-top:20px;">【現場写真記録】</h2>
';

// 画像があればHTMLに追加
if($v["img"] && file_exists("upload/".$v["img"])){
    $html .= '<div class="image-box"><img src="upload/'.$v["img"].'" width="450"></div>';
} else {
    $html .= '<p style="color:gray;">写真は添付されていません。</p>';
}

// 6. HTMLを書き込み
$pdf->writeHTML($html, true, false, true, false, '');

// 7. 出力（I = ブラウザで表示）
$pdf->Output("report_".$id.".pdf", "I");
exit();