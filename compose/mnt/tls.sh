#!/bin/sh
rm -rf /etc/nginx/ssl/* && ln -s /mnt/magento.crt /etc/nginx/ssl && ln -s /mnt/magento.key /etc/nginx/ssl && nginx -s reload