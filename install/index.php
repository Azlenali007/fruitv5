<?php
/**
 * In-Browser Web Installer Entry Point
 * N.A Fresh Fruits & Coconuts
 */
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/InstallerController.php';

InstallerController::handle();
