<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$d = [
    'name'             => $_POST['name'] ?? '',
    'title'            => $_POST['title'] ?? '',
    'email'            => $_POST['email'] ?? '',
    'phone'            => $_POST['phone'] ?? '',
    'location'         => $_POST['location'] ?? '',
    'summary'          => $_POST['summary'] ?? '',
    'skills_backend'   => $_POST['skills_backend'] ?? '',
    'skills_frontend'  => $_POST['skills_frontend'] ?? '',
    'skills_database'  => $_POST['skills_database'] ?? '',
    'skills_api'       => $_POST['skills_api'] ?? '',
    'skills_tools'     => $_POST['skills_tools'] ?? '',
    'skills_arch'      => $_POST['skills_arch'] ?? '',
    'edu_degree'       => $_POST['edu_degree'] ?? '',
    'edu_institution'  => $_POST['edu_institution'] ?? '',
    'edu_year'         => $_POST['edu_year'] ?? '',
    'other_projects'   => $_POST['other_projects'] ?? '',
    'ai_tools'         => $_POST['ai_tools'] ?? '',
];

$d['experience'] = [];
if (isset($_POST['exp']) && is_array($_POST['exp'])) {
    foreach ($_POST['exp'] as $e) {
        if (!empty(trim($e['company'] ?? '')) || !empty(trim($e['role'] ?? ''))) {
            $d['experience'][] = [
                'company' => $e['company'] ?? '',
                'role'    => $e['role'] ?? '',
                'start'   => $e['start'] ?? '',
                'end'     => $e['end'] ?? '',
                'points'  => $e['points'] ?? '',
            ];
        }
    }
}

$d['projects'] = [];
if (isset($_POST['proj']) && is_array($_POST['proj'])) {
    foreach ($_POST['proj'] as $p) {
        if (!empty(trim($p['name'] ?? ''))) {
            $d['projects'][] = [
                'name'   => $p['name'] ?? '',
                'tech'   => $p['tech'] ?? '',
                'points' => $p['points'] ?? '',
            ];
        }
    }
}

$template = $_POST['template'] ?? 'professional';
$allowedTemplates = ['professional', 'modern', 'minimal'];
if (!in_array($template, $allowedTemplates)) {
    $template = 'professional';
}

require_once __DIR__ . "/templates/{$template}.php";
$renderFn = 'render' . ucfirst($template);
$html = $renderFn($d);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($d['name']) ?> - Resume Preview</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #e6e9ef; padding: 20px; color: #2d3436; }
    .toolbar {
      max-width: 900px; margin: 0 auto 16px;
      display: flex; justify-content: space-between; align-items: center;
      background: #fff; padding: 14px 24px; border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .toolbar h2 { font-size: 16px; color: #1a3a5c; }
    .toolbar .actions { display: flex; gap: 10px; }
    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 9px 20px; border-radius: 5px; font-size: 13px;
      font-weight: 600; cursor: pointer; border: none;
      text-decoration: none; transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.85; }
    .btn-pdf { background: #1a3a5c; color: #fff; }
    .btn-edit { background: #e5e7eb; color: #374151; }
    .btn-back { background: #f3f4f6; color: #555; }
    @media print {
      body { background: #fff; padding: 0; }
      .toolbar { display: none; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <h2>&#x1F4C4; Resume Preview</h2>
    <div class="actions">
      <a class="btn btn-edit" href="index.php">&larr; Edit</a>
      <button class="btn btn-pdf" onclick="window.print()">&#x1F4E5; Download PDF</button>
    </div>
  </div>
  <?= $html ?>
</body>
</html>
