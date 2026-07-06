<?php
require_once 'config.php';
requireMenuPermission('cost-ranking');

?>
<!DOCTYPE html>
<html lang="gu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SEO Cost & Google Ranking Guide</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container py-4" style="max-width:900px;">

  <h3><i class="fas fa-rupee-sign me-2 text-success"></i>ખર્ચ (Cost) + Google Top Ranking — સાચી વાત</h3>

  <div class="alert alert-warning">
    <strong>સાચું જણાવું:</strong> કોઈ પણ software "એક ક્લિકમાં Google #1" ગારંટી આપી શકતું નથી.
    Meta tags + backlinks + content + time (2–3 મહિના) જોઈએ. આ project 80% કામ auto કરે — 20% તમારે website પર apply કરવું પડે.
  </div>

  <div class="card mb-4">
    <div class="card-header bg-success text-white"><h5 class="mb-0">💰 માસિક ખર્ચ (India — અંદાજ)</h5></div>
    <div class="card-body table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="table-light">
          <tr><th>વસ્તુ</th><th>ખર્ચ</th><th>શું મળે</th><th>જરૂરી?</th></tr>
        </thead>
        <tbody>
          <tr class="table-success">
            <td><strong>XAMPP / Hosting</strong></td>
            <td>₹0 – ₹500/mo</td>
            <td>Local free; live site Hostinger ~₹149/mo</td>
            <td>✅ હા</td>
          </tr>
          <tr class="table-success">
            <td><strong>OpenAI (ChatGPT)</strong></td>
            <td><strong>₹400 – ₹2000/mo</strong></td>
            <td>Meta tags, articles, social content</td>
            <td>✅ હા</td>
          </tr>
          <tr>
            <td>DataForSEO</td>
            <td>₹0 – ₹800/mo</td>
            <td>Real Google rank check (~100 free/day)</td>
            <td>ઓછું optional</td>
          </tr>
          <tr>
            <td>OpenAI (ChatGPT)</td>
            <td>₹400 – ₹2000/mo</td>
            <td>Primary AI for all content generation</td>
            <td>✅ હા</td>
          </tr>
          <tr>
            <td>Google Ads</td>
            <td>₹5000+ /mo</td>
            <td>Instant traffic — SEO અલગ છે</td>
            <td>optional</td>
          </tr>
          <tr class="table-primary">
            <td><strong>Minimum real SEO</strong></td>
            <td><strong>₹400 – ₹500/mo</strong></td>
            <td>ChatGPT + hosting</td>
            <td>—</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header bg-primary text-white"><h5 class="mb-0">🏷️ Meta Tags — શું change કરવું?</h5></div>
    <div class="card-body">
      <p>તમારી <strong>actual website</strong> (learnmoretechnologies.in વગેરે) પર આ બધું જોઈએ:</p>
      <ol>
        <li><code>&lt;title&gt;</code> — keyword + brand (30–60 chars)</li>
        <li><code>&lt;meta name="description"&gt;</code> — 150 chars, keyword + phone/CTA</li>
        <li><code>og:title</code>, <code>og:description</code>, <code>og:image</code> — social share</li>
        <li><code>&lt;link rel="canonical"&gt;</code> — duplicate URL avoid</li>
        <li><code>Schema JSON-LD</code> — Google rich results</li>
        <li><code>&lt;h1&gt;</code> — page પર એક જ, keyword સાથે</li>
      </ol>
      <p class="mb-0">
        <strong>આ project:</strong> Run SEO → <strong>Meta Tags</strong> tab → Copy HTML → તમારી website ના <code>&lt;head&gt;</code> માં paste.
        WordPress હોય તો: Yoast SEO / Rank Math માં Title + Description ભરો.
      </p>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">📈 Google Top પર આવવા — Real steps (2–3 months)</h5></div>
    <div class="card-body">
      <table class="table">
        <tr><td width="40"><strong>1</strong></td><td>Meta + On-Page fix (આ system) — score 70+</td></tr>
        <tr><td><strong>2</strong></td><td>Google Search Console માં site add — <a href="https://search.google.com/search-console" target="_blank">search.google.com/search-console</a> (FREE)</td></tr>
        <tr><td><strong>3</strong></td><td>Submissions — Blogger, Dev.to, backlinks (આ system)</td></tr>
        <tr><td><strong>4</strong></td><td>Weekly content — 2 blogs/month keyword સાથે</td></tr>
        <tr><td><strong>5</strong></td><td>Rank Tracker — progress જોવો (DataForSEO optional)</td></tr>
      </table>
      <p class="text-muted small mb-0">Competition high હોય (જેમ "power bi training") તો 3–6 મહિના લાગી શકે. Local keyword ("btm") ઝડપી rank થાય.</p>
    </div>
  </div>

  <a href="api-setup.php" class="btn btn-primary me-2">API Keys Setup</a>
  <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
