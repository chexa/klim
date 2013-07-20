#!/bin/sh
# Backup Skript für Magento Projekte und Datenbanken
#
# (c) 2007 Jochen Weiland
# 
#  ****************************************************** 
#  *                                                    *
#  *  ACHTUNG: Dieses Skript nur mit einem Editor be-   *
#  *  bearbeiten, der Dateien im UNIX/LINUX Format      *
#  *  speichern kann!!                                  *
#  *                                                    *
#  *  Nach Speicherung im DOS- bzw. WINDOWS-Format      *
#  *  kann das Backup Skript unter LINUX nicht mehr     *
#  *  ausgeführt werden!!                               *           *
#  *                                                    *
#  ******************************************************
#
# set var: current day of month
dayofmonth=`date '+%d'`
dayofweek=`date '+%A'`

# set vars: database, user, password
# db?name should be the name of the subdir where the Magento site is installed

db1name=standard-schmuck.de
db1=shop_db
user1=shop_db
pass1=9HnFdd5jzFhVHaCt

# db2name=
# db2=
# user2=
# pass2=

 
# localhost needs to be set to the following:
# 'localhost' for MySQL databases in version 3.x
# '127.0.0.3' for MySQL databases in version 4.x
# '127.0.0.3' for MySQL databases in version 5.x

mysqldump --opt -h 127.0.0.1 -u $user1 -p$pass1 $db1 | gzip > /var/backups-neu/$db1name/databases/$db1name.dump.sql.$dayofweek.gz
#mysqldump --opt -h 127.0.0.1 -u $user2 -p$pass2 $db2 | gzip > /var/backups-neu/$db2name/databases/$db2name.dump.sql.$dayofweek.gz

#uncomment lines below as more installations are added to backup

# backup files

rm /var/backups-neu/$db1name/files/htdocs_$dayofweek.gz
tar -cf /var/backups-neu/$db1name/files/htdocs_$dayofweek /var/www/share/$db1name/htdocs
gzip -f /var/backups-neu/$db1name/files/htdocs_$dayofweek

# backup all files of Magento installation

# die Zeilen #if, #then und #fi zeigen, wie ein Backup der Dateien z.B. nur Samstags ausgeführt wird. Ggf. '#' entfernen

#if [ "$dayofweek" = "Saturday" ]
# then

#rm /var/backups-neu/$db1name/files$db1name.$dayofweek.gz
#tar -cf /var/www/share/backup/files/$db1name.$dayofweek /var/www/share/$db1name/htdocs
#gzip -f /var/www/share/backup/files/$db1name.$dayofweek

#fi

#rm /var/www/share/backup/$db2name.$dayofweek.gz
#tar -cf /var/www/share/backup/files/$db2name.$dayofweek /var/www/share/$db1name/htdocs
#gzip -f /var/www/share/backup/files/$db2name.$dayofweek
# -----------------------------------------------------------

# Magento FullPageWarmup

# Varnisch clear Cache
cd /var/www/share/standard-schmuck.de/htdocs/
sudo /etc/init.d/varnish stop
rm -rf var/cache
mkdir var/cache
chmod 777 var/cache
sudo chown web-user:web-user var/cache
sudo /etc/init.d/varnish start
# delite Session Files
rm -rf var/session
mkdir var/session
chmod 777 var/session
sudo chown web-user:web-user var/session

sleep 60
#FullPageWarmup start
wget -O - www.standard-schmuck.de/index.php | grep -o '<a href="http[^"]*"' | sed 's/<a href="//;s/"$//' | grep "http://www.standard-schmuck.de" | while read line; do
# Folgende Zeilen auskommentieren, wenn noch weiter in der Tiefe die Seiten aufgerufen werden sollen.
#wget -O - $line | grep -o '<a href="http[^"]*"' | sed 's/<a href="//;s/"$//' | grep "http://www.standard-schmuck.de" | while read line; do
wget -P /tmp/warmup/output --delete-after $line
#done
done