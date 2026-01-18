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
  <nav class="navbar navbar-default" style="border:none;">
    <div class="container">
      <div class="navbar-header"><a class="navbar-brand" href="select.php">まちの目</a></div>
    </div>
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
        <div style="display: flex; gap: 15px;">
            <button type="button" onclick="document.getElementById('reportForm').scrollIntoView({behavior: 'smooth'})" class="btn-primary" style="padding: 15px 30px !important;">レポートを作成する</button>
            <a href="select.php" class="btn-primary" style="background:#0f172a !important; border:1px solid #334155 !important; text-decoration:none; padding: 15px 30px !important;">履歴を閲覧</a>
        </div>
    </div>
</div>

<div class="container glass-card" id="reportFormContainer" style="position:relative; z-index:10;">
  <form method="POST" action="insert.php" id="reportForm" enctype="multipart/form-data">
    <fieldset style="border:none;">
      <div style="border-left: 4px solid #2563eb; padding-left: 15px; margin-bottom: 30px;">
          <h3 style="margin:0; font-weight: 800; color: #0f172a;">新規通報フォーム</h3>
          <p style="color: #64748b; margin:0;">周囲の安全のため、正確な情報入力にご協力ください。</p>
      </div>
      
    <div class="form-group" style="margin-bottom: 25px;">
        <label style="color:#1e293b; font-size:0.95em; font-weight:700;">発生場所（住所）</label>
        <div style="display:flex; gap:10px;">
            <input type="text" name="location" id="location" placeholder="住所を入力、またはGPSボタンで現在地を取得してください" style="flex:1;">
            <button type="button" onclick="getCurrentLocation()" style="background:#0f172a !important;">📍 GPS取得</button>
        </div>
    </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="color:#1e293b; font-size:0.95em; font-weight:700;">現場の写真（任意）</label>
            <input type="file" name="img" accept="image/*" capture="environment" style="background:white; width:100%; padding:10px; border-radius:8px; border:1px solid #cbd5e1;">
        </div>

    <div class="form-group" style="margin-top: 20px;">
        <label style="color:#1e293b; font-size:0.95em; font-weight:700;">状況のクイック選択</label>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
            <button type="button" class="btn-tag" onclick="addTag('一時不停止')">一時不停止</button>
            <button type="button" class="btn-tag" onclick="addTag('危険運転')">危険運転</button>
            <button type="button" class="btn-tag" onclick="addTag('スピード出し過ぎ')">スピード過剰</button>
            <button type="button" class="btn-tag" onclick="addTag('横断妨害')">横断妨害</button>
            <button type="button" class="btn-tag" onclick="addTag('信号無視')">信号無視</button>
        </div>
    </div>

<style>
    /* クイック選択ボタンの専用デザイン */
    .btn-tag {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #cbd5e1;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-tag:active {
        background: #2563eb;
        color: white;
        transform: scale(0.95);
    }
</style>


      
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
          <div class="form-group">
            <label style="color:#1e293b; font-size:0.95em; font-weight:700;">車両番号（不明な場合は空欄）</label>
            <input type="text" name="car_number" placeholder="例：品川500 あ 1234">
        </div>
        <div class="form-group">
            <label style="color:#1e293b; font-size:0.95em; font-weight:700;">通報日時</label>
            <input type="text" name="indate" value="<?= date('Y-m-d H:i') ?>" style="background:#fff; cursor:text;">
        </div>
      </div>

      
      <div class="form-group" style="margin-top: 20px;">
          <label style="color:#1e293b; font-size:0.95em; font-weight:700;">状況の詳細</label>
          <textarea name="description" rows="5" placeholder="どのような危険や不審な点を感じたか、具体的に入力してください（例：何度も同じ場所を徘徊している、蛇行運転をしている等）"></textarea>
      </div>
      
      <input type="hidden" name="lat" id="lat">
      <input type="hidden" name="lng" id="lng">
      
      <input type="button" value="この内容で地域の安全を守る（送信）" onclick="getCoordsAndSubmit()" style="width:100%; padding:20px !important; font-size:1.1em; font-weight:800; letter-spacing:0.05em; background: linear-gradient(to right, #2563eb, #1d4ed8) !important; border-radius:12px !important;">
    </fieldset>
  </form>
</div>

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
    navigator.geolocation.getCurrentPosition((position) => {
        document.getElementById('lat').value = position.coords.latitude;
        document.getElementById('lng').value = position.coords.longitude;
        document.getElementById('location').value = "GPSによる現在地取得完了";
        alert("座標を取得しました。");
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