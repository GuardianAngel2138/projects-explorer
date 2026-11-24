<?php
/**
 * Projects Explorer: entry point.
 *
 * XAMPP's localhost page redirects to /dashboard/, which runs this file.
 * It only loads the app from the explorer/ folder, so installing or updating
 * means replacing this one file plus that one folder.
 */
// Marks a proper start, so the files in explorer/ refuse to run when opened directly.
define('PV_ENTRY', true);

require __DIR__ . '/explorer/app.php';
