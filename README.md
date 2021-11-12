# doofinder-magento2

[![Build Status](https://travis-ci.org/doofinder/doofinder-magento2.svg?branch=master)](https://travis-ci.org/doofinder/doofinder-magento2)

**IMPORTANT:** If you are in trouble with the module, please contact Doofinder Support from the Doofinder Admin panel.

## Docker Environment

To use the docker environment clone this repo inside a folder structure like this:

```
some_directory <- Name is not important
  |- packages
     |- doofinder
        |- doofinder-magento2 <- This repo
```

And copy this repo's `docker-compose.yml` file to the root of the base directory (`some_directory` in the example) so you'll have:

```
some_directory <- Name is not important
  |- packages
  |  |- doofinder
  |     |- doofinder-magento2 <- This repo
  |- docker-compose.yml
```

Edit the copy of `docker-compose.yml` and set your tokens in the `COMPOSER_AUTH` environment variable.

Then run the environment by executing:

```
$ docker-compose up
```

from the base directory where the copy of `docker-compose.yml` is located.

Magento 2 with the module installed will be running at `http://localhost:80`.

The admin panel will be available at `http://localhost:80/admin`. Admin credentials are easy:

```
User: admin
Pass: admin123
```

To install sample data, with the containers running:

```
$ docker-compose exec app bash
root@...:~# cd $MAGENTO_PATH
root@...:/var/www/magento# gosu application php -d memory_limit=-1 ./bin/magento sampledata:deploy
root@...:/var/www/magento# gosu application php -d memory_limit=-1 ./bin/magento setup:upgrade
```
