## Development Logs
echo '' > /var/log/php/php_errors.log
echo '' > /var/log/nginx/error.log
echo '' > /var/log/nginx/access.log
chmod -R 0777 /var/log/php
chmod -R 0777 /var/log/nginx

## Instructions From Invision Board Regarding... Permissions
## --------------------------------------------------------------------------------------
## The CHMOD values are not needed for Windows based servers and can be safely ignored.
## The CHMOD values may vary from system to system, if in doubt, CHMOD everything to 0777.
## --------------------------------------------------------------------------------------
## Archivist Note: Well okay then, don't use this in a production env...
chmod -R 0777 /srv/sites/invision

## Start Services
/usr/sbin/php5-fpm -D ;
/usr/sbin/nginx &
tail -f /var/log/nginx/error.log /var/log/php/php_errors.log | tee /dev/stderr
