#!/bin/sh

# -- xdebug related scripts --
wget http://xdebug.org/files/xdebug-2.6.0RC2.tgz && tar -xvzf xdebug-2.6.0RC2.tgz && xdebug-2.6.0RC2.tgz
cd xdebug-2.6.0RC2
phpize
./configure
make
sudo cp modules/xdebug.so /usr/lib/php/20170718
sudo cp ../code/20-xdebug.ini.example /etc/php/7.2/fpm/conf.d/20-xdebug.ini
sudo service php7.2-fpm restart

# -- for reference /etc/php/7.2/cli/php.ini  --
# zend_extension = /usr/lib/php/20170718/xdebug.so

# -- project specific scripts --
yarn
# -- update npm in order to properly watch files --
sudo npm install npm@latest -g

# laravel specific
cd ../code
cp .env.example .env
php artisan key:generate
php artisan migrate
npm i
npm run dev
