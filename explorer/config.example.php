<?php
/**
 * Projects Explorer: settings template.
 *
 * To change a setting, copy this file to config.php (same folder) and edit the copy.
 * config.php is never replaced by the installer, so your settings survive updates.
 * You only need to keep the lines you change; anything missing uses the default shown here.
 */
return [
    // Folder whose contents the dashboard can browse. 'auto' uses the web root (htdocs).
    // If you set a path, it must be the folder Apache serves at http://localhost/,
    // otherwise the "Open site" links will point to the wrong place.
    'root' => 'auto',

    // Folder (inside root) the dashboard opens on. 'auto' opens "Projects" if that
    // folder exists, otherwise the root itself. Example: 'work/clients'
    'start' => 'auto',

    // Folders (inside root) listed at the top of the sidebar for quick access.
    // Example: ['work/clients', 'personal/blog']
    'pinned' => [],

    // Names hidden in the root folder only (XAMPP's own files and folders).
    'hide_in_root' => ['dashboard', 'xampp', 'img', 'webalizer', 'applications.html', 'bitnami.css', 'favicon.ico', 'index.php'],

    // Names hidden in every folder.
    'hide_everywhere' => ['node_modules', 'vendor'],

    // Show files and folders whose name starts with a dot (.git, .env, .vscode).
    'show_dotfiles' => false,

    // Files that are listed but never get an "Open file" link, because they often
    // hold passwords or customer data. Patterns use * as a wildcard.
    'no_link_patterns' => ['.env*', '*.sql', '*.sqlite', '*.db', '*.key', '*.pem', '*.bak', '*.log'],

    // Links shown under "Tools" in the sidebar. Label => URL.
    'tools' => [
        'phpMyAdmin' => '/phpmyadmin/',
        'PHP info'   => '/dashboard/phpinfo.php',
    ],

    // The dashboard lists every file in htdocs, so by default it only answers
    // requests from this computer. Set to true only if you understand that anyone
    // who can reach your Apache could then browse your folders.
    'allow_remote' => false,

    // Name shown at the top of the sidebar and in the browser tab.
    'title' => 'Projects Explorer',
];
