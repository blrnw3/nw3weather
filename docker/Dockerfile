# Local-dev image for the SiteV3 weather site.
# Docroot is /var/www/html on purpose so the hardcoded ROOT in basics.php /
# v5/UtilsAndConsts.php works without any code changes.
#
# Prod runs PHP 5.4, whose official image is based on Debian 8 "jessie" (now
# archived) — hence the apt-source rewrite and the legacy gd configure flags.
ARG PHP_VERSION=5.4
FROM php:${PHP_VERSION}-apache

# jessie is EOL: the only surviving repo is archive.debian.org/debian (main).
# The security and *-updates suites are gone, so replace sources.list wholesale.
RUN set -eux; \
    echo 'deb [check-valid-until=no] http://archive.debian.org/debian jessie main' > /etc/apt/sources.list; \
    apt-get -o Acquire::Check-Valid-Until=false update; \
    apt-get install -y --no-install-recommends --force-yes \
        libpng12-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        msmtp \
        rsync; \
    docker-php-ext-configure gd \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/; \
    docker-php-ext-install gd mysqli pdo_mysql mysql; \
    rm -rf /var/lib/apt/lists/*

# Stub the mount points referenced by hardcoded paths (VID_ROOT / CAM_ROOT).
RUN mkdir -p /mnt/nw3-vol1/html /mnt/webcam/html \
    && chown -R www-data:www-data /mnt

# Route PHP mail() to Mailpit so cron alert emails are caught locally, never sent.
RUN printf 'account default\nhost mailpit\nport 1025\nfrom localdev@nw3weather.local\n' > /etc/msmtprc \
    && chmod 600 /etc/msmtprc \
    && { \
        echo 'sendmail_path = "/usr/bin/msmtp -t"'; \
        echo 'date.timezone = Europe/London'; \
        echo 'display_errors = On'; \
        echo 'error_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE'; \
       } > /usr/local/etc/php/conf.d/zz-localdev.ini

COPY docker/apache-localdev.conf /etc/apache2/conf-enabled/localdev.conf

RUN a2enmod rewrite

# NOTE: ffmpeg is not packaged for jessie, so timelapse/video generation
# (cron_cam.php) won't run locally. Page iteration does not need it.
