<?php
/**
 * Projects Explorer: page layout.
 * The whole HTML page: head, sidebar, header, then the folder and file sections.
 * Variables come from app.php.
 */

defined('PV_ENTRY') || exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc($heading . ' · ' . $config['title']); ?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath d='M3 9a3 3 0 0 1 3-3h7l3 3h10a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3z' fill='%232563eb'/%3E%3C/svg%3E">
<link rel="stylesheet" href="<?php echo esc($assetUrl . 'css/explorer.css?v=' . $assetVersion); ?>">
<script>
// Apply the saved theme before the page paints, so it doesn't flash light then dark.
try {
    var savedTheme = localStorage.getItem('pv-theme');
    if (savedTheme === 'light' || savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }
} catch (e) {}
</script>
<script src="<?php echo esc($assetUrl . 'js/explorer.js?v=' . $assetVersion); ?>" defer></script>
</head>
<body class="pv-body">
<a class="pv-skip" href="#pv-content">Skip to folder contents</a>

<div class="pv-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="pv-main" id="pv-content" tabindex="-1">
        <?php require __DIR__ . '/header.php'; ?>

        <?php if ($pathError !== null): ?>
            <p class="pv-notice" role="alert"><?php echo esc($pathError); ?></p>
        <?php endif; ?>

        <p class="pv-notice pv-notice--info is-hidden" data-search-empty role="status">Nothing in this folder matches your search.</p>

        <div class="pv-sections">
            <?php
            $sectionTitle = 'Folders';
            $sectionItems = $folders;
            $sectionEmpty = 'No folders here.';
            require __DIR__ . '/section.php';

            $sectionTitle = 'Files';
            $sectionItems = $files;
            $sectionEmpty = 'No files here.';
            require __DIR__ . '/section.php';
            ?>
        </div>
    </main>
</div>
</body>
</html>
