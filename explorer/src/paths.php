<?php
/**
 * Projects Explorer: path and URL helpers.
 * Every folder the dashboard opens goes through resolvePath(), which keeps it inside the root.
 */

/** Clean a relative path: forward slashes, no empty parts, "." removed, ".." applied. */
function normPath(string $path): string
{
    $parts = [];
    foreach (explode('/', str_replace('\\', '/', trim($path))) as $part) {
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

/** True when $path is $root or inside it. Windows paths are compared without case. */
function inRoot(string $root, string $path): bool
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    if (DIRECTORY_SEPARATOR === '\\') {
        $root = strtolower($root);
        $path = strtolower($path);
    }

    return $path === $root || strpos($path, $root . DIRECTORY_SEPARATOR) === 0;
}

/** Find the web root: the config value, Apache's document root, or the folder above this app. */
function findRoot(array $config): ?string
{
    if ($config['root'] !== 'auto') {
        $root = realpath($config['root']);
    } elseif (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $root = realpath($_SERVER['DOCUMENT_ROOT']);
    } else {
        $root = realpath(__DIR__ . '/../../..');
    }

    return ($root !== false && is_dir($root)) ? $root : null;
}

/** Turn a relative path into a real folder/file path, or null if it is missing or outside the root. */
function resolvePath(string $root, string $relative): ?string
{
    $relative = normPath($relative);
    if ($relative === '') {
        return $root;
    }

    // realpath() also follows symlinks, so the inRoot() check below catches links that point outside.
    $resolved = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
    if ($resolved === false || !inRoot($root, $resolved)) {
        return null;
    }

    return $resolved;
}

/** Path of $absolute relative to $root, with forward slashes ('' for the root itself). */
function relativePath(string $root, string $absolute): string
{
    $root = rtrim($root, DIRECTORY_SEPARATOR);
    if (strlen($absolute) <= strlen($root)) {
        return '';
    }

    return str_replace(DIRECTORY_SEPARATOR, '/', substr($absolute, strlen($root) + 1));
}

/** URL-encode each part of a path but keep the slashes. */
function encodeSegments(string $path): string
{
    $path = normPath($path);
    return $path === '' ? '' : implode('/', array_map('rawurlencode', explode('/', $path)));
}

/** URL of this dashboard (e.g. /dashboard/), worked out from the running script. */
function appUrl(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return rtrim($dir, '/') . '/';
}

/**
 * Dashboard URL that opens a folder. null means "the start folder" (no ?path).
 * An empty string means the root itself, so it gets ?path= with no value.
 */
function browseUrl(?string $relative = null): string
{
    if ($relative === null) {
        return appUrl();
    }

    return appUrl() . '?path=' . rawurlencode(normPath($relative));
}

/** Public URL of a file under the root. */
function fileUrl(string $relative): string
{
    return '/' . encodeSegments($relative);
}

/** Public URL of a folder under the root, with a trailing slash so relative links inside it work. */
function folderUrl(string $relative): string
{
    $encoded = encodeSegments($relative);
    return $encoded === '' ? '/' : '/' . $encoded . '/';
}
