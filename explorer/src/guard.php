<?php
/**
 * Projects Explorer: request guard.
 * Blocks visitors from other computers and sends safe response headers.
 */

/** True when the request comes from this computer. */
function isLocalRequest(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return $ip === '::1'
        || strpos($ip, '127.') === 0
        || strpos($ip, '::ffff:127.') === 0;
}

/** Stop with a 403 page unless the request is local or remote access is switched on. */
function guardRequest(array $config): void
{
    // Folder names can be private (client names), so don't send them to other sites
    // in the Referer header, and don't let other sites frame or cache the page.
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store');

    if ($config['allow_remote'] || isLocalRequest()) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Projects Explorer only opens on the computer it is installed on.\n";
    echo "To allow other computers, set 'allow_remote' => true in dashboard/explorer/config.php.\n";
    exit;
}
