<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>まちの目 | 地域安全プラットフォーム</title>
  <link rel="stylesheet" href="css/style.css">
  <?php require_once('funcs.php'); ?>
  <script src="https://maps.googleapis.com/maps/api/js?key=<?= get_google_api_key() ?>&libraries=places"></script>
  <style>
    .hero-section {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
        color: white;
        padding: 100px 0; /* 少し広めに確保 */
        margin-bottom: -40px;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
    .text-gradient {
        background: linear-gradient(to right, #fff, #bfdbfe);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    /* アプリ名強調用のスタイル */
    .app-brand-title {
        font-size: 1.2em;
        font-weight: 700;
        letter-spacing: 0.2em;
        color: #60a5fa;
        margin-bottom: 10px;
        display: block;
    }
  </style>
</head>
<body>

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
            一人ひとりの報告をデータで繋ぎ、事故を未然に防ぐ。
            「まちの目」は、テクノロジーで一歩先の安全をデザインします。
        </p>
        <div style="display: flex; gap: 15px;">
            <button type="button" onclick="document.getElementById('reportForm').scrollIntoView({behavior: 'smooth'})" class="btn-primary" style="padding: 15px 30px !important;">レポートを作成する</button>
            <a href="select.php" class="btn-primary" style="background:#0f172a !important; border:1px solid #334155 !important; text-decoration:none; padding: 15px 30px !important;">履歴を閲覧</a>
        </div>
    </div>
</div>

<div class="container glass-card" id="reportFormContainer" style="position:relative; z-index:10;">
  <form method="POST" action="insert.php" id="reportForm">
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
      
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
          <div class="form-group">
              <label style="color:#1e293b; font-size:0.95em; font-weight:700;">車両番号（不明な場合は空欄）</label>
              <input type="text" name="car_number" placeholder="例：品川500 あ 1234">
          </div>
          <div class="form-group">
              <label style="color:#1e293b; font-size:0.95em; font-weight:700;">通報日時</label>
              <input type="text" value="<?= date('Y-m-d H:i') ?>" disabled style="background:#f1f5f9; cursor:not-allowed;">
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