<?php
function renderMinimal(array $d): string
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
                <span class="dates">{$e['start']} – {$e['end']}</span>
            </div>
            <div class="role">{$e['role']}</div>
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

    $projExtra = '';
    if (!empty($d['other_projects'])) {
        $projExtra = '<div class="proj-item"><div class="name">Other Projects</div><div class="other-list">' . htmlspecialchars($d['other_projects']) . '</div></div>';
    }

    $aiHtml = '';
    if (!empty($d['ai_tools'])) {
        $tools = implode('</span><span>', array_map('trim', explode(',', $d['ai_tools'])));
        $aiHtml = "<div class=\"ai-line\"><strong>AI Tools:</strong> <span>{$tools}</span></div>";
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
.resume{max-width:860px;margin:0 auto;background:#fff;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.1);overflow:hidden;display:flex}
.sidebar{width:230px;background:#1e293b;color:#fff;padding:35px 25px;flex-shrink:0}
.main{flex:1;padding:35px 40px}
.sidebar .name{font-size:24px;font-weight:700;color:#fff;margin-bottom:4px}
.sidebar .title{font-size:13px;font-weight:400;color:#94a3b8;margin-bottom:20px;line-height:1.5}
.sidebar .contact{font-size:12px;color:#cbd5e1;line-height:2}
.sidebar .contact span{display:block}
.side-hr{height:1px;background:#334155;margin:20px 0}
.sidebar .section-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#94a3b8;margin-bottom:10px}
.skill-line{font-size:12px;color:#cbd5e1;margin-bottom:6px;line-height:1.6}
.edu-side .degree{font-size:13px;font-weight:600;color:#fff}
.edu-side .meta{font-size:11px;color:#94a3b8;margin-top:2px}
.main .section{margin-bottom:28px;page-break-inside:avoid}
.main .section:last-child{margin-bottom:0}
.main .section-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#1e293b;margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid #1e293b}
.summary{font-size:13.5px;color:#444;line-height:1.7;margin-bottom:0}
.exp-item{margin-bottom:20px;page-break-inside:avoid}
.exp-item:last-child{margin-bottom:0}
.exp-header{display:flex;justify-content:space-between;align-items:baseline;flex-wrap:wrap;gap:4px 12px}
.exp-header .company{font-size:15px;font-weight:700;color:#1e293b}
.exp-header .dates{font-size:12px;color:#94a3b8}
.role{font-size:13px;font-weight:600;color:#64748b;margin:2px 0 6px}
.exp-item ul{padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.exp-item ul li{margin-bottom:2px}
.proj-item{margin-bottom:16px;page-break-inside:avoid}
.proj-item:last-child{margin-bottom:0}
.proj-item .name{font-size:14px;font-weight:700;color:#1e293b}
.proj-item .tech{font-size:11px;color:#94a3b8;font-weight:500;margin-bottom:2px}
.proj-item ul{padding-left:18px;font-size:13px;color:#444;line-height:1.7}
.proj-item ul li{margin-bottom:2px}
.other-list{font-size:13px;color:#555;margin-top:4px}
.edu-main .degree{font-weight:600;color:#1e293b}
.edu-main .meta{font-size:13px;color:#777;margin-top:2px}
.ai-line{display:flex;align-items:center;gap:10px;font-size:13px;color:#444;flex-wrap:wrap;margin-top:14px}
.ai-line span{background:#f1f5f9;color:#1e293b;font-size:12px;font-weight:500;padding:2px 10px;border-radius:10px}
@media print{body{background:#fff;padding:0;-webkit-print-color-adjust:exact;print-color-adjust:exact}.resume{box-shadow:none;border-radius:0;max-width:100%}.sidebar{background:#1e293b !important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.sidebar .name{color:#fff !important}}
@media(max-width:700px){.resume{flex-direction:column}.sidebar{width:100%}}
</style>
</head>
<body>
<div class="resume">
  <div class="sidebar">
    <div class="name">{$d['name']}</div>
    <div class="title">{$d['title']}</div>
    <div class="contact">
      <span>{$d['location']}</span>
      <span>{$d['phone']}</span>
      <span>{$d['email']}</span>
    </div>
    <div class="side-hr"></div>
    <div class="section-title">Skills</div>
    <div class="skill-line"><strong>Backend:</strong> {$d['skills_backend']}</div>
    <div class="skill-line"><strong>Frontend:</strong> {$d['skills_frontend']}</div>
    <div class="skill-line"><strong>Database:</strong> {$d['skills_database']}</div>
    <div class="skill-line"><strong>API:</strong> {$d['skills_api']}</div>
    <div class="skill-line"><strong>Tools:</strong> {$d['skills_tools']}</div>
    <div class="side-hr"></div>
    <div class="section-title">Education</div>
    <div class="edu-side">
      <div class="degree">{$d['edu_degree']}</div>
      <div class="meta">{$d['edu_institution']} &middot; {$d['edu_year']}</div>
    </div>
  </div>
  <div class="main">
    <div class="section">
      <div class="section-title">About</div>
      <p class="summary">{$d['summary']}</p>
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
    {$aiHtml}
  </div>
</div>
</body>
</html>
HTML;
}
