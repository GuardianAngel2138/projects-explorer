<?php
/**
 * Projects Explorer: sidebar.
 * Quick links (start, up, root), pinned folders, the folders in the start folder, and tools.
 */

defined('PV_ENTRY') || exit;

/** One sidebar link. $kind is the small label on the right; $icon is 'folder' or 'tool'. */
function sideLink(string $url, string $label, string $kind, bool $active = false, string $icon = 'folder'): void
{
    ?>
    <a class="pv-side-link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc($url); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
        <span class="pv-side-link__main">
            <span class="pv-icon pv-icon--<?php echo esc($icon); ?>" aria-hidden="true"></span>
            <span class="pv-side-link__label"><?php echo esc($label); ?></span>
        </span>
        <small class="pv-side-link__kind"><?php echo esc($kind); ?></small>
    </a>
    <?php
}
?>
<aside class="pv-sidebar">
    <div class="pv-brand">
        <p class="pv-brand__title"><?php echo esc($config['title']); ?></p>
        <p class="pv-brand__copy">Your local sites and folders in <?php echo esc($rootLabel); ?>.</p>
    </div>

    <nav class="pv-side-block" aria-label="Quick links">
        <p class="pv-side-block__title">Go to</p>
        <div class="pv-side-list">
            <?php
            sideLink(browseUrl(), $startLabel, 'start', $isAtStart);
            if ($parentRelative !== null && !$isAtStart) {
                sideLink(browseUrl($parentRelative), 'Up one folder', 'up');
            }
            if ($startRelative !== '') {
                sideLink(browseUrl(''), $rootLabel, 'root', $currentRelative === '');
            }
            ?>
        </div>
    </nav>

    <?php if (!empty($pinned)): ?>
        <nav class="pv-side-block" aria-label="Pinned folders">
            <p class="pv-side-block__title">Pinned</p>
            <div class="pv-side-list">
                <?php foreach ($pinned as $pin) {
                    sideLink(browseUrl($pin['relative']), $pin['name'], 'pinned', isCurrentOrInside($currentRelative, $pin['relative']));
                } ?>
            </div>
        </nav>
    <?php endif; ?>

    <?php if (!empty($sidebarFolders)): ?>
        <?php /* Collapsible so a long folder list doesn't push the content far down on phones (closed there by explorer.js). */ ?>
        <details class="pv-side-block pv-side-block--folders" open data-side-folders>
            <summary class="pv-side-block__title">In <?php echo esc($startLabel); ?></summary>
            <nav class="pv-side-list" aria-label="Folders in <?php echo esc($startLabel); ?>">
                <?php foreach ($sidebarFolders as $item) {
                    sideLink($item['browse'], $item['name'], $item['type'] !== '' ? $item['type'] : 'folder', isCurrentOrInside($currentRelative, $item['relative']));
                } ?>
            </nav>
        </details>
    <?php endif; ?>

    <?php if (!empty($config['tools'])): ?>
        <nav class="pv-side-block" aria-label="Tools">
            <p class="pv-side-block__title">Tools</p>
            <div class="pv-side-list">
                <?php foreach ($config['tools'] as $label => $url) {
                    if (is_string($label) && is_string($url)) {
                        sideLink($url, $label, 'tool', false, 'tool');
                    }
                } ?>
            </div>
        </nav>
    <?php endif; ?>
</aside>
