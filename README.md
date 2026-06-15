# Projects Explorer

A custom XAMPP dashboard that replaces the default Apache Friends welcome page with a clean, folder-first file browser for your local projects.

![Light and dark mode supported](https://img.shields.io/badge/theme-light%20%2F%20dark-blue)
![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb4)
![XAMPP](https://img.shields.io/badge/XAMPP-Windows-orange)

## Features

- Browses your `htdocs/Projects` folder by default
- Sidebar with quick navigation and top-level folder list
- Real-time search filter
- File and folder metadata (size, item count, last modified)
- "Open site" button auto-detected from entry files (`index.php`, `index.html`, etc.)
- Light / dark theme toggle, saved in `localStorage`
- Breadcrumb navigation for drilling into subfolders
- Path traversal protection — stays inside `htdocs`
- Responsive layout (works on small screens)

## Installation

### One-click (recommended)

1. Download or clone this repo
2. Double-click `install.bat`
3. Open `http://localhost`

The installer will:
- Auto-detect your XAMPP folder (checks drives C–H)
- Back up your existing `dashboard/index.php` as `index.php.bak`
- Copy the new dashboard into place
- Create `htdocs/Projects/` if it doesn't exist

> **Tip:** If XAMPP isn't detected automatically, the installer will ask you to type the path (e.g. `D:\xampp`).

### Manual

Copy `index.php` into your XAMPP installation at:

```
C:\xampp\htdocs\dashboard\index.php
```

## Updating

When a new version is released:

1. Pull or re-download the repo
2. Run `install.bat` again — it backs up and overwrites automatically

Or manually replace `htdocs\dashboard\index.php` with the new one.

## Uninstalling

Restore the backup the installer created:

```
C:\xampp\htdocs\dashboard\index.php.bak  →  index.php
```

Or reinstall XAMPP to get the original dashboard back.

## Requirements

- Windows
- XAMPP (any recent version)
- PHP 7.4 or newer (included with XAMPP)
