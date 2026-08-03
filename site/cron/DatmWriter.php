<?php
/**
 * Manual daily (datm) row writer — ported from legacy datfuncdef.php.
 */
class DatmWriter {
	/** CSV column order for dat / datt files. */
	public static function datHeaders() {
		return array(
			'tmin','tmax','tmean','hmin','hmax','hmean','pmin','pmax','pmean',
			'wmean','wmax','gust','wdir','rain','hrmax','10max','ratemax',
			'dmin','dmax','dmean','nightmin','daymax','tc10max','tchrmax','hchrmax',
			'tc10min','tchrmin','hchrmin','w10max','fmin','fmax','fmean','afhrs',
			'aqmin','aqmax','aqmean'
		);
	}

	/** CSV column order for datm files. */
	public static function datmHeaders() {
		return array(
			'sunhr','wethr','cloud','snow','lysnw','hail','thunder','fog',
			'comms','extra','issues','away','pond','spare'
		);
	}

	/**
	 * Append a datm row for yesterday if not already written.
	 * @param string $sunhrs
	 * @return bool true if a row was written
	 */
	public static function write($sunhrs) {
		$yr = Date::$yr_yest;
		$path = ROOT . 'datm' . $yr . '.csv';
		if(!file_exists($path)) {
			return false;
		}
		$datm = file($path);
		$len = count($datm);
		$expected = intval(date('z')) + 1; // days in year so far + header
		if($len === $expected) {
			return false;
		}
		$pond_temp = null;
		if(false && file_exists(ROOT . 'pond_temp.txt')) {
			$mod_time = filemtime(ROOT . 'pond_temp.txt');
			if(time() - $mod_time < 48 * 3600) {
				$pond_temp = file_get_contents(ROOT . 'pond_temp.txt');
			}
		}
		if($pond_temp === null) {
			$nowFile = CACHE_ROOT . 'serialised_datNow.txt';
			if(file_exists($nowFile)) {
				$NOW = unserialize(file_get_contents($nowFile));
				$pond_temp = isset($NOW['misc']['pondTemp']) ? $NOW['misc']['pondTemp'] : '';
			} else {
				$pond_temp = '';
			}
		}
		$wethrs = file_exists(ROOT . 'wethrs.txt') ? file_get_contents(ROOT . 'wethrs.txt') : '0';
		$listm = array($sunhrs, $wethrs, 'u', '', '', '', '', '', 'blr', '', '', '1', $pond_temp, '\n');
		$fildatm = fopen($path, 'a');
		fputcsv($fildatm, $listm);
		fclose($fildatm);
		return true;
	}

	/** Ensure datm was written; mail if sunhrs never arrived. */
	public static function checkWritten() {
		if(self::write('0')) {
			mail('alerts@nw3weather.co.uk', 'Failed to receive sunhrs!',
				'Data not written for this day so defaulted to zero sun');
		}
	}
}
