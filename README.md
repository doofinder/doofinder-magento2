# Doofinder for Magento 2

[![Build Status](https://travis-ci.org/doofinder/doofinder-magento2.svg?branch=master)](https://travis-ci.org/doofinder/doofinder-magento2)

**IMPORTANT:** If you are in trouble with the module, please [contact Doofinder Support](https://support.doofinder.com/pages/contact-us) from the Doofinder website.

## Docker Environment

### Configure ngrok
In order to be able to create an account or login to an existing Doofinder account during the module initial setup, you will have to expose your local webserver to the internet (to receive a callback).

To do so, you can use, for example, the utility ngrok: https://dashboard.ngrok.com/get-started/setup

Once the external URL is created, simply set the `MAGENTO_BASE_URL` environment variable (see [Environment Variables](#environment-variables)).

So, when the installation process finished, instead of accessing to `http://localhost:9012` you will use your url, for example, `http://forcibly-ethical-apple.ngrok-free.app`).
Notice that you'll need to specify the 9012 port when executing ngrok.

### Get composer credentials
> [!IMPORTANT]
> It is mandatory to obtain credentials for composer usage. These fields can be obtained by creating an access key into [your Magento marketplace account](https://marketplace.magento.com/customer/accessKeys/). The public key will be `COMPOSER_AUTH_USERNAME` and the private key will be `COMPOSER_AUTH_PASSWORD` environment variables (see [Environment Variables](#environment-variables)).

### Environment variables

> [!TIP]
> You can create an `.env.local` file to override the environment variables defined in `.env` such as composer credentials or Magento installation data to fit your needs.

For example, below is a base `.env.local` file:

```bash
#Magento setup configuration data
MAGENTO_BASE_URL=your-url.ngrok-free.app

#Tokens for the Magento composer repository
COMPOSER_AUTH_USERNAME=YOUR_COMPOSER_PUBLIC_KEY
COMPOSER_AUTH_PASSWORD=YOUR_COMPOSER_PRIVATE_KEY
```

The `Makefile` automatically overrides `.env` vars with the ones found in `.env.local`.

> [!IMPORTANT]
> The `Makefile` internally appends `--env-file .env --env-file .env.local` to `docker compose` command for properly configuring container environment. So take it into account when interacting directly with `docker compose`.


### Initial setup

You can set up a fresh Magento installation using the provided `Makefile` targets `init` or `init-with-data`. This command will:
- Pulls and build an image with utility scripts for downloading and installing Magento 2 with defined `PHP_VERSION` and `COMPOSER_VERSION` environment variables.
- Runs a Magento `create-project` command inside a bind mount into `./app`.
- Starts the containers
- Runs a Magento installation with variables defined in the environment through `.env` or `.env.local` file.
- Optionally: Loads sample data into Magento

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

## PHP compatibility

This plugin has been thoroughly tested and confirmed to be compatible with the following PHP versions:

✅ Supported PHP Versions:

- PHP 7.3
- PHP 7.4
- PHP 8.1
- PHP 8.2
- PHP 8.3
- PHP 8.4

⚠️ Note:

- PHP versions below 7.4 are not recommended.
- PHP 8.0 is not supported by Magento 2.

## Tested compatibility with the following M2 versions

- Magento 2.3.0
- Magento 2.3.5-p3
- Magento 2.3.7-p4
- Magento 2.4.0
- Magento 2.4.1
- Magento 2.4.2
- Magento 2.4.3
- Magento 2.4.4
- Magento 2.4.5
- Magento 2.4.6
- Magento 2.4.7
- Magento 2.4.8

## Last notes

Please take care when you change the environment variable `MAGENTO_VERSION`, since you will have to change probably the `PHP_VERSION` and the `COMPOSER_VERSION` ones in order to maintain the compatibility. For example, if you wish the Magento 2.4.3 version you should have:

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

## Troubleshooting

**Redirect issues**
If after the setup process has finished the website doesn't load you may need to change the urls in the database.
Connect to the database in `localhost:3312` using the mysql user and password defined in the `.env` (`magentobase`).
In the table `core_config_data` there are two configs for the base urls that Magento will redirect to, with paths:
- `web/unsecure/base_url`
- `web/secure/base_url`
Make sure that those urls are the ones you'll be using to connect to your site or Magento will always redirect to them.
