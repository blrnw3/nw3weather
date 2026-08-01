<?php
/**
 * Template for secrets.php (which is git-excluded).
 *
 * Copy to secrets.php and fill in real values on each environment. This file is
 * included ONLY by cron_main.php, so the keys never reach page-serving code.
 *
 *   cp secrets.example.php secrets.php   # then edit the values
 */

// aprs.fi API key - used for the Islington/Potters CWOP backup fetches.
define('APRSFI_KEY', '');

// PurpleAir API key - used for the air-quality (PM2.5) fetch.
define('PURPLEAIR_KEY', '');
