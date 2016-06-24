#!/usr/bin/env bash

echo "Start Drush Downloads"
drush dl ctools-7.x-1.x-dev
drush dl commerce-7.x-1.11
echo "Start Drush Commerce Enable"
drush en -y commerce_tax, commerce_price, commerce_cart, commerce_product, commerce_order, commerce