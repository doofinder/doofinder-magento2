# doofinder-magento2

[![Build Status](https://travis-ci.org/doofinder/doofinder-magento2.svg?branch=develop)](https://travis-ci.org/doofinder/doofinder-magento2)

## Docker Environment

First of all copy `.env.example` to `.env` and modify it to add your keys for `repo.magento.com` to it (Composer).

Then run the environment by executing:

```
$ docker-compose up
```

Magento 2 with the module installed will be running at `http://localhost:8080`.

The admin panel will be available at `http://localhost:8080/admin`. Admin credentials are easy:

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
