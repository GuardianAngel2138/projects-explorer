<?php
/**
 * Projects Explorer: settings loader.
 * (Named settings.php, not config.php: the installer skips every file called config.php so it never overwrites the user's own settings.)
 * Reads the defaults from config.example.php, then applies the user's config.php on top.
 */

/**
 * Load settings. Any key with the wrong type falls back to the default,
 * so a typo in config.php can't break the page.
 */
function loadConfig(string $dir): array
{
    $defaults = require $dir . '/config.example.php';
    $userFile = $dir . '/config.php';
    $user = is_file($userFile) ? require $userFile : [];

    if (!is_array($user)) {
        return $defaults;
    }

    $config = $defaults;
    foreach ($defaults as $key => $default) {
        if (array_key_exists($key, $user) && gettype($user[$key]) === gettype($default)) {
            $config[$key] = $user[$key];
        }
    }

    return $config;
}
