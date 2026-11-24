# Changelog

## 2.0.0 (2026-11-24)

Works with any htdocs layout, not just a `Projects` folder.

### Added
- Settings file (`explorer/config.php`) for the start folder, pinned folders, hidden names, sidebar tools and more. Updates never overwrite it.
- Project detection: Laravel opens `public/`, Vite and other built sites open `dist/` or `build/`, WordPress is labelled, Node projects get a note instead of a broken link.
- Files that often hold secrets (`.env`, `.sql`, `.key` and others) are listed without an open link.
- Dotfiles, `node_modules`, `vendor` and XAMPP's own folders are hidden.
- The dashboard only answers requests from your own computer (setting: `allow_remote`).
- Sidebar Tools list with phpMyAdmin and PHP info.
- `uninstall.bat` puts XAMPP's original page back.
- Theme follows your system setting until you pick one, without a flash on load.
- Skip link, visible keyboard focus, larger tap targets, screen-reader labels.
- MIT licence.

### Changed
- Starts in `htdocs/Projects` only if it exists, otherwise in `htdocs`.
- Installer looks for XAMPP on every drive (A to Z) and accepts a path argument.
- Installer saves XAMPP's page once as `index.php.original` and never overwrites it.
- Installer no longer creates a `Projects` folder.
- Code split into small files under `explorer/` (one `index.php` loads it).
- Minimum PHP version is now 8.0.

### Fixed
- Running the installer twice overwrote the backup of XAMPP's page with this dashboard.
- The "htdocs root" link and breadcrumb went to the start folder instead of the root.
- `?path[]=x` in the address crashed the page.
- Long folder names caused sideways scrolling on phones.
- Some grey and orange text was below the minimum contrast for easy reading.

## 1.0.0 (2026-06-15)

First release: a folder browser for `htdocs/Projects` with search, dark mode and a Windows installer.
