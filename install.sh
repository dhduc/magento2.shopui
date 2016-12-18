#!/bin/bash

# Define variable
ROOT_DIR=$(pwd)
VHOST='magento2sp.conf'
DOMAIN='magento2sp.local'
MAGENTO_COMPOSER_USER='de29f7e34652c79c64fca31ec90f5ee8'
MAGENTO_COMPOSER_PASS='8d967504bee096c98de281e5818d039a'

FIRSTNAME='Duc'
LASTNAME='Dao'
EMAIL='huuduc.uneti@gmail.com'
DB_HOST='localhost'
DB_NAME='magento2sp'
DB_USER='root'
DB_PASSWORD='root'

ENDC=`tput setaf 7`
RED=`tput setaf 1`
GREEN=`tput setaf 2`
BLUE=`tput setaf 3`

init() {
	echo $GREEN 'Install and setup magento 2 project' $ENDC
	echo $BLUE 'Prerequires: \nNginx \nMySQL 5.6 \nPHP 7.0 \nNodejs 0.10.x' $ENDC

	echo $GREEN'Are you sure to continue (y/n)?'$ENDC
	read -e TERM
	if [[ $TERM = 'n' || $TERM = 'N' ]]; then
		exit
	fi

	git config core.fileMode false	
}

setupComposer()
{
	echo $GREEN 'Setup Composer' $ENDC
	composer config --global --auth http-basic.repo.magento.com $MAGENTO_COMPOSER_USER $MAGENTO_COMPOSER_PASS
	composer install
}

setupAcl() {
	echo $GREEN 'Setup ownership and permissions for' $ROOT_DIR $ENDC	
	HTTPDUSER='www-data'
	sudo chown -R `whoami`:"$HTTPDUSER" .
	sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var pub/static pub/media app/etc
	sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var pub/static pub/media app/etc
	find . -type d -exec chmod 775 {} \; && find . -type f -exec chmod 664 {} \;
	chmod u+x bin/magento install.sh
}

setupMagento() {
	echo $GREEN 'Install Magento 2' $ENDC

	php bin/magento setup:install \
	  --admin-firstname=$FIRSTNAME \
	  --admin-lastname=$LASTNAME \
	  --admin-email=$EMAIL \
	  --admin-user=admin \
	  --admin-password='admin123' \
	  --base-url='http://'$DOMAIN \
	  --backend-frontname=admin \
	  --db-host=$DB_HOST \
	  --db-name=$DB_NAME \
	  --db-user=$DB_USER \
	  --db-password=$DB_PASSWORD \
	  --language=en_US \
	  --currency=USD \
	  --timezone='Asia/Ho_Chi_Minh' \
	  --admin-use-security-key=0 \
	  --session-save=files
}

setupVhost()
{
	echo $GREEN 'Setup Nginx virtual host' $ENDC
	if [ -s /etc/nginx/conf.d/$VHOST ]; then
		sudo rm -rf /etc/nginx/conf.d/$VHOST
	fi
	sudo cp nginx.conf /etc/nginx/conf.d/$VHOST
	sudo sh -c -- "echo '127.0.0.1 ${DOMAIN}' >> /etc/hosts"
}

setupNode() {
	echo $GREEN 'Setup Grunt & Nodejs dev dependencies' $ENDC
	if [ -s 'Gruntfile.js.sample' ]; then
		cp Gruntfile.js.sample Gruntfile.js
	fi
	if [ -s 'package.json.sample' ]; then
		cp package.json.sample package.json
		npm install -g grunt-cli
		npm install --save-dev grunt
		npm install
	fi
}

setupTask() {
	php bin/magento deploy:mode:set developer
	php bin/magento module:enable --all
	php bin/magento setup:upgrade
	php bin/magento setup:static-content:deploy
	php bin/magento indexer:reindex
	php bin/magento cache:clean
	php bin/magento cache:flush
}

finish() {
	echo $GREEN 'Restart Nginx and PHP service' $ENDC
	sudo service php7.0-fpm restart
	sudo service nginx restart
	echo $GREEN 'Go to' http://$DOMAIN $ENDC
}

init &&
setupComposer &&
setupAcl &&
setupMagento &&
setupVhost &&
setupNode &&
setupTask &&
finish