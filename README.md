# Install testnegative locally with Docker & Magento 2.4.3

### Create *testnegative* directory for docker install with magento locally 

**From within the new directory**, run:
```
curl -s https://raw.githubusercontent.com/Legends-Mkt/docker_testnegative/master/lib/template | bash
```
Copy media folder from server into magento/pub
```
rsync --verbose --progress --stats --recursive --times --links --update testnegative@128.199.229.20:/var/www/testnegative.com/public_html/pub/media ./magento/pub
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

### Restore database backup from server for new Docker environment
```
ssh testnegative@128.199.229.20 "mysqldump -u testnegative -p --no-tablespaces testnegativepre_live | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/'" > testnegativepre_live.sql

mysql -u magento2 -pmagento2 -h 127.0.0.1 -P33066
use magento2;
source testnegativepre_live.sql
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
docker exec -it testnegative_fpm_1 bash

mkdir -p var/www/.composer/
chown -R www-data /var/www/.composer/ 
```
#### Upgrade Magento:
```
# switch to web user
sudo -Hsu www-data

composer install
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento indexer:reindex
php bin/magento c:c
php bin/magento c:f
php bin/magento maintenance:disable
exit
exit
# Set file permissions:
cd ./magento
find . -type f -exec chmod 644 {} \; && find . -type d -exec chmod 755 {} \; && find ./var -type d -exec chmod 777 {} \; && find ./pub/media -type d -exec chmod 777 {} \; && find ./pub/static -type d -exec chmod 777 {} \; && chmod 777 ./app/etc && chmod 644 ./app/etc/*.xml && chmod u+x bin/magento

# Clearing Varnish cache:
docker-compose exec varnish varnishadm 'ban req.url ~ .'


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
### TO RUN CLI FOR MAGENTO FROM CONTAINER 
```
docker exec -it testnegative_fpm_1 bash
sudo -Hsu www-data
```

### MySQL access:**

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
