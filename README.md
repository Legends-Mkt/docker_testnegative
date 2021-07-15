# Install testnegative locally with Docker & Magento 2.3.4

### Create *docker_testnegative* directory for docker install with magento locally 

**From within the new directory**, run:
```
curl -s https://raw.githubusercontent.com/roblefort/docker_testnegative/main/lib/template | bash
```

Copy env.php to ./magento/app/etc/
```
mv env.php ./magento/app/etc
```
Copy backup of Media and DB from staging 
```
bin/magento setup:backup --db --mediamedia 
```
Restore media backup into ./magento/pub/media/

### Create docker containers from folder docker_testnegative (runs docker-compose.yml)
```
docker-compose up -d
```

Docker hostnames services in stack containers:
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
docker-compose exec tls /mnt/tls.sh
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

### Access to the container from folder docker_testnegative in order to be able to run the Magento CLI: (Path: /app)
```
docker exec -it docker_testnegative_fpm_1 bash
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
php bin/magento setup:static-content:deploy -f
php bin/magento cache:c
php bin/magento cache:f
php bin/magento maintenance:disable
```
Launch **https://testnegative.store** and accept SSL warning in browser

***MySQL access:**

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
