## docker_testnegative install directory
Create working drectory for docker install with magento locally 
*For within the new directory*, run:
'''curl -s https://raw.githubusercontent.com/roblefort/docker_testnegative/main/lib/template | bash

Copy env.php to ./magento/app/etc/

Create docker containers
'''docker-compose up -d

'''cd mnt
'''chmod +x composer.sh
Then run:
'''docker-compose exec fpm /mnt/composer.sh  (After start all containers run)

Copy media content archive from staging magento server to ./magento/pub/media/
