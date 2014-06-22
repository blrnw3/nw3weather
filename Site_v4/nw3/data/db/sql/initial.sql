-- Daily variables (daily summary data as processed daily from the core live variables for a 24hr period) --
CREATE TABLE IF NOT EXISTS `daily` (
  `d` date NOT NULL,
  `tmin` decimal(3,1) DEFAULT NULL,
  `tmax` decimal(3,1) DEFAULT NULL,
--   `tmean` decimal(4,2) DEFAULT NULL,
  `hmin` tinyint(2) unsigned DEFAULT NULL,
  `hmax` tinyint(2) unsigned DEFAULT NULL,
  `hmean` decimal(3,1) unsigned DEFAULT NULL,
  `pmin` smallint(4) unsigned DEFAULT NULL,
  `pmax` smallint(4) unsigned DEFAULT NULL,
  `pmean` decimal(5,1) unsigned DEFAULT NULL,
  `wmean` decimal(4,2) unsigned DEFAULT NULL,
  `wmax` decimal(3,1) unsigned DEFAULT NULL,
  `gust` decimal(3,1) unsigned DEFAULT NULL,
  `wdir` smallint(3) unsigned DEFAULT NULL,
  `rain` decimal(4,1) unsigned DEFAULT NULL,
  `hrmax` decimal(3,1) unsigned DEFAULT NULL,
  `r10max` decimal(3,1) unsigned DEFAULT NULL,
  `ratemax` decimal(4,1) unsigned DEFAULT NULL,
  `dmin` decimal(3,1) DEFAULT NULL,
  `dmax` decimal(3,1) DEFAULT NULL,
  `dmean` decimal(4,2) DEFAULT NULL,
  `t24min` decimal(3,1) DEFAULT NULL,
  `t24max` decimal(3,1) DEFAULT NULL,
  `t24mean` decimal(4,2) DEFAULT NULL,
  `tchrmin` decimal(2,1) DEFAULT NULL,
  `tchrmax` decimal(2,1) DEFAULT NULL,
  `tc10min` decimal(2,1) DEFAULT NULL,
  `tc10max` decimal(2,1) DEFAULT NULL,
  `hchrmin` tinyint(2) DEFAULT NULL,
  `hchrmax` tinyint(2) DEFAULT NULL,
  `w10max` decimal(3,1) unsigned DEFAULT NULL,
  `afhrs` float unsigned DEFAULT NULL,
  `fmin` tinyint(2) DEFAULT NULL,
  `fmax` tinyint(2) DEFAULT NULL,
  `fmean` decimal(3,1) DEFAULT NULL,

--   `trange` decimal(3,1) unsigned DEFAULT NULL,
--   `hrange` tinyint(2) unsigned DEFAULT NULL,
--   `prange` tinyint(2) unsigned DEFAULT NULL,
--   `ratemean` decimal(3,1) unsigned DEFAULT NULL,

-- Manual obs --
  `sunhr` decimal(3,1) unsigned DEFAULT NULL,
  `wethr` decimal(3,1) unsigned DEFAULT NULL,
  `cloud` varchar(7) DEFAULT NULL,
  `snow` decimal(4,1) unsigned DEFAULT NULL,
  `lysnw` decimal(3,1) unsigned DEFAULT NULL,
  `hail` tinyint DEFAULT NULL,
  `thunder` tinyint DEFAULT NULL,
  `fog` tinyint DEFAULT NULL,
  `comms` varchar(250) DEFAULT NULL,
  `extra` varchar(500) DEFAULT NULL,
  `issues` varchar(500) DEFAULT NULL,
  `away` boolean DEFAULT NULL,

-- Times --
  `t_tmin` time DEFAULT NULL,
  `t_tmax` time DEFAULT NULL,
  `t_hmax` time DEFAULT NULL,
  `t_hmin` time DEFAULT NULL,
  `t_pmax` time DEFAULT NULL,
  `t_pmin` time DEFAULT NULL,
  `t_wmax` time DEFAULT NULL,
  `t_gust` time DEFAULT NULL,
  `t_hrmax` time DEFAULT NULL,
  `t_r10max` time DEFAULT NULL,
  `t_ratemax` time DEFAULT NULL,
  `t_dmax` time DEFAULT NULL,
  `t_dmin` time DEFAULT NULL,
  `t_t24max` time DEFAULT NULL,
  `t_t24min` time DEFAULT NULL,
  `t_tchrmax` time DEFAULT NULL,
  `t_tchrmin` time DEFAULT NULL,
  `t_tc10max` time DEFAULT NULL,
  `t_tc10min` time DEFAULT NULL,
  `t_hchrmax` time DEFAULT NULL,
  `t_hchrmin` time DEFAULT NULL,
  `t_w10max` time DEFAULT NULL,
  `t_fmax` time DEFAULT NULL,
  `t_fmin` time DEFAULT NULL,

-- Anomalies --
  `a_tmin` decimal(3,1) DEFAULT NULL,
  `a_tmax` decimal(3,1) DEFAULT NULL,
  `a_wmean` decimal(4,2) DEFAULT NULL,
  `a_rain` decimal(4,1) DEFAULT NULL,
  `a_sunhr` decimal(3,1) DEFAULT NULL,
  `a_wethr` decimal(3,1) DEFAULT NULL,

  PRIMARY KEY (`d`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 'Live' variables (once-per-minute sensor readings) --
CREATE TABLE IF NOT EXISTS `live_old` (
  `t` datetime NOT NULL,
  `rain` decimal(4,1) unsigned DEFAULT NULL,
  `humi` tinyint(2) unsigned DEFAULT NULL,
  `pres` smallint(4) unsigned DEFAULT NULL,
  `wind` decimal(3,1) unsigned DEFAULT NULL,
  `gust` decimal(3,1) unsigned DEFAULT NULL,
  `temp` decimal(3,1) DEFAULT NULL,
  `wdir` smallint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`t`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Alternative to live, in testing--
CREATE TABLE IF NOT EXISTS `live` (
  `t` datetime NOT NULL,
  `rain` float unsigned DEFAULT NULL,
  `humi` tinyint(2) unsigned DEFAULT NULL,
  `pres` smallint(4) unsigned DEFAULT NULL,
  `wind` float unsigned DEFAULT NULL,
  `gust` float unsigned DEFAULT NULL,
  `temp` float DEFAULT NULL,
  `wdir` smallint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`t`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Daily anom --
CREATE TABLE IF NOT EXISTS `anom_daily` (
  `d` date NOT NULL,
  `tmin` decimal(3,1) DEFAULT NULL,
  `tmax` decimal(3,1) DEFAULT NULL,
--   `tmean` decimal(4,2) DEFAULT NULL,
  `wmean` float unsigned DEFAULT NULL,
  `rain` float unsigned DEFAULT NULL,
--   `trange` decimal(3,1) unsigned DEFAULT NULL,
  `sunhr` decimal(3,1) unsigned DEFAULT NULL,
  `wethr` float unsigned DEFAULT NULL,
  `hail` float unsigned DEFAULT NULL,
  `thunder` float unsigned DEFAULT NULL,
  `fog` float unsigned DEFAULT NULL,
--   `rdays` float unsigned DEFAULT NULL,

  PRIMARY KEY (`d`)
) ENGINE=InnoDB DEFAULT CHARSET = utf8;


-- Monthly anom --
CREATE TABLE IF NOT EXISTS `anom_monthly` (
  `m` date NOT NULL, -- TODO datatype for months
  `tmin` decimal(3,1) DEFAULT NULL,
  `tmax` decimal(3,1) DEFAULT NULL,
  `tmean` decimal(4,2) DEFAULT NULL,
  `wmean` decimal(3,1) DEFAULT NULL,
  `rain` decimal(4,1) unsigned DEFAULT NULL,
  `trange` decimal(3,1) unsigned DEFAULT NULL,
  `sunhr` int unsigned DEFAULT NULL,
  `wethr` int unsigned DEFAULT NULL,
  `hail` tinyint(2) unsigned DEFAULT NULL,
  `thunder` tinyint(2) unsigned DEFAULT NULL,
  `fog` tinyint(2) unsigned DEFAULT NULL,

  `sunmax` int unsigned DEFAULT NULL,

  `rdays` int unsigned DEFAULT NULL,
  `days_frost` decimal(2,1) unsigned DEFAULT NULL,
  `days_storm` decimal(2,1) unsigned DEFAULT NULL,
  `days_snow` decimal(2,1) unsigned DEFAULT NULL,
  `days_snowfall` decimal(2,1) unsigned DEFAULT NULL,

  PRIMARY KEY (`m`)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8;