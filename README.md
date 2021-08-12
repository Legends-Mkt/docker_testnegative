# Install testnegative locally with Docker & Magento 2.3.4

### Backup pub/media and database from staging server
```
bin/magento setup:backup --db --mediamedia 
```
Retrieve backup files from var/backups/.. and download files to local machine

### Create *testnegative* directory for docker install with magento locally 

**From within the new directory**, run:
```
curl -s https://raw.githubusercontent.com/Legends-Mkt/docker_testnegative/master/lib/template | bash
```
Copy env.php to ./magento/app/etc/
```
mv env.php ./magento/app/etc
```
Restore pub/media folder from backup archive
```
tar -xzvf [source path]/xxxxxxxx_filesystem_media.tgz -C magento
```
### Create docker containers from folder testnegative (runs docker-compose.yml)
```
docker-compose up -d
```
**Docker hostnames services in stack containers:**
- db - databases
- fpm - php-fpm
- varnish - varnish service
- elasticsearch - elasticsearch service
- redis - redis service
- rabbitmq - rabbitmq service

Then after docker containers are all running:
```
# change file permissions for composer.sh
cd mnt
chmod +x composer.sh
docker-compose exec fpm /mnt/composer.sh
```
Install SSL certificates
```
chmod +x tls.sh
cd ..
docker-compose exec tls /mnt/tls.sh
```
Clear varnish cache
```
docker-compose exec varnish varnishadm 'ban req.url ~ .'
```
### Edit hosts file
``` 127.0.0.1   testnegative.store ```

### Restore database backup from staging for new environment
```
mysql -u magento2 -pmagento2 -h 127.0.0.1 -P33066
use magento2;
source [/path/DBNAME.sql]
```

Edit base url in magento DB and change domain to **testnegative.store**
```use magento2;
select * from core_config_data where path like '%base%url%';

UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '2';
UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '1436';
UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '1652';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '3';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '1438';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '1653';
```

### Access to the container from folder testnegative to run the Magento CLI: (Path: /app)
```
chown -R 33:33 ./magento/

docker exec -it testnegative_fpm_1 bash

mkdir /var/www/.composer/
chown -R www-data /var/www/.composer/
composer self-update --1 
```
#### Upgrade Magento:
```
# switch to web user
sudo -Hsu www-data

composer install
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
# Set permission from host
rm -rf ./pub/static/_cache   ./pub/static/adminhtml/*  ./pub/static/frontend/*  ./pub/static/deployed_version.txt

php bin/magento setup:static-content:deploy -f
php bin/magento cache:c
php bin/magento cache:f
php bin/magento maintenance:disable
exit
exit
Set permission:
cd ./magento
find . -type f -exec chmod 644 {} \; && find . -type d -exec chmod 755 {} \; && find ./var -type d -exec chmod 777 {} \; && find ./pub/media -type d -exec chmod 777 {} \; && find ./pub/static -type d -exec chmod 777 {} \; && chmod 777 ./app/etc && chmod 644 ./app/etc/*.xml && chown -R 33:33 . && chmod u+x bin/magento

```
Launch **https://testnegative.store** and accept SSL warning in browser

#### Setting developer mode
```
docker exec -it testnegative_fpm_1 bash
sudo -Hsu www-data
php bin/magento maintenance:enable

# run outside container
docker-compose exec varnish varnishadm 'ban req.url ~ .'

rm -rf generated/metadata/* generated/code/*
bin/magento deploy:mode:set developer
php bin/magento setup:static-content:deploy -f
php bin/magento cache:c
php bin/magento cache:f
php bin/magento maintenance:disable

# run outside container
docker-compose exec varnish varnishadm 'ban req.url ~ .'
```

**MySQL access:**

External:

- Host: (host IP)
- Port: 33066
- DB name: magento2
- DB user: magento2
- Password: magento2

Internal:

- Host: db
- DB name: magento2
- DB user: magento2
- Password: magento2
