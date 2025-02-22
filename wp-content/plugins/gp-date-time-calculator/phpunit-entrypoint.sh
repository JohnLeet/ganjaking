#!/bin/bash

# Based on Steve Henty's Gravity Flow docker-entrypoint.sh

# Allows WP CLI to run with the right permissions.
wp-su() {
    sudo -E -u www-data wp "$@"
}

# Clean up from previous tests
rm -rf /wp-core/wp-content/uploads/gravity_forms


# Make sure permissions are correct.
cd /wp

chown -R www-data:www-data wp-content
chmod 755 wp-content

export WP_CLI_CACHE_DIR=/wp-core/.wp-cli/cache

# Make sure the database is up and running.
while ! mysqladmin ping -hmysql --silent; do

    echo 'Waiting for the database'
    sleep 1

done

echo 'The database is ready'

# Make sure WordPress is installed.
if ! $(wp-su core is-installed); then

    echo "Installing WordPress"

    # The development version of Gravity Flow requires SCRIPT_DEBUG
    wp-su core config --dbhost=mysql --dbname=wordpress --dbuser=root --dbpass=wordpress --extra-php="define( 'SCRIPT_DEBUG', true );" --force

    wp-su core install --url=wordpress --title=tests --admin_user=admin --admin_email=test@test.com

fi

if [ -z ${GITHUB_TOKEN} ]; then

    echo "Installing the latest version of Gravity Forms using the CLI"

    wp-su plugin install gravityformscli --force --activate
    wp-su gf install --key=${GF_KEY} --activate --force --quiet
    echo "Gravity Forms installation complete"
    wp-su gf tool verify-checksums

else

    rm -rf /wp-core/wp-content/plugins/gravityforms

    echo "Grabbing the latest development master of Gravity Forms"

    git clone -b master --single-branch https://$GITHUB_TOKEN@github.com/gravityforms/gravityforms.git /wp-core/wp-content/plugins/gravityforms

fi

echo "Installing Subversion..."
sudo apt-get update
sudo apt-get install -y subversion

cd /wp/wp-content/plugins/gp-date-time-calculator

echo "Running Composer Install..."
composer install

echo "Installing WP Tests..."
bash bin/install-wp-tests.sh unit-tests root wordpress mysql

echo "Running PHPUnit..."
./vendor/bin/phpunit
