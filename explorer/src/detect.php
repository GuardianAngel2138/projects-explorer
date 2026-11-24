<?php
/**
 * Projects Explorer: project-type detection.
 * Looks at the files in a folder to decide what "Open site" should open.
 */

/** Files that make a folder open as a site, in Apache's usual order. */
const ENTRY_FILES = ['index.php', 'index.html', 'index.htm', 'default.php', 'default.html'];

/**
 * Work out what kind of project a folder holds.
 * Returns ['type' => label or '', 'url' => link or null, 'note' => text or null].
 * The checks run from most specific to least, so a Laravel app isn't treated
 * as a plain folder just because it also has an index.php somewhere.
 */
function detectProject(string $dir, string $relative): array
{
    $has = function (string $path) use ($dir): bool {
        return file_exists($dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path));
    };
    $sub = function (string $path) use ($relative): string {
        return trim($relative . '/' . $path, '/');
    };
    $result = function (string $type, ?string $url, ?string $note = null): array {
        return ['type' => $type, 'url' => $url, 'note' => $note];
    };

    // Laravel serves from public/; opening the project folder itself shows a file list or an error.
    if ($has('artisan') && $has('public/index.php')) {
        return $result('Laravel', folderUrl($sub('public')));
    }

    if ($has('wp-config.php') || $has('wp-load.php')) {
        return $result('WordPress', folderUrl($relative));
    }

    // A Vite project's root index.html is source code and doesn't work without the dev server.
    foreach (['vite.config.js', 'vite.config.ts', 'vite.config.mjs', 'vite.config.cjs'] as $viteFile) {
        if ($has($viteFile)) {
            return $has('dist/index.html')
                ? $result('Vite build', folderUrl($sub('dist')))
                : $result('Vite', null, 'Run npm run build or npm run dev to see it');
        }
    }

    foreach (ENTRY_FILES as $file) {
        if ($has($file)) {
            // index.* opens through the folder URL; default.* only works when linked directly.
            $url = strpos($file, 'index.') === 0 ? folderUrl($relative) : fileUrl($sub($file));
            return $result('', $url);
        }
    }

    foreach (['dist', 'build'] as $buildDir) {
        if ($has($buildDir . '/index.html')) {
            return $result('Built site', folderUrl($sub($buildDir)));
        }
    }

    if ($has('public/index.php') || $has('public/index.html')) {
        return $result('', folderUrl($sub('public')));
    }

    if ($has('package.json')) {
        return $result('Node', null, 'Needs its dev server (npm run dev)');
    }

    return $result('', null);
}
