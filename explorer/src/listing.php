<?php
/**
 * Projects Explorer: folder listing.
 * Reads a folder, applies the hide rules and builds the data each tile shows.
 */

/** Newest modified time of a path, looking one level into folders (edits usually happen inside). */
function latestTime(string $path): int
{
    $latest = @filemtime($path) ?: time();
    if (!is_dir($path)) {
        return $latest;
    }

    foreach (@scandir($path) ?: [] as $item) {
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

/** 'active' if changed in the last 2 days, 'recent' if in the last week, otherwise ''. */
function activityTag(int $time): string
{
    $age = time() - $time;
    if ($age < 2 * 86400) {
        return 'active';
    }
    return $age < 7 * 86400 ? 'recent' : '';
}

/** Number of entries in a folder. */
function itemCount(string $dir): int
{
    $items = @scandir($dir);
    return $items === false ? 0 : count($items) - 2;
}

/** Human-readable file size. */
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

/** Case-insensitive check of a name against a list of names. */
function nameInList(string $name, array $list): bool
{
    return in_array(strtolower($name), array_map('strtolower', $list), true);
}

/** True when a file or folder should not be listed. */
function isHidden(string $name, bool $inRootFolder, array $config): bool
{
    if ($name[0] === '.' && !$config['show_dotfiles']) {
        return true;
    }
    if (nameInList($name, $config['hide_everywhere'])) {
        return true;
    }

    return $inRootFolder && nameInList($name, $config['hide_in_root']);
}

/** True when a file must not get an "Open file" link (it may hold secrets). */
function isNoLink(string $name, array $config): bool
{
    foreach ($config['no_link_patterns'] as $pattern) {
        if (fnmatch(strtolower($pattern), strtolower($name))) {
            return true;
        }
    }

    return false;
}

/** Everything a tile needs to show one file or folder. */
function itemData(string $root, string $absolute, array $config): array
{
    $isDir = is_dir($absolute);
    $relative = relativePath($root, $absolute);
    $name = basename($absolute);
    $time = latestTime($absolute);
    $updated = 'Updated ' . date('d M Y, h:i A', $time);

    if ($isDir) {
        $project = detectProject($absolute, $relative);
        $count = itemCount($absolute);
        $meta = $count . ($count === 1 ? ' item' : ' items') . ' · ' . $updated;
        $openUrl = $project['url'];
        $noLink = false;
    } else {
        $project = ['type' => '', 'url' => null, 'note' => null];
        $size = @filesize($absolute);
        $ext = strtoupper(pathinfo($name, PATHINFO_EXTENSION)) ?: 'FILE';
        $meta = $ext . ' · ' . ($size !== false ? formatBytes($size) : 'Unknown size') . ' · ' . $updated;
        $noLink = isNoLink($name, $config);
        $openUrl = $noLink ? null : fileUrl($relative);
    }

    return [
        'name'     => $name,
        'relative' => $relative,
        'is_dir'   => $isDir,
        'browse'   => $isDir ? browseUrl($relative) : null,
        'open'     => $openUrl,
        'no_link'  => $noLink,
        'type'     => $project['type'],
        'note'     => $project['note'],
        'meta'     => $meta,
        'activity' => activityTag($time),
    ];
}

/** List a folder: visible items only, folders first, then natural name order. */
function listItems(string $dir, string $root, array $config, bool $foldersOnly = false): array
{
    $inRootFolder = rtrim($dir, DIRECTORY_SEPARATOR) === rtrim($root, DIRECTORY_SEPARATOR);
    $result = [];

    foreach (@scandir($dir) ?: [] as $name) {
        if ($name === '.' || $name === '..' || isHidden($name, $inRootFolder, $config)) {
            continue;
        }
        $absolute = $dir . DIRECTORY_SEPARATOR . $name;
        if ($foldersOnly && !is_dir($absolute)) {
            continue;
        }
        $result[] = itemData($root, $absolute, $config);
    }

    usort($result, function (array $a, array $b): int {
        if ($a['is_dir'] !== $b['is_dir']) {
            return $a['is_dir'] ? -1 : 1;
        }
        return strnatcasecmp($a['name'], $b['name']);
    });

    return $result;
}
