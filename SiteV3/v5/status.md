# Pages DONE
- wx2
- wx3
- wx4
- wx5
- wx6
- wx7
- wx8
- wx9
- wx10
- wx11
- wx12
- wx13
- wx14
- wx15
- wx16
- wxhistday (daily reports, incl. PM2.5)
- wxhistmonth / wxhistmonthB (monthly reports, incl. PM2.5)
- wxhistyear
- albgen / wx_albgen
- BeaufortScale / wxtempltas
- pondAdmin / datamod

# Progress log

## March 12th 2025
- converted some more pages

## March 30th 2025
- more pages done
- Next up: index.php

## Jun 2nd 2025
- converted cron_tags into something much more readable with DataSummarizer
- TODO: continue converting ViewDetailedData to use that instead of crontags

## Dec 26th 2025


## Jul 21st 2026
- Finished daily/monthly report migration polish: AQ (PM2.5) rows in tables, live today values, Highcharts intraday panels include air quality, monthly charts use dailySelectable

## Jul 31st 2026
- Responsive shell: collapsible hamburger + off-canvas nav drawer below 900px; accordion sections in drawer
- Slimmer banner/sub-header on narrow; chart reflow on resize; dd/dm grids scroll with sticky first column; repyear/rose/wx5 map fixes
- Footer “Mobile” → “Lite view” (mob.php retained)
- Detail pages now use cached `DataSummarizer` output rather than `cron_tags` files
- Hard-cutover phase 1: remaining public/admin pages ported to the Page shell; runtime URLs and AJAX endpoints de-prefixed for docroot deployment
- Deprecated URL map prepared (`wx4` → daily rankings, legacy graphs → historical graphs, webcam archive → wx2)

## TODO
- Rehearse the hard cutover locally: archive legacy UI under `oldSites/sitev3/`, move this directory's contents to docroot, and remove `v5/`
- Spot-check remaining wide tables under `.table-scroll` on phones
- Annual report (repyear) polish if needed
