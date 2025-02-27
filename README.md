# Doofinder for Magento 2

[![Build Status](https://travis-ci.org/doofinder/doofinder-magento2.svg?branch=master)](https://travis-ci.org/doofinder/doofinder-magento2)

**IMPORTANT:** If you are in trouble with the module, please [contact Doofinder Support](https://support.doofinder.com/pages/contact-us) from the Doofinder website.

## Docker Environment

**Configure NGROK**
In order to be able to create an account or login to an existing Doofinder account during the module initial setup, you will have to expose your local webserver to internet (to receive a callback).

To do so, you can use, for example; the utility Ngrok: https://dashboard.ngrok.com/get-started/setup

And once you have the external url created simply edit the `.env` file and set the MAGENTO_BASE_URL={your-url.ngrok-free.app} (for example: MAGENTO_BASE_URL=forcibly-ethical-apple.ngrok-free.app)

So, when the installation process finished, instead of accessing to `http://localhost:9012` you will use your url (for example: `http://forcibly-ethical-apple.ngrok-free.app`).
Notice that you'll need to specify the 9012 port when executing ngrok.

**Get composer credentials**
It is mandatory to obtain credentials for composer usage. These fields can be obtained by going to [Your magento marketplace account](https://marketplace.magento.com/customer/accessKeys/) and creating an access key. The public key will be `COMPOSER_AUTH_USERNAME` and the private key will be `COMPOSER_AUTH_PASSWORD`. Please fill in `.env` file.

### Initial setup

You can setup a fresh magento installation using provided `Makefile` targets `setup`or `setup-with-data`. This command will:
- Pull the images
- Build a base Magento 2 image with defined `PHP_VERSION`, `MAGENTO_EDITION` and `MAGENTO_VERSION` environment variables.
- Run a magento installation with variables defined in `.env` file.
- Optionally: Load sample data into magento
- Spin up services


Finally, Magento 2 with the module installed will be running at `http://MAGENTO_BASE_URL:9012`.

The admin panel will be available at `http://MAGENTO_BASE_URL:9012/admin`. Admin credentials are defined in the `.env`, if you used the `env.example` would be:

```
User: admin
Pass: admin123
```

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
make uninstall-doofinder
```

## Test another versions
Change your branch to the tag that you want inside package directory

```
make upgrade-doofinder
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

**Redirect issues**
If after the setup process has finished the website doesn't load you may need to change the urls in the database.
Connect to the database in `localhost:3312` using the mysql user and password defined in the `.env` (`magentobase`).
In the table `core_config_data` there are two configs for the base urls that magento will redirect to, with paths:
- `web/unsecure/base_url`
- `web/secure/base_url`
Make sure that those urls are the ones you'll be using to connect to your site or magento will always redirect to them.
