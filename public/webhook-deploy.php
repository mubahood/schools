<?php
/**
 * GitHub webhook auto-deploy handler
 * Set this URL in GitHub repo Settings → Webhooks
 * Secret: use the value of DEPLOY_SECRET defined below
 */

$DEPLOY_SECRET = 'SchoolDyn@mics!Deploy#2026';
$APP_ROOT      = '/home4/schooics/public_html';
$BRANCH        = 'master';
$REPO_ZIP      = "https://github.com/mubahood/schools/archive/refs/heads/{$BRANCH}.zip";
$TEMP_DIR      = '/tmp/schools_deploy_' . time();

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method Not Allowed\n");
}

// Verify GitHub signature
$payload   = file_get_contents('php://input');
$signature = 'sha256=' . hash_hmac('sha256', $payload, $DEPLOY_SECRET);
$received  = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!hash_equals($signature, $received)) {
    http_response_code(403);
    exit("Signature mismatch\n");
}

$event = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
if ($event !== 'push') {
    exit("Event '{$event}' ignored\n");
}

$data = json_decode($payload, true);
if (($data['ref'] ?? '') !== "refs/heads/{$BRANCH}") {
    exit("Branch not {$BRANCH}, skipping\n");
}

// Download the zip from GitHub
$zipPath = "{$TEMP_DIR}.zip";
$ch = curl_init($REPO_ZIP);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_USERAGENT      => 'SchoolDynamics-Deploy/1.0',
]);
$zipContent = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($zipContent)) {
    http_response_code(500);
    exit("Failed to download repo zip (HTTP {$httpCode})\n");
}

file_put_contents($zipPath, $zipContent);

// Extract zip
$zip = new ZipArchive();
if ($zip->open($zipPath) !== true) {
    http_response_code(500);
    exit("Failed to open zip\n");
}
mkdir($TEMP_DIR, 0755, true);
$zip->extractTo($TEMP_DIR);
$zip->close();
unlink($zipPath);

// GitHub zip extracts to a subfolder named "<repo>-<branch>"
$extractedDir = glob("{$TEMP_DIR}/schools-{$BRANCH}")[0] ?? null;
if (!$extractedDir || !is_dir($extractedDir)) {
    // Fallback: find any subdirectory
    $dirs = glob("{$TEMP_DIR}/*/", GLOB_ONLYDIR);
    $extractedDir = $dirs[0] ?? null;
}

if (!$extractedDir) {
    http_response_code(500);
    exit("Could not find extracted directory in " . implode(', ', glob("{$TEMP_DIR}/*")) . "\n");
}

// Paths to preserve on server (never overwrite from zip)
$preserve = ['.env', 'storage/app', 'storage/logs', 'public/storage'];

// Copy files from extracted zip to app root, skipping preserved paths
function rsyncDirs(string $src, string $dst, array $skip): void {
    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    ) as $item) {
        $rel = ltrim(str_replace($src, '', $item->getPathname()), '/');

        foreach ($skip as $s) {
            if (str_starts_with($rel, $s)) continue 2;
        }

        $target = "{$dst}/{$rel}";
        if ($item->isDir()) {
            if (!is_dir($target)) mkdir($target, 0755, true);
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

rsyncDirs($extractedDir, $APP_ROOT, $preserve);

// Clean up temp dir
exec("rm -rf " . escapeshellarg($TEMP_DIR));

// Run artisan commands
$commands = [
    "php artisan migrate --force",
    "php artisan config:cache",
    "php artisan view:cache",
    "php artisan queue:restart",
];

echo "Deploy started at " . date('Y-m-d H:i:s') . "\n";

foreach ($commands as $cmd) {
    $output = [];
    exec("cd " . escapeshellarg($APP_ROOT) . " && {$cmd} 2>&1", $output);
    echo implode("\n", $output) . "\n";
}

echo "Deploy complete at " . date('Y-m-d H:i:s') . "\n";
