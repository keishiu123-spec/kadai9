<?php
session_start();
require_once('funcs.php');
sschk(); // 管理者権限チェック

// 1. 全通報データをDBから取得
$pdo = db_conn();
$stmt = $pdo->prepare("SELECT location, description, indate FROM gs_report_table ORDER BY indate DESC");
$status = $stmt->execute();

$report_list = "";
if ($status == false) {
    sql_error($stmt);
} else {
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $res) {
        $report_list .= "【発生日】" . $res["indate"] . " 【場所】" . $res["location"] . " 【内容】" . $res["description"] . "\n";
    }
}

// 2. ChatGPT APIで分析を実行
$api_key = get_chatgpt_api_key(); // funcs.phpから読み込み
$prompt = "以下の交通通報データを分析し、地域安全レポートを作成してください。\n【指示】\n1. 頻発している違反の種類をまとめてください。\n2. 危険が集中している場所を特定してください。\n3. 住民ができる対策を3つ提案してください。\n\nデータ：\n" . $report_list;

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $api_key"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "model" => "gpt-4o-mini", // 速度と精度のバランスが良い最新モデル
    "messages" => [
        ["role" => "system", "content" => "あなたは地域の安全担当者です。データに基づき、箇条書きを活用してプロフェッショナルかつ簡潔に回答してください。"],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.7
]));

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

// 回答の取得
if (isset($result['choices'][0]['message']['content'])) {
    $ai_analysis = $result['choices'][0]['message']['content'];
} else {
    $error_msg = isset($result['error']['message']) ? $result['error']['message'] : 'APIエラーが発生しました。';
    $ai_analysis = "分析に失敗しました。理由: " . $error_msg;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>AI安全分析レポート | まちの目</title>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%) !important;
            background-attachment: fixed;
            color: white !important;
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 40px 20px;
            display: flex; justify-content: center;
        }
        .analysis-container {
            max-width: 800px; width: 100%;
            background: rgba(255, 255, 255, 0.98);
            color: #0f172a; border-radius: 24px;
            padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .report-header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px; margin-bottom: 25px;
        }
        .analysis-content {
            line-height: 1.8; font-size: 1.05rem;
        }
        .btn-group {
            margin-top: 35px; display: flex; gap: 15px; align-items: center;
        }
        .btn-pdf {
            background: #2563eb; color: white; padding: 12px 25px;
            border-radius: 10px; text-decoration: none; font-weight: bold;
            border: none; cursor: pointer; transition: 0.3s;
        }
        .btn-pdf:hover { background: #1d4ed8; transform: translateY(-2px); }
        .back-link { color: #64748b; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="analysis-container">
    <div class="report-header">
        <h1 style="margin:0; color:#1e293b; font-size:1.8rem;">✨ 地域安全分析レポート</h1>
        <p style="color:#64748b; margin:5px 0 0;">AI Agent By "Machinome" - <?= date('Y-m-d H:i') ?></p>
    </div>

    <div class="analysis-content">
        <?= nl2br(h($ai_analysis)) ?>
    </div>

    <div class="btn-group">
        <form action="export_pdf.php" method="POST" style="margin:0;">
            <input type="hidden" name="analysis_data" value="<?= h($ai_analysis) ?>">
            <button type="submit" class="btn-pdf">📄 PDFとして保存する</button>
        </form>
        <a href="select.php" class="back-link">管理画面に戻る</a>
    </div>
</div>

</body>
</html>