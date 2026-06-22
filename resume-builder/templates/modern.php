<?php
function renderModern(array $d): string
{
    $expHtml = '';
    foreach ($d['experience'] ?? [] as $e) {
        $points = '';
        foreach (array_filter(explode("\n", $e['points'] ?? '')) as $p) {
            $points .= '<li>' . htmlspecialchars(trim($p)) . '</li>';
        }
        $expHtml .= <<<HTML
        <div class="exp-item">
            <div class="exp-dot"></div>
            <div class="exp-content">
                <div class="exp-header">
                    <span class="company">{$e['company']}</span>
                    <span class="dates">{$e['start']} – {$e['end']}</span>
                </div>
                <div class="role">{$e['role']}</div>
                <ul>{$points}</ul>
            </div>
        </div>
HTML;
    }

    $projHtml = '';
    foreach ($d['projects'] ?? [] as $p) {
        $points = '';
        foreach (array_filter(explode("\n", $p['points'] ?? '')) as $pt) {
            $points .= '<li>' . htmlspecialchars(trim($pt)) . '</li>';
        }
        $projHtml .= <<<HTML
        <div class="proj-item">
            <div class="proj-header">
                <span class="name">{$p['name']}</span>
                <span class="tech">{$p['tech']}</span>
            </div>
            <ul>{$points}</ul>
        </div>
HTML;
    }

    $projExtra = '';
    if (!empty($d['other_projects'])) {
        $items = implode(', ', array_map('trim', explode(',', $d['other_projects'])));
        $projExtra = "<p class=\"proj-extra\"><strong>Others:</strong> {$items}</p>";
    }

    $aiHtml = '';
    if (!empty($d['ai_tools'])) {
        $tools = implode('</span><span>', array_map('trim', explode(',', $d['ai_tools'])));
        $aiHtml = "<div class=\"ai-section\"><strong>AI Tools:</strong> <span>{$tools}</span></div>";
    }

    $skillItems = [];
    $skillMap = [
        'Backend' => $d['skills_backend'],
        'Frontend' => $d['skills_frontend'],
        'Database &amp; Caching' => $d['skills_database'],
        'API &amp; Integrations' => $d['skills_api'],
        'DevOps &amp; Tools' => $d['skills_tools'],
    ];
    foreach ($skillMap as $label => $val) {
        if (!empty($val)) {
            $skillItems[] = "<div class=\"skill-group\"><span class=\"skill-label\">{$label}</span><span class=\"skill-items\">{$val}</span></div>";
        }
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{$d['name']} - Resume</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI','Helvetica Neue',Arial,sans-serif;background:#e6e9ef;padding:20px;color:#2d3436;line-height:1.5}
.resume{max-width:860px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,0.1);overflow:hidden}
.header{background:linear-gradient(135deg,#0d9488,#14b8a6);color:#fff;padding:45px 50px 40px;text-align:center}
.header h1{font-size:34px;font-weight:700;letter-spacing:1px}
.header .title{font-size:15px;font-weight:400;color:#ccfbf1;margin-top:6px;opacity:0.9}
.header .contact{display:flex;justify-content:center;flex-wrap:wrap;gap:18px;margin-top:14px;font-size:13px;color:#ccfbf1}
.body{padding:35px 50px}
.section{margin-bottom:32px;page-break-inside:avoid}
.section:last-child{margin-bottom:0}
.section-title{font-size:16px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#0d9488;margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid #0d9488}
.summary{font-size:14px;color:#444;line-height:1.8;margin-bottom:0}
.skills-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.skill-group{display:flex;flex-direction:column;gap:2px;padding:10px 14px;background:#f0fdfa;border-radius:6px;border-left:3px solid #0d9488}
.skill-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#0d9488}
.skill-items{font-size:13px;color:#444;line-height:1.5}
.exp-item{display:flex;gap:16px;margin-bottom:24px;page-break-inside:avoid}
.exp-item:last-child{margin-bottom:0}
.exp-dot{width:12px;height:12px;border-radius:50%;background:#0d9488;margin-top:5px;flex-shrink:0}
.exp-content{flex:1}
.exp-header{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:4px 12px}
.exp-header .company{font-size:16px;font-weight:700;color:#0d9488}
.exp-header .dates{font-size:12px;color:#999;font-weight:500}
.role{font-size:13px;font-weight:600;color:#555;margin:2px 0 6px}
.exp-content ul{padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.exp-content ul li{margin-bottom:2px}
.proj-item{margin-bottom:18px;page-break-inside:avoid}
.proj-item:last-child{margin-bottom:0}
.proj-header{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:4px 12px;margin-bottom:4px}
.proj-header .name{font-size:15px;font-weight:700;color:#0d9488}
.proj-header .tech{font-size:12px;color:#999;font-weight:500}
.proj-item ul{padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.proj-item ul li{margin-bottom:2px}
.proj-extra{font-size:13px;color:#555;margin-top:6px}
.edu-item{font-size:14px;color:#444}
.edu-item .degree{font-weight:600;color:#0d9488}
.edu-item .meta{font-size:13px;color:#888;margin-top:2px}
.ai-section{display:flex;align-items:center;gap:10px;font-size:13px;color:#444;flex-wrap:wrap;margin-top:14px}
.ai-section span{background:#f0fdfa;color:#0d9488;font-size:12px;font-weight:500;padding:3px 12px;border-radius:10px}
@media print{body{background:#fff;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}.resume{box-shadow:none;border-radius:0;max-width:100%}.header{background:#0d9488 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media(max-width:600px){.body{padding:25px 20px}.skills-grid{grid-template-columns:1fr}.exp-item{flex-direction:column;gap:6px}}
</style>
</head>
<body>
<div class="resume">
  <div class="header">
    <h1>{$d['name']}</h1>
    <div class="title">{$d['title']}</div>
    <div class="contact">
      <span>{$d['location']}</span>
      <span>{$d['phone']}</span>
      <span>{$d['email']}</span>
    </div>
  </div>
  <div class="body">
    <div class="section">
      <div class="section-title">About</div>
      <p class="summary">{$d['summary']}</p>
    </div>
    <div class="section">
      <div class="section-title">Skills</div>
       <div class="skills-grid">{implode('', $skillItems)}</div>
    </div>
    <div class="section">
      <div class="section-title">Experience</div>
      {$expHtml}
    </div>
    <div class="section">
      <div class="section-title">Projects</div>
      {$projHtml}
      {$projExtra}
    </div>
    <div class="section">
      <div class="section-title">Education</div>
      <div class="edu-item">
        <div class="degree">{$d['edu_degree']}</div>
        <div class="meta">{$d['edu_institution']} &middot; {$d['edu_year']}</div>
      </div>
    </div>
    {$aiHtml}
  </div>
</div>
</body>
</html>
HTML;
}
