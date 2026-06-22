<?php
function renderProfessional(array $d): string
{
    $expHtml = '';
    foreach ($d['experience'] ?? [] as $e) {
        $points = '';
        foreach (array_filter(explode("\n", $e['points'] ?? '')) as $p) {
            $points .= '<li>' . htmlspecialchars(trim($p)) . '</li>';
        }
        $expHtml .= <<<HTML
        <div class="exp-item">
            <div class="exp-header">
                <span class="company">{$e['company']}</span>
                <span class="role">{$e['role']}</span>
                <span class="dates">{$e['start']} – {$e['end']}</span>
            </div>
            <ul>{$points}</ul>
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
            <div class="name">{$p['name']}</div>
            <div class="tech">{$p['tech']}</div>
            <ul>{$points}</ul>
        </div>
HTML;
    }

    $otherProj = '';
    if (!empty($d['other_projects'])) {
        $items = implode('</span><span>', array_map('trim', explode(',', $d['other_projects'])));
        $otherProj = <<<HTML
        <div class="proj-item">
            <div class="name">Other Projects</div>
            <div class="other-projects"><span>{$items}</span></div>
        </div>
HTML;
    }

    $aiTools = '';
    if (!empty($d['ai_tools'])) {
        $tools = implode('</span><span>', array_map('trim', explode(',', $d['ai_tools'])));
        $aiTools = <<<HTML
        <div class="section">
            <div class="section-title">AI Tools</div>
            <div class="ai-tools"><span>{$tools}</span></div>
        </div>
HTML;
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
.resume{max-width:900px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.12);overflow:hidden}
.header{background:linear-gradient(135deg,#1a3a5c,#2c5f8a);color:#fff;padding:40px 45px 35px}
.header h1{font-size:32px;font-weight:700}
.header .title{font-size:16px;font-weight:400;color:#b8d4f0;margin-top:5px}
.header .contact{display:flex;flex-wrap:wrap;gap:20px;margin-top:14px;font-size:14px;color:#d0e2f3}
.body{display:flex}
.sidebar{width:250px;background:#f4f7fb;padding:30px 25px;flex-shrink:0}
.main{flex:1;padding:30px 35px 30px 30px}
.section{margin-bottom:28px;page-break-inside:avoid}
.section:last-child{margin-bottom:0}
.section-title{font-size:15px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#1a3a5c;border-bottom:2px solid #1a3a5c;padding-bottom:6px;margin-bottom:14px}
.sidebar .section-title{border-bottom-color:#c0d4e8}
.skill-group{margin-bottom:14px}
.skill-group:last-child{margin-bottom:0}
.skill-group .label{font-size:12px;font-weight:600;color:#1a3a5c;margin-bottom:4px}
.skill-group .items{font-size:13px;color:#444;line-height:1.6}
.exp-item{margin-bottom:22px;page-break-inside:avoid}
.exp-item:last-child{margin-bottom:0}
.exp-header{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:4px 12px;margin-bottom:3px}
.exp-header .company{font-size:15px;font-weight:700;color:#1a3a5c}
.exp-header .role{font-size:13px;font-weight:600;color:#555}
.exp-header .dates{font-size:12px;color:#888;font-weight:500}
.exp-item ul{margin-top:6px;padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.exp-item ul li{margin-bottom:2px}
.proj-item{margin-bottom:16px;page-break-inside:avoid}
.proj-item:last-child{margin-bottom:0}
.proj-item .name{font-size:14px;font-weight:700;color:#1a3a5c}
.proj-item .tech{font-size:12px;color:#777;font-weight:500;margin-bottom:3px}
.proj-item ul{padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.proj-item ul li{margin-bottom:2px}
.other-projects{display:flex;flex-wrap:wrap;gap:4px 16px;font-size:13px;color:#444;margin-top:6px}
.edu-item{font-size:14px;color:#444}
.edu-item .degree{font-weight:600;color:#1a3a5c}
.edu-item .meta{font-size:13px;color:#777;margin-top:2px}
.summary{font-size:13.5px;color:#444;line-height:1.7;margin-bottom:0}
.ai-tools{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.ai-tools span{background:#e6edf6;color:#1a3a5c;font-size:12px;font-weight:500;padding:3px 12px;border-radius:12px}
@media print{body{background:#fff;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}.resume{box-shadow:none;border-radius:0;max-width:100%}.header{background:#1a3a5c !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.sidebar{background:#f4f7fb !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.ai-tools span{background:#e6edf6 !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media(max-width:700px){.body{flex-direction:column}.sidebar{width:100%}.header{padding:30px 25px}.main{padding:25px}}
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
    <div class="sidebar">
      <div class="section">
        <div class="section-title">Skills</div>
        <div class="skill-group"><div class="label">Backend</div><div class="items">{$d['skills_backend']}</div></div>
        <div class="skill-group"><div class="label">Frontend</div><div class="items">{$d['skills_frontend']}</div></div>
        <div class="skill-group"><div class="label">Database &amp; Caching</div><div class="items">{$d['skills_database']}</div></div>
        <div class="skill-group"><div class="label">API &amp; Integrations</div><div class="items">{$d['skills_api']}</div></div>
        <div class="skill-group"><div class="label">DevOps &amp; Tools</div><div class="items">{$d['skills_tools']}</div></div>
        <div class="skill-group"><div class="label">Architecture</div><div class="items">{$d['skills_arch']}</div></div>
      </div>
      <div class="section">
        <div class="section-title">Education</div>
        <div class="edu-item">
          <div class="degree">{$d['edu_degree']}</div>
          <div class="meta">{$d['edu_institution']} &middot; {$d['edu_year']}</div>
        </div>
      </div>
    </div>
    <div class="main">
      <div class="section">
        <div class="section-title">Professional Summary</div>
        <p class="summary">{$d['summary']}</p>
      </div>
      <div class="section">
        <div class="section-title">Experience</div>
        {$expHtml}
      </div>
      <div class="section">
        <div class="section-title">Projects</div>
        {$projHtml}
        {$otherProj}
      </div>
      {$aiTools}
    </div>
  </div>
</div>
</body>
</html>
HTML;
}
