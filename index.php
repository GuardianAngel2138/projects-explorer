<?php
$rootDir = realpath(__DIR__ . '/..');
$entryFiles = ['index.php', 'index.html', 'index.htm', 'default.php', 'default.html'];
$pathError = null;
$mainRelative = 'Projects';

if ($rootDir === false) {
    http_response_code(500);
    exit('Unable to resolve the htdocs root.');
}

function normPath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $parts = [];

    foreach (explode('/', $path) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }

    return implode('/', $parts);
}

function inRoot(string $root, string $path): bool
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    return $path === $root || strpos($path, $root . DIRECTORY_SEPARATOR) === 0;
}

function resolvePath(string $root, string $relative): ?string
{
    $relative = normPath($relative);
    if ($relative === '') {
        return $root;
    }

    $candidate = $root;
    foreach (explode('/', $relative) as $part) {
        $candidate .= DIRECTORY_SEPARATOR . $part;
    }

    $resolved = realpath($candidate);
    if ($resolved === false || !inRoot($root, $resolved)) {
        return null;
    }

    return $resolved;
}

function relativePath(string $root, string $absolute): string
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    if ($absolute === $root) {
        return '';
    }

    return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolute, strlen($root) + 1));
}

function encodeSegments(string $path): string
{
    $path = normPath($path);
    if ($path === '') {
        return '';
    }

    return implode('/', array_map('rawurlencode', explode('/', $path)));
}

function dashUrl(string $path = ''): string
{
    $path = normPath($path);
    return $path === '' ? '/dashboard/' : '/dashboard/?path=' . rawurlencode($path);
}

function publicUrlPath(string $path = ''): string
{
    return '/' . encodeSegments($path);
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function latestTime(string $path): int
{
    $latest = @filemtime($path);
    if ($latest === false) {
        return time();
    }
    if (!is_dir($path)) {
        return $latest;
    }

    $items = @scandir($path);
    if ($items === false) {
        return $latest;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $mtime = @filemtime($path . DIRECTORY_SEPARATOR . $item);
        if ($mtime !== false && $mtime > $latest) {
            $latest = $mtime;
        }
    }

    return $latest;
}

function activityTag(string $path): string
{
    $diff = time() - latestTime($path);
    if ($diff < 172800) {
        return 'active';
    }
    if ($diff < 604800) {
        return 'recent';
    }
    return '';
}

function entryFile(string $directory, array $entryFiles): ?string
{
    foreach ($entryFiles as $file) {
        if (is_file($directory . DIRECTORY_SEPARATOR . $file)) {
            return $file;
        }
    }
    return null;
}

function itemCount(string $directory): int
{
    $items = @scandir($directory);
    if ($items === false) {
        return 0;
    }

    $count = 0;
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            $count++;
        }
    }

    return $count;
}

function formatBytes(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $size = max($bytes, 0);
    $power = 0;

    while ($size >= 1024 && $power < count($units) - 1) {
        $size /= 1024;
        $power++;
    }

    return number_format($size, $power === 0 ? 0 : 1) . ' ' . $units[$power];
}

function itemData(string $root, string $absolute, array $entryFiles): array
{
    $isDir = is_dir($absolute);
    $relative = relativePath($root, $absolute);
    $name = basename($absolute);
    $updated = date('d M Y, h:i A', latestTime($absolute));
    $entryUrl = null;
    $browseUrl = $isDir ? dashUrl($relative) : publicUrlPath($relative);

    if ($isDir) {
        $entry = entryFile($absolute, $entryFiles);
        if ($entry !== null) {
            $entryUrl = publicUrlPath(trim($relative . '/' . $entry, '/'));
        }
        $meta = 'Folder - ' . itemCount($absolute) . ' items - Updated ' . $updated;
        $action = 'Browse folder';
        $kind = 'Folder';
    } else {
        $size = @filesize($absolute);
        $ext = strtoupper((string) pathinfo($name, PATHINFO_EXTENSION));
        $meta = ($ext !== '' ? $ext : 'FILE') . ' - ' . ($size !== false ? formatBytes((int) $size) : 'Unknown size') . ' - Updated ' . $updated;
        $action = 'Open file';
        $kind = 'File';
    }

    return [
        'name' => $name,
        'relative' => $relative,
        'is_dir' => $isDir,
        'browse_url' => $browseUrl,
        'entry_url' => $entryUrl,
        'meta' => $meta,
        'kind' => $kind,
        'action' => $action,
        'activity' => activityTag($absolute),
    ];
}

function listItems(string $directory, string $root, array $entryFiles): array
{
    $items = @scandir($directory);
    if ($items === false) {
        return [];
    }

    $result = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $result[] = itemData($root, $directory . DIRECTORY_SEPARATOR . $item, $entryFiles);
    }

    usort($result, function (array $left, array $right): int {
        if ($left['is_dir'] !== $right['is_dir']) {
            return $left['is_dir'] ? -1 : 1;
        }
        return strnatcasecmp($left['name'], $right['name']);
    });

    return $result;
}

function crumbs(string $relative): array
{
    $items = [['label' => 'htdocs', 'url' => dashUrl()]];
    $relative = normPath($relative);
    if ($relative === '') {
        return $items;
    }

    $path = '';
    foreach (explode('/', $relative) as $part) {
        $path = $path === '' ? $part : $path . '/' . $part;
        $items[] = ['label' => $part, 'url' => dashUrl($path)];
    }

    return $items;
}

function menuActive(string $current, string $item): bool
{
    if ($item === '') {
        return $current === '';
    }
    return $current === $item || strpos($current, $item . '/') === 0;
}

$mainDir = resolvePath($rootDir, $mainRelative);
if ($mainDir === null || !is_dir($mainDir)) {
    $mainRelative = '';
    $mainDir = $rootDir;
}

$currentRelative = normPath($_GET['path'] ?? $mainRelative);
$currentDir = resolvePath($rootDir, $currentRelative);

if ($currentDir === null || !is_dir($currentDir)) {
    $pathError = 'That folder could not be opened, so the dashboard returned to the main workspace.';
    $currentRelative = $mainRelative;
    $currentDir = $mainDir;
}
$items = listItems($currentDir, $rootDir, $entryFiles);
$folders = [];
$files = [];
foreach ($items as $item) {
    if ($item['is_dir']) {
        $folders[] = $item;
    } else {
        $files[] = $item;
    }
}

$sidebarRootLabel = $mainRelative !== '' ? $mainRelative : 'htdocs';
$topLevel = listItems($mainDir, $rootDir, $entryFiles);
$currentEntry = entryFile($currentDir, $entryFiles);
$currentEntryUrl = $currentEntry !== null ? publicUrlPath(trim($currentRelative . '/' . $currentEntry, '/')) : null;
$currentFolderUrl = $currentRelative === '' ? '/' : publicUrlPath($currentRelative);
$parentRelative = $currentRelative === '' ? '' : dirname($currentRelative);
if ($parentRelative === '.') {
    $parentRelative = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Projects Explorer</title>
<style>
:root {
    --bg: #f3f6fb;
    --sidebar: #fbfdff;
    --surface: #ffffff;
    --surface-soft: #f7f9fc;
    --text: #162236;
    --muted: #66768d;
    --border: #d8e1ec;
    --accent: #2563eb;
    --accent-soft: #e7efff;
    --folder: #d97706;
    --folder-soft: #fff3df;
    --file: #475569;
    --file-soft: #eef2f6;
}
body.dark {
    --bg: #0e1522;
    --sidebar: #111a28;
    --surface: #162232;
    --surface-soft: #0f1827;
    --text: #edf3fb;
    --muted: #94a3b8;
    --border: #253348;
    --accent: #7db7ff;
    --accent-soft: rgba(125, 183, 255, 0.12);
    --folder: #f4b25d;
    --folder-soft: rgba(244, 178, 93, 0.12);
    --file: #c2cfdf;
    --file-soft: rgba(194, 207, 223, 0.12);
}
* { box-sizing: border-box; }
body {
    margin: 0;
    min-height: 100vh;
    font-family: "Segoe UI", Tahoma, sans-serif;
    background: var(--bg);
    color: var(--text);
}
a, button, input { font: inherit; }
a { color: inherit; }
button { cursor: pointer; }
.shell {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    min-height: 100vh;
}
.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
    padding: 22px;
    background: var(--sidebar);
    border-right: 1px solid var(--border);
}
.brand {
    margin-bottom: 24px;
}
.brand h1 {
    margin: 0;
    font-size: 24px;
}
.brand p,
.helper,
.meta,
.item-path,
.empty,
.section-copy,
.notice,
.sidebar-copy,
.breadcrumbs,
.stat,
.side-kind {
    color: var(--muted);
}
.sidebar-copy {
    margin-top: 8px;
    line-height: 1.6;
}
.sidebar-block {
    margin-top: 22px;
}
.sidebar-title {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.sidebar-links {
    display: grid;
    gap: 8px;
}
.sidebar-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 11px 12px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    text-decoration: none;
}
.sidebar-link:hover,
.sidebar-link.active {
    border-color: var(--accent);
    background: var(--accent-soft);
}
.sidebar-link-main {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}
.sidebar-link-main span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.side-kind {
    font-size: 12px;
    text-transform: uppercase;
}
.main {
    padding: 24px;
}
.header,
.section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 18px;
}
.header {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    gap: 18px;
    align-items: flex-start;
}
.header-copy {
    max-width: 760px;
}
.header-copy h2,
.section h3 {
    margin: 0;
}
.header-copy p {
    margin: 10px 0 0;
    line-height: 1.6;
}
.breadcrumbs {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
    font-size: 14px;
}
.breadcrumbs a {
    text-decoration: none;
}
.summary {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 14px;
}
.stat {
    padding: 8px 12px;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: var(--surface-soft);
    font-size: 13px;
}
.toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: flex-end;
    align-items: center;
}
.search {
    width: 300px;
    max-width: 100%;
    padding: 11px 13px;
    border: 1px solid var(--border);
    border-radius: 12px;
    background: var(--surface-soft);
    color: var(--text);
}
.button,
.subtle-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 14px;
    border-radius: 12px;
    border: 1px solid var(--border);
    text-decoration: none;
    background: var(--surface);
}
.button {
    border-color: var(--accent);
    background: var(--accent);
    color: #ffffff;
}
.notice {
    margin-top: 16px;
    padding: 12px 14px;
    border: 1px solid #e8c7c7;
    border-radius: 12px;
    background: #fff6f6;
}
body.dark .notice {
    background: rgba(255, 120, 120, 0.08);
    border-color: rgba(255, 120, 120, 0.24);
}
.page {
    display: grid;
    gap: 16px;
    margin-top: 16px;
}
.section {
    padding: 20px;
}
.section-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
}
.section-copy {
    margin-top: 6px;
    line-height: 1.5;
}
.items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
}
.explorer-tile {
    display: flex;
    flex-direction: column;
    gap: 14px;
    min-height: 220px;
    padding: 16px;
    border: 1px solid var(--border);
    border-radius: 16px;
    background: var(--surface-soft);
}
.explorer-tile:hover {
    border-color: var(--accent);
}
.tile-link {
    display: block;
    flex: 1;
    min-width: 0;
    padding: 2px;
    border-radius: 12px;
    text-decoration: none;
}
.tile-link:hover .row-title,
.breadcrumbs a:hover {
    color: var(--accent);
}
.tile-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.row-main {
    min-width: 0;
}
.row-heading {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.row-title {
    font-size: 16px;
    font-weight: 600;
}
.kind-pill {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}
.kind-pill.folder {
    background: var(--folder-soft);
    color: var(--folder);
}
.kind-pill.file {
    background: var(--file-soft);
    color: var(--file);
}
.row-icon {
    position: relative;
    width: 38px;
    height: 30px;
    flex: 0 0 auto;
}
.row-icon.folder::before {
    content: '';
    position: absolute;
    left: 0;
    top: 7px;
    width: 100%;
    height: 23px;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 7px;
    background: var(--folder-soft);
}
.row-icon.folder::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 2px;
    width: 16px;
    height: 9px;
    border-radius: 5px 5px 0 0;
    background: #ffd9a3;
}
body.dark .row-icon.folder::after {
    background: rgba(244, 178, 93, 0.55);
}
.row-icon.file::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 1px;
    width: 24px;
    height: 28px;
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 6px;
    background: var(--file-soft);
}
.row-icon.file::after {
    content: '';
    position: absolute;
    right: 8px;
    top: 1px;
    width: 10px;
    height: 10px;
    background: linear-gradient(135deg, transparent 50%, rgba(255,255,255,0.8) 50%);
    border-top-right-radius: 5px;
}
.meta,
.item-path {
    margin-top: 5px;
    font-size: 13px;
    line-height: 1.5;
    word-break: break-word;
}
.row-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-start;
    margin-top: auto;
}
.empty {
    padding: 16px;
    border: 1px dashed var(--border);
    border-radius: 14px;
    background: var(--surface-soft);
}
.hidden {
    display: none;
}
@media (max-width: 980px) {
    .shell {
        grid-template-columns: 1fr;
    }
    .sidebar {
        position: static;
        height: auto;
        border-right: 0;
        border-bottom: 1px solid var(--border);
    }
}
@media (max-width: 760px) {
    .main {
        padding: 16px;
    }
    .header {
        display: block;
    }
    .toolbar {
        justify-content: flex-start;
        margin-top: 16px;
    }
    .search {
        width: 100%;
    }
    .row-actions {
        justify-content: flex-start;
    }
}
</style>
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <h1>Projects Explorer</h1>
            <div class="sidebar-copy">A folder-first view of your `Projects` workspace with a fixed sidebar for quick navigation.</div>
        </div>

        <div class="sidebar-block">
            <div class="sidebar-title">Workspace</div>
            <div class="sidebar-links">
                <a class="sidebar-link <?php echo $currentRelative === $mainRelative ? 'active' : ''; ?>" href="/dashboard/">
                    <div class="sidebar-link-main">
                        <div class="row-icon folder"></div>
                        <span>Projects home</span>
                    </div>
                    <small class="side-kind">main</small>
                </a>
                <?php if ($currentRelative !== $mainRelative): ?>
                    <a class="sidebar-link" href="<?php echo esc(dashUrl($parentRelative)); ?>">
                        <div class="sidebar-link-main">
                            <div class="row-icon folder"></div>
                            <span>Go up one folder</span>
                        </div>
                        <small class="side-kind">up</small>
                    </a>
                <?php endif; ?>
                <a class="sidebar-link" href="<?php echo esc(dashUrl()); ?>">
                    <div class="sidebar-link-main">
                        <div class="row-icon file"></div>
                        <span>htdocs root</span>
                    </div>
                    <small class="side-kind">other</small>
                </a>
            </div>
        </div>

        <div class="sidebar-block">
            <div class="sidebar-title">Top Level In <?php echo esc($sidebarRootLabel); ?></div>
            <div class="sidebar-links">
                <?php foreach ($topLevel as $item): ?>
                    <a class="sidebar-link <?php echo menuActive($currentRelative, $item['relative']) ? 'active' : ''; ?>" href="<?php echo esc($item['browse_url']); ?>">
                        <div class="sidebar-link-main">
                            <div class="row-icon <?php echo $item['is_dir'] ? 'folder' : 'file'; ?>"></div>
                            <span><?php echo esc($item['name']); ?></span>
                        </div>
                        <small class="side-kind"><?php echo esc(strtolower($item['kind'])); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

    <main class="main">
        <section class="header">
            <div class="header-copy">
                <h2><?php echo esc($currentRelative !== '' ? $currentRelative : $sidebarRootLabel); ?></h2>
                <div class="breadcrumbs">
                    <?php foreach (crumbs($currentRelative) as $index => $crumb): ?>
                        <?php if ($index > 0): ?><span>/</span><?php endif; ?>
                        <a href="<?php echo esc($crumb['url']); ?>"><?php echo esc($crumb['label']); ?></a>
                    <?php endforeach; ?>
                </div>
                <p class="helper">Folders come first, files stay separate, and each row now reads more like a real file browser.</p>
                <div class="summary">
                    <span class="stat"><?php echo esc(count($folders) . ' folders'); ?></span>
                    <span class="stat"><?php echo esc(count($files) . ' files'); ?></span>
                    <span class="stat"><?php echo esc('Updated ' . date('d M Y, h:i A', latestTime($currentDir))); ?></span>
                </div>
            </div>
            <div class="toolbar">
                <input id="q" class="search" type="search" placeholder="Search this folder" autocomplete="off">
                <button type="button" class="subtle-button" data-theme-toggle>Toggle theme</button>
                <?php if ($currentEntryUrl !== null && $currentRelative !== ''): ?>
                    <a class="button" href="<?php echo esc($currentEntryUrl); ?>" target="_blank" rel="noreferrer">Open site</a>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($pathError !== null): ?>
            <div class="notice"><?php echo esc($pathError); ?></div>
        <?php endif; ?>

        <div class="page">
            <section class="section">
                <div class="section-head">
                    <div>
                        <h3>Folders</h3>
                        <div class="section-copy">Use these to keep moving around inside the dashboard.</div>
                    </div>
                    <div class="stat"><?php echo esc((string) count($folders)); ?> items</div>
                </div>
                <?php if (!empty($folders)): ?>
                    <div class="items">
                        <?php foreach ($folders as $item): ?>
                            <article class="explorer-tile" data-item data-search="<?php echo esc(strtolower($item['name'] . ' ' . $item['relative'] . ' folder')); ?>">
                                <a class="tile-link" href="<?php echo esc($item['browse_url']); ?>">
                                    <div class="tile-head">
                                        <div class="row-icon folder"></div>
                                        <div class="row-main">
                                            <div class="row-heading">
                                                <span class="row-title"><?php echo esc($item['name']); ?></span>
                                                <span class="kind-pill folder">Folder</span>
                                            </div>
                                            <div class="meta"><?php echo esc($item['meta']); ?></div>
                                            <div class="item-path">/<?php echo esc($item['relative']); ?></div>
                                        </div>
                                    </div>
                                </a>
                                <div class="row-actions">
                                    <a class="subtle-button" href="<?php echo esc($item['browse_url']); ?>">Browse</a>
                                    <?php if (!empty($item['entry_url'])): ?>
                                        <a class="button" href="<?php echo esc($item['entry_url']); ?>" target="_blank" rel="noreferrer">Open site</a>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty">No folders in this location.</div>
                <?php endif; ?>
            </section>

            <section class="section">
                <div class="section-head">
                    <div>
                        <h3>Files</h3>
                        <div class="section-copy">Open individual files directly from here.</div>
                    </div>
                    <div class="stat"><?php echo esc((string) count($files)); ?> items</div>
                </div>
                <?php if (!empty($files)): ?>
                    <div class="items">
                        <?php foreach ($files as $item): ?>
                            <article class="explorer-tile" data-item data-search="<?php echo esc(strtolower($item['name'] . ' ' . $item['relative'] . ' file')); ?>">
                                <a class="tile-link" href="<?php echo esc($item['browse_url']); ?>">
                                    <div class="tile-head">
                                        <div class="row-icon file"></div>
                                        <div class="row-main">
                                            <div class="row-heading">
                                                <span class="row-title"><?php echo esc($item['name']); ?></span>
                                                <span class="kind-pill file">File</span>
                                            </div>
                                            <div class="meta"><?php echo esc($item['meta']); ?></div>
                                            <div class="item-path">/<?php echo esc($item['relative']); ?></div>
                                        </div>
                                    </div>
                                </a>
                                <div class="row-actions">
                                    <a class="button" href="<?php echo esc($item['browse_url']); ?>">Open file</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty">No files in this location.</div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>
<script>
const themeKey = 'dev-dashboard-theme';
const searchInput = document.getElementById('q');

function applySavedTheme() {
    if (localStorage.getItem(themeKey) === 'dark') {
        document.body.classList.add('dark');
    }
}

function toggleTheme() {
    document.body.classList.toggle('dark');
    localStorage.setItem(themeKey, document.body.classList.contains('dark') ? 'dark' : 'light');
}

function filterItems() {
    const query = searchInput.value.trim().toLowerCase();
    document.querySelectorAll('[data-item]').forEach(function (item) {
        const haystack = item.dataset.search || '';
        item.classList.toggle('hidden', query !== '' && haystack.indexOf(query) === -1);
    });
}

document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-theme-toggle]');
    if (button) {
        event.preventDefault();
        toggleTheme();
    }
});

searchInput.addEventListener('input', filterItems);
applySavedTheme();
</script>
</body>
</html>


