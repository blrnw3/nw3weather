# nw3weather site v2 - static archive

Read-only snapshot of site version 2 (10 Sep 2011 - 20 May 2013), served as plain HTML.

* Most pages are the original pages as captured by the Internet Archive (Wayback Machine), with
  scripts, forms and unit/refresh selectors removed and internal links rewritten to the `.html` files.
* The photo album pages had no capture and were rendered once from `oldSites/sitev2` (PHP 8.3, after
  fixing a removed `microtime()` constant locally) and cleaned the same way.
* Pages that need data files which no longer exist link to `not-archived.html`.
* Images are archived copies where available, otherwise the file of the same name on the current site.
* `.htaccess` blocks any script extension; there is no PHP in this folder.

Built by a one-off script; the original PHP source remains in `oldSites/sitev2` (not served).
