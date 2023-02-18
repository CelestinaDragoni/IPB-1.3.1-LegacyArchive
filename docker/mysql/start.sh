# Install DB
if [ ! -f "/etc/snapshot.lock" ]; then
mysql_install_db --user=mysql --ldata=/var/db/mysql/
fi

## Start Service
mysqld_safe &

## Import Snapshot
if [ ! -f "/etc/snapshot.lock" ]; then
sleep 5
mysqladmin -u root password 'Aq1Sw2De3'
mysql -u root -pAq1Sw2De3 --execute="CREATE DATABASE ivboard;"
mysql -u root -pAq1Sw2De3 --execute="GRANT ALL PRIVILEGES ON *.* to 'root'@'%' IDENTIFIED BY 'Aq1Sw2De3';"
touch /etc/snapshot.lock
fi

## Don't Close Docker
tail -f /dev/null
