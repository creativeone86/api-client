#!/bin/sh


# -- xdebug related scripts --
# wget http://xdebug.org/files/xdebug-2.6.0RC2.tgz && tar -xvzf xdebug-2.6.0RC2.tgz && xdebug-2.6.0RC2.tgz
# cd xdebug-2.6.0RC2
# phpize
# ./configure
# make
# sudo cp modules/xdebug.so /usr/lib/php/20170718

# -- Edit /etc/php/7.2/cli/php.ini and add the line --
# zend_extension = /usr/lib/php/20170718/xdebug.so

# -- project specific scripts --
yarn
# -- update npm in order to properly watch files --
sudo npm install npm@latest -g