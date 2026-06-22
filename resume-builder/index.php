<?php
$parsed = [];
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['resume_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx'];
    if (in_array($ext, $allowed)) {
        $dest = __DIR__ . '/uploads/' . uniqid('resume_') . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $dest);
        require_once __DIR__ . '/parse.php';
        $parsed = parseResumeFile($dest, $ext);
        if (!empty($parsed['error'])) {
            $uploadError = $parsed['error'];
            $parsed = [];
        }
    } else {
        $uploadError = 'Only PDF, DOC, DOCX files are allowed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resume Builder</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>&#x1F4C4; Resume Builder</h1>
      <p>Upload your existing resume or fill the form below. Pick a template and generate a professional resume.</p>
    </div>

    <?php if ($uploadError): ?>
      <div class="alert alert-error"><?= htmlspecialchars($uploadError) ?></div>
    <?php elseif (!empty($parsed)): ?>
      <div class="alert alert-success">File parsed successfully! Review the fields below.</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="upload-form" action="index.php">
      <div class="upload-section">
        <label class="upload-label">
          <span>Upload Resume (PDF / DOC / DOCX)</span>
          <input type="file" name="resume_file" accept=".pdf,.doc,.docx" onchange="this.form.submit()">
        </label>
        <span class="upload-hint">Auto-fills the form below. You can edit afterwards.</span>
      </div>
    </form>

    <form method="post" class="resume-form" id="resumeForm" action="generate.php">
      <div class="form-columns">
        <div class="form-main">

          <div class="section-title">Personal Info</div>
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($parsed['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Title / Headline</label>
              <input type="text" name="title" value="<?= htmlspecialchars($parsed['title'] ?? '') ?>" placeholder="e.g. Senior PHP Laravel Developer">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($parsed['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($parsed['phone'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Location</label>
              <input type="text" name="location" value="<?= htmlspecialchars($parsed['location'] ?? '') ?>" placeholder="e.g. Jabalpur, India">
            </div>
            <div class="form-group">
              <label>LinkedIn (optional)</label>
              <input type="url" name="linkedin" value="<?= htmlspecialchars($parsed['linkedin'] ?? '') ?>">
            </div>
          </div>

          <div class="section-title">Professional Summary</div>
          <div class="form-group">
            <textarea name="summary" rows="4"><?= htmlspecialchars($parsed['summary'] ?? '') ?></textarea>
          </div>

          <div class="section-title">Skills</div>
          <div class="form-group">
            <label class="sub-label">Backend</label>
            <input type="text" name="skills_backend" value="<?= htmlspecialchars($parsed['skills_backend'] ?? '') ?>" placeholder="PHP, Laravel, CodeIgniter, REST API, MVC">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="sub-label">Frontend</label>
              <input type="text" name="skills_frontend" value="<?= htmlspecialchars($parsed['skills_frontend'] ?? '') ?>" placeholder="HTML5, CSS3, Bootstrap, JavaScript, jQuery">
            </div>
            <div class="form-group">
              <label class="sub-label">Database &amp; Caching</label>
              <input type="text" name="skills_database" value="<?= htmlspecialchars($parsed['skills_database'] ?? '') ?>" placeholder="MySQL, Redis, Query Optimization">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="sub-label">API &amp; Integrations</label>
              <input type="text" name="skills_api" value="<?= htmlspecialchars($parsed['skills_api'] ?? '') ?>" placeholder="Payment Gateways, Webhooks, REST APIs">
            </div>
            <div class="form-group">
              <label class="sub-label">DevOps &amp; Tools</label>
              <input type="text" name="skills_tools" value="<?= htmlspecialchars($parsed['skills_tools'] ?? '') ?>" placeholder="Git, Docker, Postman, PHPUnit">
            </div>
          </div>
          <div class="form-group">
            <label class="sub-label">Architecture (optional)</label>
            <input type="text" name="skills_arch" value="<?= htmlspecialchars($parsed['skills_arch'] ?? '') ?>" placeholder="Queues, Events, Repository Pattern, SOLID">
          </div>

          <div class="section-title">Experience</div>
          <div id="experienceContainer">
            <?php
            $exp = $parsed['experience'] ?? [[]];
            foreach ($exp as $i => $e):
            ?>
            <div class="exp-block">
              <div class="form-row">
                <div class="form-group">
                  <label>Company</label>
                  <input type="text" name="exp[<?= $i ?>][company]" value="<?= htmlspecialchars($e['company'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Role</label>
                  <input type="text" name="exp[<?= $i ?>][role]" value="<?= htmlspecialchars($e['role'] ?? '') ?>">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Start Date</label>
                  <input type="text" name="exp[<?= $i ?>][start]" value="<?= htmlspecialchars($e['start'] ?? '') ?>" placeholder="e.g. Dec 2025">
                </div>
                <div class="form-group">
                  <label>End Date</label>
                  <input type="text" name="exp[<?= $i ?>][end]" value="<?= htmlspecialchars($e['end'] ?? '') ?>" placeholder="e.g. Present">
                </div>
              </div>
              <div class="form-group">
                <label>Points (one per line)</label>
                <textarea name="exp[<?= $i ?>][points]" rows="4"><?= htmlspecialchars($e['points'] ?? '') ?></textarea>
              </div>
              <?php if ($i > 0): ?>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn-add" onclick="addExperience()">+ Add Experience</button>

          <div class="section-title">Projects</div>
          <div id="projectContainer">
            <?php
            $proj = $parsed['projects'] ?? [[]];
            foreach ($proj as $i => $p):
            ?>
            <div class="proj-block">
              <div class="form-row">
                <div class="form-group">
                  <label>Project Name</label>
                  <input type="text" name="proj[<?= $i ?>][name]" value="<?= htmlspecialchars($p['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                  <label>Technologies</label>
                  <input type="text" name="proj[<?= $i ?>][tech]" value="<?= htmlspecialchars($p['tech'] ?? '') ?>" placeholder="Laravel 11 | PHP 8 | MySQL">
                </div>
              </div>
              <div class="form-group">
                <label>Points (one per line)</label>
                <textarea name="proj[<?= $i ?>][points]" rows="3"><?= htmlspecialchars($p['points'] ?? '') ?></textarea>
              </div>
              <?php if ($i > 0): ?>
              <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" class="btn-add" onclick="addProject()">+ Add Project</button>

          <div class="section-title">Education</div>
          <div class="form-row">
            <div class="form-group">
              <label>Degree</label>
              <input type="text" name="edu_degree" value="<?= htmlspecialchars($parsed['edu_degree'] ?? '') ?>" placeholder="Bachelor of Engineering (CSE)">
            </div>
            <div class="form-group">
              <label>Institution</label>
              <input type="text" name="edu_institution" value="<?= htmlspecialchars($parsed['edu_institution'] ?? '') ?>" placeholder="University Name">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Year</label>
              <input type="text" name="edu_year" value="<?= htmlspecialchars($parsed['edu_year'] ?? '') ?>" placeholder="e.g. 2016">
            </div>
            <div class="form-group">
              <label>Other Projects (comma separated)</label>
              <input type="text" name="other_projects" value="<?= htmlspecialchars($parsed['other_projects'] ?? '') ?>" placeholder="Project1, Project2, Project3">
            </div>
          </div>

          <div class="section-title">AI Tools (optional)</div>
          <div class="form-group">
            <input type="text" name="ai_tools" value="<?= htmlspecialchars($parsed['ai_tools'] ?? '') ?>" placeholder="ChatGPT, DeepSeek, Gemini">
          </div>
        </div>

        <div class="form-sidebar">
          <div class="template-picker">
            <div class="section-title">Choose Template</div>
            <label class="template-option">
              <input type="radio" name="template" value="professional" checked>
              <div class="template-card">
                <div class="template-preview" style="background:linear-gradient(135deg,#1a3a5c,#2c5f8a);height:40px;border-radius:4px;"></div>
                <strong>Professional</strong>
                <span>Classic two-column with dark header</span>
              </div>
            </label>
            <label class="template-option">
              <input type="radio" name="template" value="modern">
              <div class="template-card">
                <div class="template-preview" style="background:linear-gradient(135deg,#0d9488,#14b8a6);height:40px;border-radius:4px;"></div>
                <strong>Modern</strong>
                <span>Clean teal accent, single column</span>
              </div>
            </label>
            <label class="template-option">
              <input type="radio" name="template" value="minimal">
              <div class="template-card">
                <div class="template-preview" style="background:#374151;height:40px;border-radius:4px;"></div>
                <strong>Minimal</strong>
                <span>Bold &amp; simple, dark sidebar</span>
              </div>
            </label>
          </div>

          <button type="submit" class="btn-generate">&#x1F680; Generate Resume</button>
        </div>
      </div>
    </form>
  </div>

  <script>
  function addExperience() {
    const c = document.getElementById('experienceContainer');
    const i = c.children.length;
    const div = document.createElement('div');
    div.className = 'exp-block';
    div.innerHTML = `
      <div class="form-row">
        <div class="form-group"><label>Company</label><input type="text" name="exp[${i}][company]"></div>
        <div class="form-group"><label>Role</label><input type="text" name="exp[${i}][role]"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Start Date</label><input type="text" name="exp[${i}][start]" placeholder="e.g. Dec 2025"></div>
        <div class="form-group"><label>End Date</label><input type="text" name="exp[${i}][end]" placeholder="e.g. Present"></div>
      </div>
      <div class="form-group"><label>Points (one per line)</label><textarea name="exp[${i}][points]" rows="4"></textarea></div>
      <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
    `;
    c.appendChild(div);
  }
  function addProject() {
    const c = document.getElementById('projectContainer');
    const i = c.children.length;
    const div = document.createElement('div');
    div.className = 'proj-block';
    div.innerHTML = `
      <div class="form-row">
        <div class="form-group"><label>Project Name</label><input type="text" name="proj[${i}][name]"></div>
        <div class="form-group"><label>Technologies</label><input type="text" name="proj[${i}][tech]"></div>
      </div>
      <div class="form-group"><label>Points (one per line)</label><textarea name="proj[${i}][points]" rows="3"></textarea></div>
      <button type="button" class="btn-remove" onclick="this.parentElement.remove()">Remove</button>
    `;
    c.appendChild(div);
  }
  </script>
</body>
</html>
