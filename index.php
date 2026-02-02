<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>まちの目 | 地域安全プラットフォーム</title>
  <link rel="stylesheet" href="css/style.css">
  <?php require_once('funcs.php'); ?>
  <script src="https://maps.googleapis.com/maps/api/js?key=<?= get_google_api_key() ?>&libraries=places"></script>
<style>
    /* 1. 全体：動画を見せるためにbodyを透明化 */
    html, body {
        background-color: #020617 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        overflow-x: hidden;
    }

    /* 2. ヘッダー：白背景・角丸・コンテナの余白を徹底排除 */
    header {
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
    }
    .navbar, .navbar-default, .container, .container-fluid {
        background-color: #020617 !important;
        background-image: none !important;
        border: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        min-height: 40px !important;
        box-shadow: none !important;
    }
    .navbar-brand {
        color: #60a5fa !important;
        font-weight: 800;
        font-size: 1rem !important;
        line-height: 40px !important;
        height: 40px !important;
        padding: 0 20px !important;
        margin: 0 !important;
        display: block;
    }

    /* 3. 動画背景 */
    .video-background {
        position: fixed;
        top: 0; left: 0; width: 100vw; height: 100vh;
        z-index: -2;
    }
    .video-background video {
        width: 100%; height: 100%; object-fit: cover;
    }
    .video-overlay {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(2, 6, 23, 0.6);
        z-index: -1;
    }

    /* 4. コンテンツ・フォーム（左側にマージンを追加して右に寄せる） */
    .hero-section { 
        background: transparent !important; 
        padding: 80px 0 40px; 
    }
    
    /* 文章全体を囲むエリアに左余白を設定 */
    .hero-section .container {
        margin-left: 8% !important; /* この数字を大きくするとさらに右へ寄ります */
        max-width: 800px;
    }

    .hero-section h1, .hero-section p, .hero-section span {
        color: #ffffff !important;
        text-shadow: 2px 2px 8px rgba(0,0,0,0.9);
    }

    .glass-card {
        background: #020617 !important;
        border: 1px solid #1e40af !important;
        padding: 30px !important;
        border-radius: 12px !important;
        margin-left: 0 !important; /* フォームは元の位置を維持 */
    }
    
    .glass-card label, .glass-card h3, .glass-card p {
        color: #ffffff !important;
    }

    /* 入力エリア：白背景・黒文字（実用性） */
    input[type="text"], input[type="file"], textarea {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: 2px solid #3b82f6 !important;
        border-radius: 6px !important;
    }

    body { animation: fadeIn 0.8s ease-out forwards; opacity: 0; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }


    /* スマホ画面（横幅768px以下）用の調整 */
@media screen and (max-width: 768px) {
    /* 1. 全体：左右の不要なマージンをリセットして中央寄せを徹底 */
    .hero-section .container {
        margin-left: auto !important;
        margin-right: auto !important;
        width: 100% !important;
        padding: 0 15px !important;
    }

    /* 2. ガラスカード：スマホの横幅に合わせて中央配置 */
    .glass-card {
        margin-left: auto !important;  /* 強制的に中央へ */
        margin-right: auto !important; /* 強制的に中央へ */
        padding: 25px 20px !important;
        width: 90% !important;         /* 画面端に密着しないよう90%程度に設定 */
        max-width: none !important;
        box-sizing: border-box;        /* パディングによるはみ出しを防止 */
    }

    /* 3. タイトル等の文字サイズ調整 */
    .hero-section h1 {
        font-size: 2.0rem !important; 
        line-height: 1.2 !important;
    }
}


</style>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>


<body>

<div class="video-background">
        <video autoplay loop muted playsinline>
            <source src="road_loop_video.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>



<header>
    <nav class="navbar" style="display: flex; justify-content: flex-end; align-items: center; padding: 15px 30px; gap: 25px;">
        <a class="nav-link-custom" href="view.php">📊 履歴を見る</a>
        <a class="nav-link-custom" href="login.php">🔑 管理者画面</a>
        <a class="nav-link-custom" href="risk_check.php">🚦 道路リスク診断</a>
    </nav>
</header>


<div class="hero-section">
    <div class="container" style="background:transparent !important; border:none !important; box-shadow:none !important;">
        <span class="app-brand-title">地域安全プラットフォーム「まちの目」</span>
        <h1 class="text-gradient" style="font-size: 3.5em; font-weight: 800; letter-spacing: -0.05em; margin-bottom: 20px; line-height:1.2;">
            あなたの「気づき」が、<br>街の防犯カメラになる。
        </h1>
        <p style="color: #cbd5e1; font-size: 1.2em; max-width: 600px; margin-bottom: 40px;">
        街に潜む危険は、警察にも防犯カメラにもすべては見えていません。<br>
        ですが、毎日そこを歩く親たちの目には、はっきりと映っています。<br>
        「まちの目」は、あなたの小さな違和感をデータとして繋ぎ、抑止力に変えるプラットフォームです。 <br>
        ヒヤリハットを可視化し、行政と連携することで、事故が起きる前に街を書き換える。<br>
        テクノロジーで、あの子の通学路を世界で一番安全な場所にします。
        </p>

    </div>
</div>

<div class="glass-card" style="max-width: 500px; margin: 0 auto; backdrop-filter: blur(15px); background: rgba(255,255,255,0.05) !important;">
  <form method="POST" action="insert.php" id="reportForm" enctype="multipart/form-data">
    
    <div class="form-group">
        <button type="button" onclick="getCurrentLocation()" class="btn-primary" style="margin-bottom:15px;">
            📍 今この場所を取得する
        </button>
        
        <label style="color:white; font-size:0.8rem; display:block; margin-bottom:5px; text-align:left;">場所・住所（修正・手入力可）</label>
        <input type="text" name="location" id="location" placeholder="住所を入力、またはGPS取得" style="width:100%;">
    </div>

    <div style="margin: 20px 0;">
        <p style="font-size:0.8rem; color:#94a3b8; margin-bottom:10px;">状況を選択</p>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <?php $tags = ['一時不停止','危険運転','信号無視','スピード過剰']; 
            foreach($tags as $tag): ?>
                <button type="button" class="btn-tag" onclick="addTag('<?= $tag ?>')"><?= $tag ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <textarea name="description" placeholder="詳細メモ（任意）" style="width:100%; height:80px; background:rgba(255,255,255,0.1) !important; color:white !important; border:1px solid #334155;"></textarea>
    
    <label class="btn-secondary" style="display:block; text-align:center; margin-top:15px; cursor:pointer;">
        📷 写真を添える
        <input type="file" name="img" accept="image/*" style="display:none;">
    </label>

    <button type="button" onclick="getCoordsAndSubmit()" class="btn-primary" style="width:100%; margin-top:20px; background:linear-gradient(to right, #60a5fa, #2563eb);">
        送信を完了する
    </button>
    
    <input type="hidden" name="lat" id="lat"><input type="hidden" name="lng" id="lng">
  </form>
</div>

<style>
  /* 既存のスタイルをこれに置き換え */
.hero-section {
    display: flex;
    flex-direction: column;
    align-items: center; /* 中央に寄せる */
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
    text-align: center;
}

.glass-card {
    background: rgba(15, 23, 42, 0.7) !important; /* 少し暗くして文字を読みやすく */
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    padding: 40px !important;
    border-radius: 24px !important;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    width: 100%;
    max-width: 480px; /* 横幅を絞ってスマートに */
    margin-top: 40px;
}

/* 下にあった古いフォームを強制非表示にする */
#reportFormContainer, .container.glass-card:not(:first-of-type) {
    display: none !important;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    border: none !important;
    border-radius: 12px !important;
    padding: 16px 24px !important;
    font-weight: 800 !important;
    letter-spacing: 0.05em !important;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3) !important;
    transition: all 0.3s ease !important;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4) !important;
}

/* 状況選択（タグ）ボタンをアプリ風に */
.btn-tag {
    background: rgba(255, 255, 255, 0.1) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    padding: 10px 20px !important;
    border-radius: 50px !important; /* 丸みを持たせる */
    font-size: 0.9rem !important;
    font-weight: 600 !important;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-tag:active {
    background: #3b82f6 !important;
    transform: scale(0.95);
}

.nav-link-custom {
    color: #ffffff !important;         /* 強制的に白文字にする */
    font-weight: 700 !important;      /* 太字にする */
    font-size: 1.0rem !important;     /* 視認性を上げるため少し大きく */
    text-decoration: none !important; /* 下線を消す */
    letter-spacing: 0.05em;           /* 文字の間隔を広げて高級感を出す */
    transition: 0.3s;
    opacity: 0.9;                     /* 背景に馴染むよう少しだけ透かす */
}

.nav-link-custom:hover {
    color: #60a5fa !important;         /* ホバー時だけ青く光らせる */
    opacity: 1;
    transform: translateY(-1px);
}

</style>




<script>
function getCoordsAndSubmit() {
    const address = document.getElementById('location').value;
    if (!address) { alert("場所を入力してください"); return; }
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: address }, (results, status) => {
        if (status === 'OK') {
            document.getElementById('lat').value = results[0].geometry.location.lat();
            document.getElementById('lng').value = results[0].geometry.location.lng();
            document.getElementById('reportForm').submit();
        } else { alert('場所の特定に失敗しました。正しい住所を入力してください。'); }
    });
}
function getCurrentLocation() {
    if (!navigator.geolocation) { alert("位置情報に対応していません"); return; }
    
    const locInput = document.getElementById('location');
    locInput.value = "取得中..."; // ユーザーへのフィードバック

    navigator.geolocation.getCurrentPosition((position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        
        // 1. 座標を隠しフィールドにセット
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;

        // 2. 逆ジオコーディング（座標 → 住所文字列）
        const geocoder = new google.maps.Geocoder();
        const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };

        geocoder.geocode({ location: latlng }, (results, status) => {
            if (status === "OK") {
                if (results[0]) {
                    locInput.value = results[0].formatted_address; // 住所を欄に表示
                } else {
                    locInput.value = "住所が見つかりませんでした";
                }
            } else {
                locInput.value = "エラー: " + status;
            }
        });
    }, (error) => {
        alert("位置情報の取得に失敗しました。");
        locInput.value = "";
    });
}

function addTag(tagName) {
    const textarea = document.querySelector('textarea[name="description"]');
    // すでに文字がある場合は改行して追加、なければそのまま追加
    if (textarea.value) {
        textarea.value += "\n" + tagName + "：";
    } else {
        textarea.value = tagName + "：";
    }
    // 入力欄にフォーカスを当てる（UX向上）
    textarea.focus();
}

</script>
</body>

</html>

<?php
/**
 * ============================================================
 * プロダクト名：地域安全プラットフォーム「まちの目」
 * ============================================================
 * WHO  : 毎朝「無事に帰ってきて」と祈りながら子供を送り出す保護者
 * 　　　　通学路の安全対策をしたいが「根拠となるデータ」がなくて動けない自治体・学校関係者・警察署
 * 　　　　地域の安全を守りたいと願うすべての市民
 * WHAT : 子供と共により安全な街に住むことができる
 * HOW  : 警察の目が届かない場所でのスピード違反/一時不停止/歩行横断妨害等の違反を可視化し、データとして蓄積する。
 * 　　　　1. ヒヤリハットのリアルタイム可視化（デジタル地図生成）と警戒資産の蓄積
 *        2. 蓄積されたヒートマップを「要望書」として自動出力し、取り締まりの優先度を決定
 *        3. 自動カメラの設置や交通違反予測の基盤へ
 * COPY : あなたの「気づき」が、街の防犯カメラになる。
 * BODY : 街に潜む危険は、警察にも防犯カメラにもすべては見えていません。
 * 　　　　ですが、毎日そこを歩く親たちの目には、はっきりと映っています。
 * 　　　　「まちの目」は、あなたの小さな違和感をデータとして繋ぎ、抑止力に変えるプラットフォームです。 
 *　　　　　ヒヤリハットを可視化し、行政と連携することで、事故が起きる前に街を書き換える。
 * 　　　テクノロジーで、あの子の通学路を世界で一番安全な場所にします。
 * ============================================================
 */

?>