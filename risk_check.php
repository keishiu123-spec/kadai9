<?php
session_start();
require_once('funcs.php');
sschk(); // ログインチェック

$analysis_result = null;
$error_msg = "";

// 画像アップロード・AI解析処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['risk_image'])) {
    if ($_FILES['risk_image']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['risk_image']['tmp_name'];

        // --- 【強化ポイント】自動リサイズ処理 ---
        list($w, $h) = getimagesize($tmp_name);
        $max_size = 1200; // AI解析に十分な解像度
        $base_img = imagecreatefromany($tmp_name); // funcs.phpの追加関数を使用
        
        if ($base_img) {
            // アスペクト比を維持してリサイズ計算
            if ($w > $max_size || $h > $max_size) {
                $ratio = $max_size / ($w > $h ? $w : $h);
                $new_w = (int)($w * $ratio);
                $new_h = (int)($h * $ratio);
            } else {
                $new_w = $w;
                $new_h = $h;
            }

            $canvas = imagecreatetruecolor($new_w, $new_h);
            imagecopyresampled($canvas, $base_img, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
            
            // メモリ上でBase64化（一時ファイルを作らず高速化）
            ob_start();
            imagejpeg($canvas, null, 80); // 画質80%で圧縮
            $img_raw = ob_get_clean();
            $base64_image = "data:image/jpeg;base64," . base64_encode($img_raw);
            
            imagedestroy($base_img);
            imagedestroy($canvas);
        } else {
            $error_msg = "画像の読み込みに失敗しました。";
        }

        if (!$error_msg) {
            $api_key = get_chatgpt_api_key();

            $system_prompt = "
            あなたは物理学者であり、交通安全工学のトップエキスパートです。
            アップロードされた道路画像を分析し、以下の『Invisible Risk Visualizer』ロジックに基づいて厳密にリスク診断を行ってください。
            出力は必ずJSON形式のみで行ってください。

            ## 1. 画像解析レイヤー
            - 遮蔽オブジェクト、道路幾何構造、錯視トリガーを検出。
            ## 2. リスク判定ロジック
            - ロジック①：React Limit (飛び出し不可避判定)
            - ロジック②：Illusion Logic (脳の錯覚判定)
            - ロジック③：Short-cut Logic (抜け道判定)
            ## 3. 総合スコア・ランク
            - S/A/Bランクで判定。
            ## 4. 対策レコメンド
            - ハンプ設置、ライジングボラード等を提案。

            出力はJSONのみ:
            {
                \"total_score\": 数値,
                \"rank\": \"S/A/B\",
                \"logic_1_eval\": \"分析結果\",
                \"logic_2_eval\": \"分析結果\",
                \"logic_3_eval\": \"分析結果\",
                \"danger_elements\": [\"要素\"],
                \"recommendations\": [{\"title\": \"対策\", \"desc\": \"詳細\"}]
            }
            ";

            $data = [
                "model" => "gpt-4o",
                "messages" => [
                    ["role" => "system", "content" => $system_prompt],
                    ["role" => "user", "content" => [
                        ["type" => "text", "text" => "この道路のリスクを診断してください。"],
                        ["type" => "image_url", "image_url" => ["url" => $base64_image]]
                    ]]
                ],
                "response_format" => ["type" => "json_object"]
            ];

            $ch = curl_init("https://api.openai.com/v1/chat/completions");
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $api_key"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            
            // --- 【強化ポイント】タイムアウトを90秒に延長 ---
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
            
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error_msg = 'API通信エラー: ' . curl_error($ch);
            } else {
                $result = json_decode($response, true);
                if (isset($result['choices'][0]['message']['content'])) {
                    $analysis_result = json_decode($result['choices'][0]['message']['content'], true);
                } else {
                    $error_msg = "AI解析に失敗しました。";
                }
            }
            curl_close($ch);
        }
    } else {
        $error_msg = "ファイルアップロードエラーが発生しました。";
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Invisible Risk Visualizer | まちの目</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        /* レイアウト：既存のデザインを完全維持 */
        body {
            background-color: #050510;
            background-image: linear-gradient(135deg, rgba(5, 5, 15, 0.5) 0%, rgba(10, 20, 50, 0.3) 100%), url('suushiki.png'); 
            background-size: cover; background-position: center; background-attachment: fixed;
            background-blend-mode: luminosity; color: white; font-family: 'Inter', sans-serif; margin: 0;
        }
        .info-section, .glass-panel, .score-card {
            background: rgba(10, 20, 45, 0.95) !important;
            backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; margin-bottom: 30px;
            border: 1px solid rgba(0, 243, 255, 0.3); box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
        }
        .mech-card, .logic-box {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 20px;
        }
        header {
            background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px); padding: 15px 20px;
            position: sticky; top: 0; z-index: 100; border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        .info-title { font-size: 1.5rem; font-weight: 900; margin-bottom: 20px; border-left: 5px solid #3b82f6; padding-left: 15px; display: flex; align-items: center; gap: 10px; }
        .mechanism-grid { display: grid; grid-template-columns: 1fr; gap: 20px; margin-bottom: 30px; }
        @media(min-width: 768px) { .mechanism-grid { grid-template-columns: 1fr 1fr; } }
        .btn-upload { background: linear-gradient(45deg, #3b82f6, #8b5cf6); border: none; padding: 15px 40px; color: white; font-weight: bold; border-radius: 50px; cursor: pointer; font-size: 1rem; margin-top: 20px; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.5); }
        .score-circle { width: 100px; height: 100px; border-radius: 50%; background: #0f172a; color: white; display: flex; justify-content: center; align-items: center; font-size: 2rem; font-weight: 800; border: 5px solid #3b82f6; }
        #loading { display: none; text-align: center; margin-top: 30px; }
        .spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.3); border-top: 4px solid #fff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<header>
    <a href="index.php" style="color:#fff; text-decoration:none; font-weight:800;">まちの目</a>
    <a href="index.php" style="color:#cbd5e1; text-decoration:none;">戻る</a>
</header>

<div class="container">
    <div style="text-align:center; margin-bottom:30px; margin-top:20px;">
        <h1 style="font-weight:900; margin-bottom:10px; font-size: 2.5rem;">Invisible Risk Visualizer</h1>
        <p style="color:#cbd5e1;">「見えているはず」の過信を、AIが可視化します。</p>
    </div>

    <div class="info-section">
        <div class="info-title">⚠️ なぜ、通学路で事故は起きるのか？</div>
        <div class="mechanism-grid">
            <div class="mech-card">
                <div style="font-weight:bold; color:#60a5fa; margin-bottom:10px;">👁️ 視覚と認識のズレ</div>
                <p style="font-size:0.9rem; color:#cbd5e1;">Aピラーの死角や錯視により、子供の姿が脳内で「透明化」される瞬間があります。</p>
            </div>
            <div class="mech-card">
                <div style="font-weight:bold; color:#60a5fa; margin-bottom:10px;">🛑 物理的限界</div>
                <p style="font-size:0.9rem; color:#cbd5e1;">身長110cmの子供はブロック塀に隠れます。時速30kmの車は即座に止まれません。</p>
            </div>
        </div>
    </div>

    <div class="glass-panel" style="text-align:center; border: 2px dashed rgba(255,255,255,0.3);">
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="document.getElementById('loading').style.display='block';">
            <div style="font-size:3rem; margin-bottom:10px;">📸</div>
            <p>道路の写真をドラッグ＆ドロップ、または選択</p>
            <input type="file" name="risk_image" accept="image/*" required style="display:none;" id="fileInput">
            <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()">画像を選択</button>
            <br><br>
            <button type="submit" class="btn-upload" style="background: #2563eb;">AI解析開始</button>
        </form>
    </div>

    <div id="loading">
        <div class="spinner"></div>
        <p>画像を物理シミュレーション中...</p>
    </div>

    <?php if ($analysis_result): ?>
        <div class="score-card">
            <div style="display:flex; align-items:center; gap:20px;">
                <div class="score-circle"><?= h($analysis_result['total_score']) ?></div>
                <h2>RANK: <?= h($analysis_result['rank']) ?></h2>
            </div>
            <p style="margin-top:20px;"><strong>分析結果:</strong> <?= h($analysis_result['logic_1_eval']) ?></p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>