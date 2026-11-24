<?php
/**
 * Projects Explorer: one section of tiles (Folders or Files).
 * Expects $sectionTitle, $sectionItems and $sectionEmpty from layout.php.
 */

defined('PV_ENTRY') || exit;
$sectionId = 'pv-section-' . strtolower($sectionTitle);
?>
<section class="pv-section" aria-labelledby="<?php echo esc($sectionId); ?>" data-section>
    <div class="pv-section__head">
        <h2 class="pv-section__title" id="<?php echo esc($sectionId); ?>"><?php echo esc($sectionTitle); ?></h2>
        <span class="pv-stat"><?php echo esc((string) count($sectionItems)); ?></span>
    </div>

    <?php if (!empty($sectionItems)): ?>
        <ul class="pv-grid">
            <?php foreach ($sectionItems as $item) {
                require __DIR__ . '/tile.php';
            } ?>
        </ul>
    <?php else: ?>
        <p class="pv-empty"><?php echo esc($sectionEmpty); ?></p>
    <?php endif; ?>
</section>
