# doofinder-magento2

[![Build Status](https://travis-ci.org/doofinder/doofinder-magento2.svg?branch=master)](https://travis-ci.org/doofinder/doofinder-magento2)

**IMPORTANT:** If you are in trouble with the module, please contact Doofinder Support from the Doofinder website.

## Docker Environment

> **NOTE**: If you are in Windows or WSL, probably you'll have to fix CONTROL-M (^M) carriage return characters in `build.sh` file. Run this command to get rid of this characters:

```
dos2unix build.sh
```

**Configure NGROK**
In order to be able to create an account or login to an existing Doofinder account during the module initial setup, you will have to expose your local webserver to internet (to receive a callback).

To do so, you can use, for example; the utility Ngrok: https://dashboard.ngrok.com/get-started/setup

And once you have the external url created simply edit the `.env` file and set the MAGENTO_BASE_URL={your-url.ngrok-free.app} (for example: MAGENTO_BASE_URL=forcibly-ethical-apple.ngrok-free.app)

So, when the installation process finished, instead of accessing to `http://localhost:9012` you will use your url (for example: `http://forcibly-ethical-apple.ngrok-free.app`).
Notice that you'll need to specify the 9012 port when executing ngrok.

Then setup the environment by executing:

```
$ docker-compose --profile setup up
```

from the base directory where the `docker-compose.yml` is located.
The installation process will take some minutes to be finished. You can follow the status logging with:

```docker logs setup -f```

Finally, Magento 2 with the module installed will be running at `http://localhost:9012`.

The admin panel will be available at `http://localhost:9012/admin`. Admin credentials are defined in the `.env`, if you used the `env.example` would be:

```
User: admin
Pass: admin123
```

To install sample data, with the containers running, you can simply execute:

```
$ ./data_loader.sh
```

In order to make this script work, the only thing you'll need to do is to fill the username and password fields in the `src/auth_json` file, with the same values used previously in the `.env` file, base your file in the `auth.json.sample` file.

OR if you'd rather load the data manually, you can also:

```
$ docker exec -it web bash
root@...:~# cd /app
root@...:/app# php -d memory_limit=-1 bin/magento sampledata:deploy
root@...:/app# bin/magento setup:upgrade
root@...:/app# bin/magento setup:di:compile
root@...:/app# bin/magento setup:static-content:deploy -f
```

**Note:** After you run the ```bin/magento sampledata:deploy``` command you will be prompted for authentication:
```Authentication required (repo.magento.com):```. You will have to use simply the same Magento repository tokens that you used in the `.env` file:
```
COMPOSER_AUTH_USERNAME & COMPOSER_AUTH_PASSWORD
```
These fields can be obtained by going to [Your magento marketplace account](https://marketplace.magento.com/customer/accessKeys/) and creating an access key. The public key will be COMPOSER_AUTH_USERNAME and the private key will be COMPOSER_AUTH_PASSWORD.

## Xdebug ready to use

If you wish to debug your new Magento installation, just simply set the correct values in `.env` and configure your IDE attending to the remote PHP docker container `web`. You should also bind your local source path: `./src` to the docker one: `/app`

## PhpMyAdmin ready to use

Once the installation has finished, you can also access to a ready to use phpMyAdmin local server listening in port 8080: http://localhost:8080.
Here you will see all the Magento 2 tables in the database specified in the file `.env` (by default: magentobase)

## Varnish was added to manage cache

By default Varnish is commented on docker-compose. So if you need to use it, you can uncomment and restart your containers.
To enable Magento to use Varnish as cache manager, you can follow the official doc from Adobe: [Configure the Commerce application to use Varnish](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/configure-varnish-commerce).

If you uncomment Varnish container, remember to comment the port `9012:80` in the `web` container.

## Uninstall the module

You can remove the Doofinder module using this straightforward method:

```
$ docker exec -it web bash
root@...:~# cd /app
root@...:/app# bin/magento module:uninstall Doofinder_Feed --remove-data
```
Manual Uninstall
```
php bin/magento module:disable Doofinder_Feed --clear-static-content
php bin/magento setup:upgrade
php bin/magento cache:flush
remove directory inside src/app/code
```

## Test another versions
Change your branch to the tag that you want inside package directory

Go inside the magento container 
```
cd app
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento module:enable Doofinder_Feed  --clear-static-content
php bin/magento cache:clean
php bin/magento cache:flush
```

## Last notes

Please, take care when you change in `.env` the MAGENTO_VERSION parameter since you'll have to change probably the PHP_VERSION & COMPOSER_VERSION ones in order to maintain the compatibility. For example, if you wish the Magento 2.4.3 version you should have:

```
PHP_VERSION=7.4
COMPOSER_VERSION=2.0.14
MAGENTO_EDITION=community
MAGENTO_VERSION=2.4.3
```
but if you want to test, let's say, the 2.3.1 version you should have something like this:

```
PHP_VERSION=7.2
COMPOSER_VERSION=1.4.3
MAGENTO_EDITION=community
MAGENTO_VERSION=2.3.1
```
And please, don't forget to copy in `.env` your Magento repository tokens filling the parameters:
```
COMPOSER_AUTH_USERNAME=
COMPOSER_AUTH_PASSWORD=
```

## Troubleshooting

**Permissions issues**
After running the `bin/magento setup:upgrade` or other magento commands inside the docker container some folders are created and the user running apache can loose permissions to execute returning 500 Error.
To restore permissions for these folders run in the host terminal `sudo chmod 777 -R src/`.

**Redirect issues**
If after the setup process has finished the website doesn't load you may need to change the urls in the database.
Connect to the database in `localhost:3312` using the mysql user and password defined in the `.env` (`magentobase`).
In the table `core_config_data` there are two configs for the base urls that magento will redirect to, with paths:
- `web/unsecure/base_url`
- `web/secure/base_url`
Make sure that those urls are the ones you'll be using to connect to your site or magento will always redirect to them.
