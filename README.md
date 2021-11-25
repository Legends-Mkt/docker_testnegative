# Install testnegative locally with Docker & Magento 2.4.3

### Create *testnegative* directory for docker install with magento locally 

**From within the new directory**, run:
```
curl -s https://raw.githubusercontent.com/Legends-Mkt/docker_testnegative/master/lib/template | bash
```
**Clone Testnegative Repo**
```
mkdir ./magento
cd ./magento 
git clone -b pre-master-dev3 git@github.com:Legends-Mkt/testnegative_magento.git .
```
**Copy env and config to app/etc**
```
cd ..
mkdir -v -p ./magento/app/etc
mv env.php config.php ./magento/app/etc
```
**Copy media folder from server into magento/pub**
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
- elasticsearch - elasticsearch service
- web - web service

Then after docker containers are all running:
```
# change file permissions for composer.sh
cd mnt
chmod +x composer.sh
cd ..

docker-compose exec fpm /mnt/composer.sh  
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
```
select * from core_config_data where path like '%base%url%';

UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '2';
UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '1436';
UPDATE core_config_data SET value = 'http://testnegative.store/' WHERE config_id = '1652';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '3';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '1438';
UPDATE core_config_data SET value = 'https://testnegative.store/' WHERE config_id = '1653';

exit
```

### Access to the container in order to be able to run the commands of Magento CLI:: (Path: /app)
```
docker exec -it testnegative_fpm_1 bash (Linux)
OR
docker exec -it testnegative-fpm-1 bash (Mac)
```
#### Upgrade Magento:
**NB: during composer install, enter repo.magento.com credentials (username + password) found in auth.json**
```
composer install
php bin/magento config:set catalog/search/engine 'elasticsearch7'
php bin/magento config:set catalog/search/elasticsearch7_server_hostname 'elasticsearch'
php bin/magento config:set catalog/search/elasticsearch7_server_port '9200'
php bin/magento config:set catalog/search/elasticsearch7_index_prefix 'magento2'
php bin/magento config:set catalog/search/elasticsearch5_enable_auth '0'
php bin/magento config:set catalog/search/elasticsearch5_server_timeout '15'
php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento c:c
php bin/magento c:f
php bin/magento maintenance:disable
exit

# Set permission:
cd ./magento
find . -type f -exec chmod 644 {} \; && find . -type d -exec chmod 755 {} \; && find ./var -type d -exec chmod 777 {} \; && find ./pub/media -type d -exec chmod 777 {} \; && find ./pub/static -type d -exec chmod 777 {} \; && chmod 777 ./app/etc && chmod 644 ./app/etc/*.xml && chmod u+x bin/magento
```
Launch **https://testnegative.store** and accept SSL warning in browser

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
