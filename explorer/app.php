<?php
/**
 * Projects Explorer: app.
 * Loads settings, checks the request, works out which folder to show, then renders the page.
 */

defined('PV_ENTRY') || exit;

require __DIR__ . '/src/config.php';
require __DIR__ . '/src/guard.php';
require __DIR__ . '/src/paths.php';
require __DIR__ . '/src/detect.php';
require __DIR__ . '/src/listing.php';

$config = loadConfig(__DIR__);
guardRequest($config);

/** Escape text for HTML output. */
function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// ---- Root and start folder ----
$rootDir = findRoot($config);
if ($rootDir === null) {
    http_response_code(500);
    exit('Projects Explorer could not find the web root. Set "root" in explorer/config.php.');
}

$startRelative = $config['start'] === 'auto' ? 'Projects' : normPath($config['start']);
$startDir = resolvePath($rootDir, $startRelative);
if ($startDir === null || !is_dir($startDir)) {
    $startRelative = '';
    $startDir = $rootDir;
}
$startRelative = relativePath($rootDir, $startDir);

// ---- Folder being viewed ----
// No ?path means the start folder; ?path= (empty) means the root. Anything that
// isn't a plain string (e.g. ?path[]=x) is ignored rather than crashing the page.
$pathError = null;
$requested = $_GET['path'] ?? null;
$currentDir = $startDir;
if (is_string($requested)) {
    $resolved = resolvePath($rootDir, $requested);
    if ($resolved !== null && is_dir($resolved)) {
        $currentDir = $resolved;
    } else {
        $pathError = 'That folder could not be opened, so you are back at the start folder.';
    }
}
$currentRelative = relativePath($rootDir, $currentDir);
$isAtStart = $currentRelative === $startRelative;

// ---- Contents ----
$items = listItems($currentDir, $rootDir, $config);
$folders = array_values(array_filter($items, function (array $item): bool { return $item['is_dir']; }));
$files = array_values(array_filter($items, function (array $item): bool { return !$item['is_dir']; }));
$currentProject = detectProject($currentDir, $currentRelative);

// ---- Sidebar data ----
$rootLabel = basename($rootDir);
$startLabel = $startRelative === '' ? $rootLabel : basename($startDir);
// The parent of "a/b" is "a"; the parent of "a" is the root (''). The root has no parent (null).
$parentRelative = $currentRelative === '' ? null : normPath(dirname($currentRelative) === '.' ? '' : dirname($currentRelative));
$sidebarFolders = listItems($startDir, $rootDir, $config, true);

$pinned = [];
foreach ($config['pinned'] as $pin) {
    if (!is_string($pin)) {
        continue;
    }
    $pinDir = resolvePath($rootDir, $pin);
    if ($pinDir !== null && is_dir($pinDir)) {
        $pinned[] = ['name' => basename($pinDir), 'relative' => relativePath($rootDir, $pinDir)];
    }
}

/** True when the current folder is $relative or inside it (for highlighting sidebar links). */
function isCurrentOrInside(string $current, string $relative): bool
{
    return $current === $relative || strpos($current, $relative . '/') === 0;
}

// ---- Breadcrumbs: root first, then each folder down to the current one ----
$crumbs = [['label' => $rootLabel, 'url' => browseUrl('')]];
$walk = '';
foreach ($currentRelative === '' ? [] : explode('/', $currentRelative) as $part) {
    $walk = $walk === '' ? $part : $walk . '/' . $part;
    $crumbs[] = ['label' => $part, 'url' => browseUrl($walk)];
}

$heading = $currentRelative === '' ? $rootLabel : basename($currentDir);
$updated = date('d M Y, h:i A', latestTime($currentDir));
$assetUrl = appUrl() . 'explorer/assets/';
$assetVersion = '2.0.0';

require __DIR__ . '/views/layout.php';
