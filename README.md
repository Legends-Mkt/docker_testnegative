## Install testnegative locally with Docker and Magento 2.3.4

Create *testnegative* directory for docker install with magento locally 

*From within the new directory*, run:

```curl -s https://raw.githubusercontent.com/roblefort/docker_testnegative/main/lib/template | bash```

Copy env.php to ./magento/app/etc/

Copy media content archive from staging magento server to ./magento/pub/media/

Create docker containers from folder testnegative (runs docker-compose.yml)
```
docker-compose up -d
# change file permissions for composer.sh
cd mnt
chmod +x composer.sh
```

Docker hostnames services in stack containers:
db - databases
fpm - php-fpm
varnish - varnish service
elasticsearch - elasticsearch service
redis - redis service
rabbitmq - rabbitmq service

Then after docker containers are all running:
```
docker-compose exec fpm /mnt/composer.sh
```

Edit hosts file and add line
```
127.0.0.1 	local.testnegative.com
```
Check host
```
ping localtestnegative.com
curl -I local.testnegative.com
```

Restore database backup from staging for new environment
```
mysql -u magento2 -pmagento2 -h 127.0.0.1 -P33066
use magento2;
source [/path/DBNAME.sql]
```

Edit base url in magento DB
```use magento2;
select * from core_config_data where path like '%base%url%';
UPDATE core_config_data SET value = 'http://local.testnegative.com/' WHERE config_id = '2';
UPDATE core_config_data SET value = 'http://local.testnegative.com/' WHERE config_id = '1436';
UPDATE core_config_data SET value = 'http://local.testnegative.com/' WHERE config_id = '1652';
UPDATE core_config_data SET value = 'https://local.testnegative.com/' WHERE config_id = '3';
UPDATE core_config_data SET value = 'https://local.testnegative.com/' WHERE config_id = '1438';
UPDATE core_config_data SET value = 'https://local.testnegative.com/' WHERE config_id = '1653';
```

Access to the container from folder testnegative in order to be able to run the commands of Magento CLI: (Path: /app)
```
docker exec -it testnegative_fpm_1 bash
mkdir /var/www/.composer/
chown -R www-data /var/www/.composer/
composer self-update --1 
sudo -Hsu www-data
```
Upgrade Magento:
```
composer install
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:c
php bin/magento cache:f
php bin/magento maintenance:disable
```
Clearing Varnish cache if need:
```
docker-compose exec varnish varnishadm 'ban req.url ~ .'
```

*MySQL access:*

External:
Host: (host IP)
Port: 33066
DB name: magento2
DB user: magento2
Password: magento2

Internal:
Host: db
DB name: magento2
DB user: magento2
Password: magento2
