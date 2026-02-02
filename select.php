<?php
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
    /* --- 全体：背景設定 --- */
    body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%) !important;
        background-attachment: fixed;
        color: white !important;
        margin: 0;
        font-family: 'Inter', -apple-system, sans-serif;
        animation: fadeIn 0.8s ease-out forwards;
        opacity: 0;
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    /* --- ヘッダー：完全な黒透過 --- */
    header {
        width: 100% !important;
        background: rgba(0, 0, 0, 0.95) !important;
        backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        position: sticky;
        top: 0;
        z-index: 9999;
    }
    .navbar {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 10px 0 !important;
    }
    .navbar-brand { color: #fff !important; font-weight: 800; font-size: 1.1rem; text-decoration: none; }

    /* --- マップ：青い背景（余白）を消す --- */
    #myMap { 
        width: 100% !important;
        height: 450px !important; 
        border-radius: 20px;
        margin-bottom: 25px;
        border: 1px solid rgba(255,255,255,0.1);
        background-color: #000 !important;
        padding: 0 !important;
    }
    #myMap img { max-width: none !important; }

    /* --- レポートリスト：超コンパクト・スクロール --- */
    .report-list {
        max-height: 350px !important; /* 縦幅を固定 */
        overflow-y: auto;
        background: rgba(15, 23, 42, 0.4);
        border-radius: 12px;
        padding: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .report-card-modern {
        background: rgba(255, 255, 255, 0.95) !important;
        padding: 6px 15px !important;
        margin-bottom: 4px !important;
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        min-height: 40px !important; /* ボタンがあるため少し高さを確保 */
        border-radius: 8px !important;
        color: #0f172a !important;
    }

    .report-info-main {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        overflow: hidden;
        font-size: 0.8rem !important;
    }

    .location-text { font-weight: 800; min-width: 140px; white-space: nowrap; }
    .description-text { color: #475569; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
    .tag-status { background: #2563eb; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; font-weight: bold; }
    .info-date { color: #64748b; font-size: 0.7rem; white-space: nowrap; margin-left: 10px; }

    /* 管理用ボタンのスリム化 */
    .btn-admin-small {
        padding: 4px 10px !important;
        font-size: 0.7rem !important;
        border-radius: 4px !important;
        text-decoration: none !important;
        font-weight: bold;
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
            <div class="report-info-main">
                <span class="tag-status">警戒資産</span>
                <div class="location-text">📍 <?= h($v['location']) ?></div>
                <div class="description-text"><?= h($v['description']) ?></div>
                <span class="info-date"><?= h(date('m/d H:i', strtotime($v['indate']))) ?></span>
            </div>
            
            <div style="display: flex; gap: 6px; margin-left: 15px;">
                <a href="detail.php?id=<?= h($v['id']) ?>" class="btn-primary btn-admin-small">編集</a>
                <a href="delete.php?id=<?= h($v['id']) ?>" class="btn-danger btn-admin-small" 
                   style="background-color: #ef4444;" onclick="return confirm('本当に削除しますか？')">削除</a>
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