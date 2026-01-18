<?php
session_start();
session_start();
include("funcs.php");
sschk();

$pdo = db_conn();

// 1. データの取得（最新のレポート順）
$sql = "SELECT * FROM gs_report_table ORDER BY indate DESC";
$stmt = $pdo->prepare($sql);
$status = $stmt->execute();

if($status==false) {
  sql_error($stmt);
}

$values = $stmt->fetchAll(PDO::FETCH_ASSOC);
$json = json_encode($values, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>まちの目 | 危険箇所マッピング</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* index.php と共通の背景グラデーション */
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%) !important;
            background-attachment: fixed;
            color: white !important;
            margin: 0;
            font-family: 'Inter', -apple-system, sans-serif;
        }

        /* ヘッダーのスリム化と背景への統合 */
        header {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
            min-height: auto !important;
            padding: 12px 0 !important;
        }
        .navbar-brand {
            color: #fff !important;
            font-weight: 800;
            font-size: 1.2rem !important;
            letter-spacing: 0.1em;
            text-decoration: none;
        }

        .container-main {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* マップセクション */
        .map-header {
            margin: 20px 0;
        }
        .map-title {
            font-size: 1.6rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .map-title::before {
            content: "";
            width: 5px;
            height: 24px;
            background: #2563eb;
            display: inline-block;
            border-radius: 3px;
        }

        #myMap { 
            width: 100%; 
            height: 700px; /* 下に大きく広げる */
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            margin-bottom: 50px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* レポートカード（視認性重視） */
        .report-card-modern {
            background: rgba(255, 255, 255, 0.98) !important;
            border-radius: 16px !important;
            padding: 24px !important;
            margin-bottom: 20px !important;
            display: flex;
            align-items: center;
            color: #0f172a !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .report-card-modern:hover {
            transform: translateY(-3px);
        }

        .tag-status {
            background: #2563eb;
            color: white;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }
        .info-date {
            margin-left: 15px;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
        }
    
        /* 全体フェードインアニメーション */
        body {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0; /* 初期状態は透明 */
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body>

<header>
    <nav class="navbar">
        <div class="container-main">
            <a class="navbar-brand" href="index.php">まちの目</a>
        </div>
    </nav>
</header>




<div class="container-main">
    <div class="map-header">
        <div class="map-title">「まちの目」安全状況ダッシュボード</div>
        <p style="color: #cbd5e1; margin-top: 8px;">一人ひとりの報告が繋がり、抑止力（警戒資産）へと変わります。</p>
    </div>
    
    <div id="myMap"></div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
        <h2 style="font-weight:800; color:#fff; margin:0;">最新の安全レポート</h2>
    </div>

    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
    <a href="csv_download.php" class="btn" style="background-color: #10b981; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">CSVダウンロード</a>
    <a href="summary_report.php" class="btn" style="background-color: #8b5cf6; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        ✨ AI一括レポート分析</a>
    <a href="index.php" class="btn" style="background-color: #2563eb; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 700; font-size: 0.9rem;">新規レポートを作成</a>
</div>

    
    <div class="report-list">
        <?php foreach($values as $v){ ?>
            <div class="report-card-modern">
                <div style="flex:1;">
                    <div style="margin-bottom:12px;">
                        <span class="tag-status">警戒資産</span>
                        <span class="info-date"><?= h($v['indate']) ?> 報告</span>
                        <span style="margin-left:15px; color:#475569; font-weight:700;">車両番号: <?= h($v['car_number']) ?></span>
                    </div>
                    <div style="font-size: 1.3rem; font-weight: 800; color:#0f172a; margin-bottom:8px;">📍 <?= h($v['location']) ?></div>
                    <div style="color:#334155; line-height:1.6;"><?= nl2br(h($v['description'])) ?></div>
                </div>
                <div style="margin-left:30px; display:flex; gap:12px;">
                    <a href="detail.php?id=<?= h($v['id']) ?>" class="btn-primary" style="text-decoration:none; padding: 10px 20px !important; font-size: 0.85rem; border-radius: 8px;">編集</a>
                    <a href="delete.php?id=<?= h($v['id']) ?>" class="btn-danger" style="text-decoration:none; padding: 10px 20px !important; font-size: 0.85rem; border-radius: 8px;" onclick="return confirm('本当に削除しますか？')">削除</a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= get_google_api_key() ?>&libraries=places,visualization"></script>
<script>
    const data = JSON.parse('<?= $json ?>');

    function initMap() {
    const defaultPos = {lat: 35.681236, lng: 139.767125};
    const centerPos = data.length > 0 ? {lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lng)} : defaultPos;

    // 1. 地図の基本設定
    const map = new google.maps.Map(document.getElementById('myMap'), {
        zoom: 15,
        center: centerPos,
        mapTypeId: 'roadmap'
    });

    // 2. ヒートマップ用のデータ作成（緯度・経度の配列）
    const heatData = data.map(v => {
        return new google.maps.LatLng(parseFloat(v.lat), parseFloat(v.lng));
    });

    // 3. ヒートマップレイヤーを地図に重ねる
    const heatmap = new google.maps.visualization.HeatmapLayer({
        data: heatData,
        map: map,
        radius: 50,    // 密度の広がり具合（UXに合わせて調整）
        opacity: 0.8   // 透明度
    });

    // 4. 【UX向上】クリックして詳細も見れるようにピンも小さく表示
    data.forEach(v => {
        if(v.lat && v.lng){
            const marker = new google.maps.Marker({
                position: {lat: parseFloat(v.lat), lng: parseFloat(v.lng)},
                map: map,
                // 小さな円形のアイコンにしてヒートマップを邪魔しないようにする
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 4,
                    fillColor: '#2563eb',
                    fillOpacity: 0.7,
                    strokeWeight: 1,
                    strokeColor: 'white'
                }
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `<div style="color:#333;"><strong>📍 ${v.location}</strong><br>${v.description}</div>`
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    });
    }
    window.onload = initMap;
</script>
</body>
</html>