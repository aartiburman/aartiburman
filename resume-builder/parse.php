<?php
function parseResumeFile(string $path, string $ext): array
{
    $text = '';
    try {
        if ($ext === 'pdf') {
            $text = parsePDF($path);
        } elseif (in_array($ext, ['doc', 'docx'])) {
            $text = parseDOCX($path);
        }
    } catch (Exception $e) {
        return ['error' => 'Could not parse file: ' . $e->getMessage()];
    }

    if (empty(trim($text))) {
        return ['error' => 'No text could be extracted from the file.'];
    }

    return extractFields($text);
}

function parsePDF(string $path): string
{
    $text = '';

    // Try pdftotext if available (XAMPP or Linux)
    $pdftotext = trim(shell_exec('where pdftotext 2>nul'));
    if ($pdftotext) {
        $tmp = tempnam(sys_get_temp_dir(), 'pdf') . '.txt';
        shell_exec('"' . $pdftotext . '" "' . $path . '" "' . $tmp . '" 2>nul');
        if (file_exists($tmp)) {
            $text = file_get_contents($tmp);
            unlink($tmp);
        }
        if (!empty(trim($text))) return $text;
    }

    // Fallback: raw PDF text extraction
    $content = file_get_contents($path);
    if ($content === false) return '';

    // Extract text between parentheses (PDF text objects)
    preg_match_all('/\(([^)]*)\)/', $content, $matches);
    if (!empty($matches[1])) {
        $text = implode("\n", $matches[1]);
        $text = preg_replace('/\\\([^)]*)\)/', '', $text);
        $text = preg_replace('/[^a-zA-Z0-9\s@.,+\-():;!\/#]/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
    }

    return $text;
}

function parseDOCX(string $path): string
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) return '';

    $content = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($content === false) return '';

    $content = str_replace(['<w:br/>', '<w:tab/>'], ["\n", "\t"], $content);
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');

    return $content;
}

function extractFields(string $text): array
{
    $data = [];
    $lines = explode("\n", $text);
    $lines = array_map('trim', $lines);
    $lines = array_filter($lines, fn($l) => strlen($l) > 1);
    $lines = array_values($lines);

    $full = implode("\n", $text);

    // Name - first non-empty line that looks like a name (2-4 words, no special chars)
    foreach ($lines as $i => $line) {
        if (preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3}$/', $line)) {
            $data['name'] = $line;
            break;
        }
    }
    if (empty($data['name']) && !empty($lines[0])) {
        $data['name'] = $lines[0];
    }

    // Email
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $m)) {
        $data['email'] = $m[0];
    }

    // Phone
    if (preg_match('/\+?\d{1,4}[-.\s]?\d{7,12}/', $text, $m)) {
        $data['phone'] = $m[0];
    }

    // Location (look for "City, Country" pattern)
    if (preg_match('/[A-Z][a-z]+(?:[\s-][A-Z][a-z]+)*,\s*[A-Z][a-z]+(?:\s*\([^)]+\))?/', $text, $m)) {
        $data['location'] = $m[0];
    }

    // Title - line with pipe separators or containing "Developer" "Engineer" etc
    foreach ($lines as $line) {
        if (preg_match('/(PHP|Laravel|Developer|Engineer|Full.?Stack|Software|Backend)/i', $line) && preg_match('/\||[A-Z][a-z]+.*[A-Z][a-z]+/', $line)) {
            $data['title'] = $line;
            break;
        }
    }

    // Summary: text between "summary" heading and next section
    if (preg_match('/(?:professional\s+summary|summary|profile)\s*\n(.+?)(?=\n{2,}(?:technical\s+skills|experience|education|projects))/is', $text, $m)) {
        $data['summary'] = trim(preg_replace('/\s+/', ' ', $m[1]));
    }

    // Skills - extract from skills section
    if (preg_match('/(?:technical\s+skills|skills)\s*\n(.+?)(?=\n{2,}(?:professional\s+experience|experience|projects|education))/is', $text, $m)) {
        $skillsText = $m[1];
        $skillLines = explode("\n", $skillsText);
        foreach ($skillLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (preg_match('/backend|php|laravel|codeigniter/i', $line) && !preg_match('/frontend|database|api|tools/i', $line)) {
                $data['skills_backend'] = cleanSkillsLine($line);
            } elseif (preg_match('/frontend|html|css|bootstrap|javascript|jquery/i', $line)) {
                $data['skills_frontend'] = cleanSkillsLine($line);
            } elseif (preg_match('/database|mysql|redis|query/i', $line)) {
                $data['skills_database'] = cleanSkillsLine($line);
            } elseif (preg_match('/api|integration|payment|gateway|webhook|smtp/i', $line)) {
                $data['skills_api'] = cleanSkillsLine($line);
            } elseif (preg_match('/tools|git|docker|postman|bitbucket/i', $line)) {
                $data['skills_tools'] = cleanSkillsLine($line);
            }
        }
    }

    // Experience
    $expBlocks = [];
    $currentExp = null;
    $inExp = false;
    $expHeadings = ['professional experience', 'experience', 'work experience', 'employment'];

    foreach ($lines as $i => $line) {
        if (preg_match('/^(professional\s+experience|experience|work\s+experience|employment)/i', $line)) {
            $inExp = true;
            continue;
        }
        if ($inExp && preg_match('/^(projects|education|technical\s+skills|skills|certifications)/i', $line)) {
            $inExp = false;
        }
        if (!$inExp) continue;

        // Company line: "CompanyName — Role" or "CompanyName - Role" or "CompanyName\nRole"
        if (preg_match('/^(.+?)\s*[—–-]\s*(.+)$/', $line, $m)) {
            if ($currentExp) $expBlocks[] = $currentExp;
            $currentExp = ['company' => trim($m[1]), 'role' => trim($m[2]), 'points' => ''];
            continue;
        }
        // Date line: month year patterns
        if (preg_match('/^\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+\d{4}\s*[–\-to]+\s*(?:Present|Now|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+\d{4})/i', $line)) {
            if ($currentExp) {
                $parts = preg_split('/\s*[–\-to]+\s*/', $line);
                $currentExp['start'] = trim($parts[0]);
                $currentExp['end'] = trim($parts[1] ?? 'Present');
            }
            continue;
        }
        // Bullet points
        if ($currentExp && preg_match('/^[•\-*]\s*(.+)/', $line, $m)) {
            $currentExp['points'] .= $m[1] . "\n";
        } elseif ($currentExp && !empty($line) && !preg_match('/^(company|role|date|technolog)/i', $line)) {
            // Try to assign company/role from plain lines
            if (empty($currentExp['company']) && preg_match('/^[A-Z][a-z]+/', $line)) {
                $currentExp['company'] = $line;
            } elseif (empty($currentExp['role']) && preg_match('/(Developer|Engineer|SSE|Lead|Manager|Architect)/i', $line)) {
                $currentExp['role'] = $line;
            }
        }
    }
    if ($currentExp) $expBlocks[] = $currentExp;
    if (!empty($expBlocks)) {
        $data['experience'] = $expBlocks;
    }

    // Projects
    $projBlocks = [];
    $currentProj = null;
    $inProj = false;

    foreach ($lines as $i => $line) {
        if (preg_match('/^projects/i', $line)) {
            $inProj = true;
            continue;
        }
        if ($inProj && preg_match('/^(education|technical\s+skills|skills|certifications)/i', $line)) {
            $inProj = false;
        }
        if (!$inProj) continue;

        if (preg_match('/^[A-Z][a-zA-Z\s]{3,60}$/', $line) && !preg_match('/^(technology|other|project)/i', $line) && strlen($line) > 5) {
            if ($currentProj) $projBlocks[] = $currentProj;
            $currentProj = ['name' => $line, 'tech' => '', 'points' => ''];
            continue;
        }
        if ($currentProj && preg_match('/^[•\-*]\s*(.+)/', $line, $m)) {
            $currentProj['points'] .= $m[1] . "\n";
        }
        if ($currentProj && preg_match('/Technology|Tech|Stack|Laravel|PHP|MySQL|REST/i', $line)) {
            $currentProj['tech'] = $line;
        }
    }
    if ($currentProj) $projBlocks[] = $currentProj;
    if (!empty($projBlocks)) {
        $data['projects'] = $projBlocks;
    }

    // Education
    if (preg_match('/(?:Bachelor|Master|B\.?\s*E\.?|M\.?\s*E\.?|B\.?\s*Tech|M\.?\s*Tech|B\.?\s*Sc|M\.?\s*Sc|Ph\.?\s*D|Diploma|BE|ME|BTech|MTech)[^,\n]*(?:,|of|in|\([^)]+\))?/i', $text, $m)) {
        $data['edu_degree'] = trim($m[0]);
    }
    if (preg_match('/(?:University|College|Institute|IT\s*\([^)]+\))[^,\n]*(?:,\s*[A-Z]+)?/i', $text, $m)) {
        $data['edu_institution'] = trim($m[0]);
    }
    if (preg_match('/\b(19|20)\d{2}\b/', $text, $m)) {
        $data['edu_year'] = $m[0];
    }

    // AI tools
    if (preg_match('/(ChatGPT|DeepSeek|Gemini|Claude|Copilot)/i', $text, $m)) {
        $tools = [];
        if (preg_match_all('/(ChatGPT|DeepSeek|Gemini|Claude|Copilot)/i', $text, $matches)) {
            $tools = array_unique($matches[0]);
        }
        $data['ai_tools'] = implode(', ', $tools);
    }

    // Other projects
    if (preg_match('/other\s+projects?:?\s*\n(.+?)(?=\n{2,}|$)/is', $text, $m)) {
        $data['other_projects'] = trim(preg_replace('/[•\-*\n]+/', ', ', $m[1]));
        $data['other_projects'] = preg_replace('/,\s*,/', ',', $data['other_projects']);
    }

    return $data;
}

function cleanSkillsLine(string $line): string
{
    $line = preg_replace('/^[:\s]*/', '', $line);
    $line = preg_replace('/:\s*/', ': ', $line);
    $line = preg_replace('/\s+/', ' ', $line);
    return trim($line);
}
