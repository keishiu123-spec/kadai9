<?php
require_once('funcs.php');
require_once('tcpdf/tcpdf.php');

// 1. 全通報データをDBから取得
$pdo = db_conn();
$stmt = $pdo->prepare("SELECT location, description, indate FROM gs_report_table ORDER BY indate DESC");
$status = $stmt->execute();

$report_list = "";
if ($status == false) {
    sql_error($stmt);
} else {
    while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // AIが認識しやすいように整形
        $report_list .= "【発生日】" . $res["indate"] . " 【場所】" . $res["location"] . " 【内容】" . $res["description"] . "\n";
    }
}

// 2. ChatGPT APIで分析を実行
$api_key = get_chatgpt_api_key();
$prompt = "以下の交通通報データを分析し、地域安全レポートを作成してください。
【指示】
1. 頻発している違反の種類をまとめてください。
2. 危険が集中している場所（例：砧公園前など）を特定してください。
3. 住民ができる対策を3つ提案してください。

データ：\n" . $report_list;

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-3.5-turbo", 
    "messages" => [
        ["role" => "system", "content" => "あなたは地域の安全担当者です。データに基づき簡潔に回答してください。"],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.7
]));

$response = curl_exec($ch);
$result = json_decode($response, true);

// 回答の取得とエラーチェック
if (isset($result['choices'][0]['message']['content'])) {
    $ai_analysis = $result['choices'][0]['message']['content'];
} else {
    // エラーメッセージを具体的に表示
    $error_msg = isset($result['error']['message']) ? $result['error']['message'] : '不明なエラーが発生しました。APIキーや残高を確認してください。';
    $ai_analysis = "【分析エラー】\n" . $error_msg;
}
curl_close($ch);

// 3. PDFの生成
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
// 日本語フォントの設定（kozminproregularを使用）
$pdf->SetFont('kozminproregular', '', 11);

// レポートのHTML構造
$html = '
    <h1 style="text-align:center; color:#2563eb; border-bottom: 2px solid #2563eb; padding-bottom:10px;">地域安全分析レポート（AI生成）</h1>
    <p style="text-align:right;">作成日: ' . date('Y-m-d H:i') . '</p>
    
    <h3 style="background-color:#eff6ff; padding:5px;">■ AIによる分析結果と対策提言</h3>
    <div style="padding:10px; border:1px solid #e2e8f0; line-height:1.6;">
        ' . nl2br(htmlspecialchars($ai_analysis)) . '
    </div>
    
    <h3 style="background-color:#f1f5f9; padding:5px; margin-top:20px;">■ 解析の根拠となった通報データ（全件）</h3>
    <div style="font-size:9pt; color:#475569;">
        ' . nl2br(htmlspecialchars($report_list)) . '
    </div>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Safety_Report_AI.pdf', 'D');