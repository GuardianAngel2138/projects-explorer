<?php
/**
 * Projects Explorer: page header.
 * Current folder name, breadcrumbs, counts, search, theme switch and "Open site" for this folder.
 */

defined('PV_ENTRY') || exit;
?>
<header class="pv-header">
    <div class="pv-header__copy">
        <h1 class="pv-header__title"><?php echo esc($heading); ?></h1>

        <?php /* At the root the trail would only repeat the heading, so it starts one level down. */ ?>
        <?php if (count($crumbs) > 1): ?>
        <nav aria-label="Breadcrumb">
            <ol class="pv-crumbs">
                <?php foreach ($crumbs as $index => $crumb): ?>
                    <li class="pv-crumbs__item">
                        <?php if ($index === count($crumbs) - 1): ?>
                            <span aria-current="page"><?php echo esc($crumb['label']); ?></span>
                        <?php else: ?>
                            <a href="<?php echo esc($crumb['url']); ?>"><?php echo esc($crumb['label']); ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>

        <ul class="pv-stats" aria-label="Folder summary">
            <li class="pv-stat"><?php echo esc(count($folders) . (count($folders) === 1 ? ' folder' : ' folders')); ?></li>
            <li class="pv-stat"><?php echo esc(count($files) . (count($files) === 1 ? ' file' : ' files')); ?></li>
            <li class="pv-stat">Updated <?php echo esc($updated); ?></li>
            <?php if ($currentProject['type'] !== ''): ?>
                <li class="pv-stat"><?php echo esc($currentProject['type']); ?></li>
            <?php endif; ?>
        </ul>
        <?php if ($currentProject['note'] !== null): ?>
            <p class="pv-header__note"><?php echo esc($currentProject['note']); ?></p>
        <?php endif; ?>
    </div>

    <div class="pv-toolbar">
        <label class="pv-sr-only" for="pv-search">Search this folder</label>
        <input id="pv-search" class="pv-search" type="search" placeholder="Search this folder" autocomplete="off" data-search>
        <button type="button" class="pv-btn" data-theme-toggle aria-label="Switch to dark theme">Dark theme</button>
        <?php if ($currentProject['url'] !== null && $currentRelative !== ''): ?>
            <a class="pv-btn pv-btn--primary" href="<?php echo esc($currentProject['url']); ?>" target="_blank" rel="noopener noreferrer">Open site</a>
        <?php endif; ?>
    </div>
</header>
