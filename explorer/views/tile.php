<?php
/**
 * Projects Explorer: one tile for a file or folder.
 * Expects $item (from itemData() in src/listing.php).
 * Folders: the name opens the folder in the dashboard, "Open site" opens the site.
 * Files: the name opens the file, unless it may hold secrets (then it isn't linked).
 */

defined('PV_ENTRY') || exit;
$kind = $item['is_dir'] ? 'folder' : 'file';
$nameUrl = $item['is_dir'] ? $item['browse'] : $item['open'];
$searchText = strtolower($item['name'] . ' ' . $item['type']);
?>
<li class="pv-tile" data-item data-search-text="<?php echo esc($searchText); ?>">
    <div class="pv-tile__head">
        <span class="pv-icon pv-icon--<?php echo esc($kind); ?>" aria-hidden="true"></span>
        <div class="pv-tile__body">
            <h3 class="pv-tile__title">
                <?php if ($nameUrl !== null): ?>
                    <a class="pv-tile__link" href="<?php echo esc($nameUrl); ?>"><?php echo esc($item['name']); ?></a>
                <?php else: ?>
                    <?php echo esc($item['name']); ?>
                <?php endif; ?>
            </h3>
            <p class="pv-tile__meta"><?php echo esc($item['meta']); ?></p>
        </div>
    </div>

    <div class="pv-tile__pills">
        <?php if ($item['type'] !== ''): ?>
            <span class="pv-pill pv-pill--type"><?php echo esc($item['type']); ?></span>
        <?php endif; ?>
        <?php if ($item['activity'] === 'active'): ?>
            <span class="pv-pill pv-pill--active">Changed in last 2 days</span>
        <?php elseif ($item['activity'] === 'recent'): ?>
            <span class="pv-pill">Changed this week</span>
        <?php endif; ?>
        <?php if ($item['no_link']): ?>
            <span class="pv-pill pv-pill--warn">Not linked: may hold passwords or data</span>
        <?php endif; ?>
    </div>

    <?php if ($item['note'] !== null): ?>
        <p class="pv-tile__note"><?php echo esc($item['note']); ?></p>
    <?php endif; ?>

    <div class="pv-tile__actions">
        <?php if ($item['is_dir']): ?>
            <a class="pv-btn" href="<?php echo esc($item['browse']); ?>" aria-label="Browse <?php echo esc($item['name']); ?>">Browse</a>
            <?php if ($item['open'] !== null): ?>
                <a class="pv-btn pv-btn--primary" href="<?php echo esc($item['open']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Open site <?php echo esc($item['name']); ?> in a new tab">Open site</a>
            <?php endif; ?>
        <?php elseif ($item['open'] !== null): ?>
            <a class="pv-btn" href="<?php echo esc($item['open']); ?>" aria-label="Open file <?php echo esc($item['name']); ?>">Open file</a>
        <?php endif; ?>
    </div>
</li>
