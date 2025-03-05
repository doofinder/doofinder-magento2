---
title: "Doofinder for Magento 2"
subtitle: "User Manual"
subject: "Doofinder for Magento 2 - User Manual"
keywords: [Doofinder, Magento2, User Manual]
lang: "en"
colorlinks: true
titlepage: true
titlepage-color: "1b184e"
titlepage-text-color: "ffffff"
titlepage-rule-color: "fff031"
titlepage-rule-height: 4
titlepage-logo: "./template/logodoofinder.png"
logo-width: 60mm
header-includes:
- |
  ```{=latex}
  \usepackage{awesomebox}
  ```
pandoc-latex-environment:
  noteblock: [note]
  tipblock: [tip]
  warningblock: [warning]
  cautionblock: [caution]
  importantblock: [important]
...


# Overview

This module provides integration between Magento 2.3+ and the Doofinder search service to enhance the overall search experience in your site.

To do that, your product data is indexed in Doofinder servers and updated whenever a product is changed. When a search occurs, our front-end Javascript layers enhances the user search experience with faster results.


## Support

For technical support, get in touch from your [Doofinder Account](https://admin.doofinder.com/admin/support/contact-us).

If you’re a developer, you can file an issue or contribution request in our public code repository at Github: [https://github.com/doofinder/doofinder-magento2](https://github.com/doofinder/doofinder-magento2).

## Pre-requisites

* This module requires Magento 2.3 or greater.

* Hardware and software requirements are the same as for Magento 2. Check Magento 2 technical requirements for more information.

* This module is heavily based on Cron. Magento 2 requires Cron to properly work, so you must have Cron up and running in your server for the module to work.

* To use the Doofinder module you need an active Doofinder account. Doofinder is a paid service, but you can try it 30 days for free and benefit from the freemium plan if you have a small store. If you don’t have an account yet you can sign up at: [https://www.doofinder.com/signup](https://www.doofinder.com/signup)

Plans and pricing are available at:

[https://www.doofinder.com/price](https://www.doofinder.com/price)

* Doofinder module is a free purchase, so you have to pay nothing to use it. The only cost for you is the price of the Doofinder service once the trial period has ended.

# Installation

Doofinder can be installed via Component Manager as a free purchase done in Magento Marketplace, or via Composer, the de facto PHP package manager, if you feel at home working from the command line directly in your server.

::: warning
Do not install this module via FTP or direct upload to your server’s filesystem. This module has external software dependencies that Component Manager and Composer know how to deal with. If you try to install the module by any other way than recommended you may experience errors in your system.
:::

From the official Magento documentation:

Installing an extension from the Admin is a three-step process that should take place during off-peak hours. Before the extension is installed, your store is put into maintenance mode, checked for readiness, and backed up. After the extension is installed, it must be configured for your store according to the developer’s instructions.

As a best practice, an extension should be installed and tested in a development environment before it is pushed to production.

## Installation via Magento Marketplace

For up-to-date instructions, refer to the [official Magento documentation](https://experienceleague.adobe.com/en/docs/commerce-operations/installation-guide/tutorials/extensions).

Installation via Composer

1. Log into your Magento 2 server via SSH.

2. Go to Magento 2 installation path, for instance:

```
$ cd /var/www/domains/shop.example.com
```

3. Execute:

```
$ composer require doofinder/doofinder-magento2
$ php bin/magento setup:upgrade
```

## Post-Installation

After installing the module, a new "Doofinder" section should have been added to the main menu.

There you will find all the module configuration options.

# Module Configuration

**Read First**

As you will probably know, Magento 2 can be conﬁgured at different levels; you can set up default values and be more speciﬁc for each store view.

This module makes use of that conﬁguration scheme but not all settings are available at all levels, some will be at the default level and some others will be speciﬁc for each store view. You must pay attention, so the module is properly conﬁgured.

Before conﬁguring the module we should clarify some concepts:

* In Dooﬁnder’s slang a search engine is a single container where you can put documents you want to search. You can have multiple search engines in the same account, each with a different unique ID which is called a Hash ID. When you set up search in Magento 2 with the Dooﬁnder module, you associate each store view with its own search engine by using different Hash ID values.

Module conﬁguration is structured in two submenus:

1. Conﬁguration: General conﬁguration of the module, like authentication, search engine identiﬁcators or whether to use the Dooﬁnder Layer in the frontend or not.

2. Initial Setup: from where the initial configuration flow will proceed.

3. Indexes Processing Status: In where you'll find the status of the indexes synchronization.

Almost all module configuration will take place inside the Initial Setup step so please read the next section.

## Configuration Flow

The initial configuration of the module is easy and almost unattended. The whole process happens in the Initial Setup section of the menu, and after going through a couple of steps you'll have Doofinder up and running in no time. In summary:

1. Go into the Initial Setup: clicking in this menu option.

2. Create the system integration: simply by clicking the “Create integration” button.

3. Your website to a Doofinder account: you can log in using an existing account or   register a new one in a very simple way.

4. Make sure all settings have been set correctly

### Go into the Initial Setup

After clicking in the Initial Setup menu option, you will access to an almost unattended configuration process. Only two simple steps are required.


### Create the system integration

Just click on the "Create integration" button to create the system integration necessary to configure our Doofinder module. After a few seconds, the integration will be ready and the next step in the setup flow will be activated.

### Link your website to a Doofinder account

To link your website to a Doofinder account, you can either log in using an existing account or register a new free trial in a very simple way. Just click the appropriate button and fill out the popup form. After submitting this form with the corresponding information, the popup will be closed and the initial setup flow will have ended.

::: note
If you are creating a new account, be sure to use an email that you have not used before. It is a unique field. Also, make sure your website has not previously been linked to a Doofinder account. You can only link your website to one account.
:::

### Make sure all settings have been set correctly

Once you've linked your website to a Doofinder account, the setup process will automatically create search engines, indexes, and all other settings. Finally, the last step is to check if all the configurations were created correctly. You can access these settings by clicking the "Go to Settings" button that appears after you have completed the linking account step above. It is very important that you verify that the Doofinder Layer field is set to "Yes" (default is "Yes"). It is also recommended to clear the system cache to ensure that the module is rendered correctly on the front-end side.

## Configuration

### Account

| API Key Global | We will need an API Key to properly authenticate requests done from your server against Doofinder servers. If you don’t have an API Key: Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual. |
| :---- | :---- |

### Update on save

| Enabled Global | If "Update on save" is enabled when a product is created / updated / deleted this change is sent to Doofinder. |
| :---- | :---- |
| Process changed products Global | If “Update on save” in enabled, with this parameter we configure when registered product changes are sent to Doofinder. Options are: each 5, 10, 15, 30, or 60 minutes. |

### Doofinder Layer

Doofinder provides some Javascript layers to easily integrate search with any website. Those layers will be automatically created in your Magento 2 website. In this section you will have access to the following configuration parameters.

| Enabled Global, Store View | Enable or disable the layer globally or per store view (default: Yes).  |
| :---- | :---- |
| Hash Id Store View | Every store view will have its own Hash Id in order to synchronize data with our servers (Do not modify this unless you are an advanced user). If you don’t have Hash Id: Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual. |
| Installation Id Website | We need an installation ID for every website (Do not modify this unless you are an advanced user). If you don’t have Installation Id: Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual. |
| Script Website | This is the place where you can edit the layer script (Do not modify this unless you are an advanced user). If you don’t have any script: Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual.  |

### Indexes Processing Status

In the main menu section of Doofinder, we have an option called: "Index Processing Status". If we enter this screen we will see a summary with the information of the indexing process for each store view. We will see the current status, if there were any errors (and the error message), and the date and time of the last synchronization.

# Troubleshooting

**Q: I have my indexes conﬁgured to be updated on save - when will the items be indexed?**

**A:** They should be indexed after a while, assuming cron works properly \- indexes are revalidated in case of invalidation in cron no matter of index mode. You can also configure manually the time to be: each 5, 10, 15, 30, or 60 minutes. This parameter can be changed in the global “Update on save” configuration section.

**Q: In the configuration section my API Key, Hash Id and/or my Installation Id are empty**

**A:** Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual.

