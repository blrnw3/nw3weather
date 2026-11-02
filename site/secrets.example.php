<?php
/**
 * Template for secrets.php (which is git-excluded).
 *
 * Copy to secrets.php and fill in real values on each environment. It is
 * included by cron_main.php and, via AdminAuth.php, by the admin edit forms.
 *
 *   cp secrets.example.php secrets.php   # then edit the values
 */

// aprs.fi API key - used for the Islington/Potters CWOP backup fetches.
define('APRSFI_KEY', '');

// PurpleAir API key - used for the air-quality (PM2.5) fetch.
define('PURPLEAIR_KEY', '');

// Admin edit form passwords (datamod.php, pondAdmin.php). Use long random values.
// Editing is refused while either is missing or still the placeholder.
define('ADMIN_DATAMOD_PASSWORD', 'CHANGE_ME_PLACEHOLDER');
define('ADMIN_POND_PASSWORD', 'CHANGE_ME_PLACEHOLDER');

// Bearer token for api_recap.php (written weather recaps). At least 32 random characters; the ingest answers
// 503 while this is missing, empty or a placeholder.
define('NW3_RECAP_TOKEN', '');
