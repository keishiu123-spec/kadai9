<?php
session_start();
require_once('funcs.php');
sschk(); // 管理者権限チェック
require_once('tcpdf/tcpdf.php');

// 1. summary_report.php から送られてきた分析結果を取得
$ai_analysis = isset($_POST['analysis_data']) ? $_POST['analysis_data'] : 'データがありません。';

// 2. PDFの生成
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15); // 余白設定
$pdf->AddPage();

// 日本語フォントの設定（kozminproregular: 標準で日本語が通るフォント）
$pdf->SetFont('kozminproregular', '', 11);

// 3. レポートのHTML構造（デザインを整える）
$html = '
    <div style="text-align:center;">
        <h1 style="color:#2563eb; border-bottom: 2px solid #2563eb; padding-bottom:10px;">地域安全分析レポート（AI生成）</h1>
        <p style="text-align:right; color:#64748b;">作成日: ' . date('Y-m-d H:i') . '</p>
    </div>
    
    <div style="margin-top:20px;">
        <h3 style="background-color:#eff6ff; color:#1e40af; padding:8px;">■ AIによる分析結果と対策提言</h3>
        <div style="padding:15px; border:1px solid #e2e8f0; line-height:1.8; background-color:#ffffff;">
            ' . nl2br(htmlspecialchars($ai_analysis)) . '
        </div>
    </div>

    <div style="margin-top:30px; text-align:center; color:#94a3b8; font-size:9pt;">
        <p>※本レポートは「まちの目」システムに蓄積されたデータを元にAIが自動生成したものです。</p>
    </div>
';

// PDFへの書き込み
$pdf->writeHTML($html, true, false, true, false, '');

// 4. ファイルをブラウザへ出力（D:強制ダウンロード, I:ブラウザで表示）
$pdf->Output('Safety_Report_' . date('Ymd_Hi') . '.pdf', 'D');