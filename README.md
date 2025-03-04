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

> [!IMPORTANT]
> **Get composer credentials**
> It is mandatory to obtain credentials for composer usage. These fields can be obtained by going to [Your magento marketplace account](https://marketplace.magento.com/customer/accessKeys/) and creating an access key. The public key will be `COMPOSER_AUTH_USERNAME` and the private key will be `COMPOSER_AUTH_PASSWORD`. Please fill in `.env` file.

### Initial setup

You can set up a fresh magento installation using provided `Makefile` targets `init` or `init-with-data`. This command will:
- Pulls and build an image with utility scripts for downloading and installing Magento 2 with defined `PHP_VERSION` and `COMPOSER_VERSION` environment variables.
- Runs a magento `create-project` command inside a bind mount into `./app`.
- Starts the containers
- Runs a magento installation with variables defined in `.env` file.
- Optionally: Loads sample data into magento

Finally, Magento 2 with the module installed will be running at `http://MAGENTO_BASE_URL`.

The admin panel will be available at `http://MAGENTO_BASE_URL/admin`. Admin credentials are defined in the `.env`, if you used the `env.example` would be:

- User: `admin`
- Pass: `admin123`

## Xdebug ready to use

If you wish to debug your new Magento installation, simply uncomment the `XDEBUG_CONFIG` environment variable in `docker-compose.yml` configure your IDE accordingly and have fun!


## Varnish was added to manage cache

By default Varnish is commented on docker-compose. So if you need to use it, you can uncomment and restart your containers.
To enable Magento to use Varnish as cache manager, you can follow the official doc from Adobe: [Configure the Commerce application to use Varnish](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/configure-varnish-commerce).

If you uncomment Varnish container, remember to comment the port `9012:80` in the `web` container.

## Uninstall the module

You can remove the Doofinder module using this straightforward method:

```sh
make doofinder-uninstall
```

## Test another versions
Change your branch to the tag that you want inside package directory

```sh
make doofinder-upgrade
```

## Backup and Restore Database

During development, it is sometimes useful to create a data snapshot before performing an action.

- To create a database dump, use:
  ```sh
  make db-backup [prefix=_some_state]
  ```
- To restore a previous state, run:
  ```sh
  make db-restore file=backup_file.sql.gz
  ```

## Last notes

Please take care when you change in `.env` the MAGENTO_VERSION parameter since you'll have to change probably the PHP_VERSION & COMPOSER_VERSION ones in order to maintain the compatibility. For example, if you wish the Magento 2.4.3 version you should have:

```sh
PHP_VERSION=7.4
COMPOSER_VERSION=2.0.14
MAGENTO_EDITION=community
MAGENTO_VERSION=2.4.3
```
but if you want to test, let's say, the 2.3.1 version you should have something like this:

```sh
PHP_VERSION=7.2
COMPOSER_VERSION=1.4.3
MAGENTO_EDITION=community
MAGENTO_VERSION=2.3.1
```

And please, don't forget to copy in `.env` your Magento repository tokens filling the parameters:
```sh
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
