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

For up-to-date documentation, please, visit: [Doofinder Magento Installation Guide](https://support.doofinder.com/plugins/magento/installation-guide/pre-requisites-magento).

## Support

For technical support, get in touch from your [Doofinder Account](https://admin.doofinder.com/admin/support/contact-us).

If you’re a developer, you can file an issue or contribution request in our public code repository at Github: [https://github.com/doofinder/doofinder-magento2](https://github.com/doofinder/doofinder-magento2).

# Pre-requisites Magento

- This module is compatible with Magento versions 2.3 through 2.4.7.
- This module is compatible with Hyvä.
- This module is heavily based on Cron. Magento 2 requires Cron to work properly, so you must have Cron up and running in your server for the module to work.
- Activate the following option in your Magento admin: **Stores > Configuration > Services > OAuth > Consumer Settings > Allow OAuth Access Tokens to be used as standalone Bearer tokens: YES** (this option is only available if you have a Magento 2.4.4 or later).

![Allow OAuth Access Tokens in Magento](https://support.doofinder.com/images/magento-token.png)

# Installation Steps Magento

Doofinder can be installed via Component Manager as a free purchase done in [Magento Marketplace ](https://marketplace.magento.com/doofinder-doofinder-magento2.html), once downloaded you need to follow these steps in composer.

::: warning
Do not install this module via FTP or direct upload to your server’s filesystem. This module has external software dependencies that Component Manager and Composer know how to deal with. If you try to install the module by any other way than recommended you may experience errors in your system.
:::

## Installation Via Composer

To install the module through composer:

`composer require doofinder/doofinder-magento2`

The module should now be installed and working on the platform.

To activate it, run the following command in the Magento root folder:

`bin/magento setup:upgrade`

A new "Doofinder" menu should have been added to your Magento Admin Panel.

## Initial Setup

Click on the Doofinder icon under Stores to display the option's menu and select 'Initial Setup'.

![Magento initial setup](https://support.doofinder.com/images/magento-1.png)

1. Select your Store's sector.

   Click on dropdown menu to select your store's sector.

   After a few seconds, the integration will be ready and the next step of the configuration flow will be activated.

2. ![Magento initial setup](https://support.doofinder.com/images/magento-new.png)

   Link your website to a Doofinder Account.

   To link your website to a Doofinder account, you can log in with an existing account or register for a new free trial very easily.

   ![Magento link website](https://support.doofinder.com/images/magento-4.png)

   Simply click on the corresponding button and fill in the pop-up form. Once submitted, the pop-up window will close and the initial setup flow will be finished.

   ![Magento link website](<https://support.doofinder.com/images/Magento 2_3.png>)

::: note
   If you are creating a new account, be sure to use an email address that you have not used before. This is a unique field. Also, make sure that your website has not been previously linked to a Doofinder account. You can only link your website to one account.
:::

   ![Magento acocunt linked](https://support.doofinder.com/images/magento-5.png)

   Once you have linked your website to a Doofinder account, the configuration process will automatically create the Search Engines, indexes and all other settings.

   ![Magento initial setup](https://support.doofinder.com/images/magento-6.png)

   On the following screen, you will have two options:

3. ![Magento indexing](https://support.doofinder.com/images/magento-7.png)

   Check Index processing status

   Here you will find the current status of the indexing process, whether it is OK or there is an error (and the corresponding message), and the date and time of the last synchronization.

   Check the following example of a successful indexing process:

4. ![Magento indexing status](https://support.doofinder.com/images/magento-8.png)

   Go to Configuration

   In this section, you can check if all settings have been created correctly.

   Once all the products are indexed the doofinder layer option will be set to yes.

![Magento module](https://support.doofinder.com/images/magento-config.png)

## Module Configuration Fields

- **Account**: In here you will find your API KEY.
- **Doofinder Layer**: You can activate or deactivate the search layer by selecting YES or NO.
- **Image Configuration**: Select the size of the image to display on the layer. Once the size has been chosen, you must reindex the products in Doofinder.
- **Automatic Indexing**: you can configure when registered product changes are sent to Doofinder and whether to export only categories present in navigation menus.
- **Manual Indexing**: Advanced option for indexing.
- **Custom Attributes**: Attributes selected as 'Enabled' will be included in the feed indexation.
- **Doofinder Integration Configuration (Advanced)**: you will be able to reset the integration, in order to re-launch the setup wizard if necessary.

## Layer

The layer will be automatically created in your Magento 2 website.

Magento 2 works with our Live Layer and Fullscreen layer by default.

Once the indexing is finished, the layer will be enabled. You can enable or disable the layer in your **Magento Store > Doofinder > Configuration > Account > Doofinder Layer**.

![Magento enable layer](https://support.doofinder.com/images/magento-10.png)

::: warning
This module no longer works with V7 and Embedded Layer.
:::

## Installation ID And Script

The Installation ID and the Script are generated automatically during the installation process.

# Uninstallation Magento

## Version from 0.5.0

You can remove the Doofinder module using this straightforward method:

`www-data@...:/app# bin/magento module:uninstall Doofinder_Feed --remove-data`

## Version up to 0.4.14 (included)

To uninstall the module, execute the following command on the root folder where your Magento is installed:

`bin/magento module:uninstall Doofinder_Feed Doofinder_FeedCompatibility`

If there is any problem with the uninstalling process of the plugin, you need to make sure that:

- The composer tool is accessible by Magento (type down composer in your terminal and see if there is any response)
- The established memory limit for your PHP is equal or higher than 4 GB (this is configured in your php.ini file)

Verify this module is no longer in your installed modules list. To check this out, run:

`bin/magento module:status Doofinder_Feed Doofinder_FeedCompatibility`

and check the response.

# Custom Fields - Magento

You can configure the custom attributes you want to display by enabling them in your plugin under **Doofinder > Configuration > Custom Attributes:**

![enable magento attributes](https://support.doofinder.com/images/magento-ca.png)

# Recommendations - Magento 2

## Magento 2 - Recommendations Script

If you are using Magento 2, the Recommendations Script for required JS is different. You need to replace the first line in the default script found in the recommendations section with the following:

### Default Script

![default recommendations script](https://support.doofinder.com/images/recom-magento.png)

### New Script Line

```html
<script>
  var dfUrl = 'https://cdn.doofinder.com/recommendations/js/doofinderRecommendation.min.js';
  (function(c,o,k,e){var r,t,i=setInterval(function(){t+=c;r=typeof(require)==='function';
  if(t>=o||r)clearInterval(i);if(r)require([k],e)},c)})(100, 10000, dfUrl);
</script>
```

### Final Script

The final script should look like this:

```html
<script>
  var dfUrl = 'https://cdn.doofinder.com/recommendations/js/doofinderRecommendation.min.js';
  (function(c,o,k,e){var r,t,i=setInterval(function(){t+=c;r=typeof(require)==='function';
  if(t>=o||r)clearInterval(i);if(r)require([k],e)},c)})(100, 10000, dfUrl);
</script>
<df-recommendations
  hashid="5bdf17409acaa64fd36fc4fcf7c25820"
  total-products="10">
</df-recommendations>
```

## How to add the Recommendation widget to a Magento page

::: note
Deprecated feature. Refer to the following [documentation](https://support.doofinder.com/pages/recommendations.html) for updated information on the Recommendations product.
:::

1. Login into your Magento Admin Panel.
2. On the left side menu, look for **Content > Pages**.
3. ![recommendations magento 2](https://support.doofinder.com/images/recomm-magento2.png)

   Locate the Page you want to add the widget to, go to **Action > Select > click on Edit**. Make sure to check if the page is _Enabled_.
4. ![recommendations magento 2](https://support.doofinder.com/images/recomm-magento2-2.png)

   Open the **Content** section, and click on **Edit with Page Builder**.
5. On the left side menu, search for the element **Elements**. Select and drag the **HTML Code** block to the page section you desire.
6. Once placed, click on the wheel icon to Edit the HTML Code.
7. Copy and Paste the **Final Code** you see on this article above. Remember **you must replace the Hash ID with the Hash ID from your website**.
8. Remember to save the changes.

![recommendations magento 2](https://support.doofinder.com/images/recomm-magento2-3.gif)

::: note
Please note that in order to work properly, the Recommendations must also be activated in your [Doofinder Admin Panel.](https://support.doofinder.com/recommendations/recommendations-deprecated.html)
:::

# How To Index Your Catalog With Magento

There are two options to index your catalog with Magento: Update on save, or API.

Follow these prerequisites before indexing your catalog.

**For Update on save:**

- This module is heavily based on Cron. Magento 2 requires Cron to work properly, so you must have Cron up and running on your server for the module to work.

**Indexing through API catalog:**

- The Setup wizard of the module generates an access token to allow the module to index the catalog using Magento’s API.

- The access token needs to be valid. Revise it and activate it in your Magento admin. Go to **Stores** > **Configuration** > **Services** > **OAuth** > **Consumer Settings** > Allow OAuth Access Tokens to be used as standalone Bearer tokens: YES (this option is only available if you have a Magento 2.4.4 or later).

## Catalog Indexing

Your Catalog will be automatically indexed once a day with Doofinder. By default, Doofinder indexes product data daily when the [automatic option is enabled.](https://support.doofinder.com/managing-data/indexing-options)

However, this feature allows you to schedule the index tasks as a CRON. **The system utility Cron can be used to schedule programs to run automatically at predetermined intervals**. In this case, you can choose between indexing your Catalog once a day, periodically, configuring a time interval between indexes, or scheduling specific timing (maximum six times a day).

In case of another preferred indexing option, like indexing every two hours or five minutes, it is necessary to configure Automatic Indexing (Update on save).

## 1. Update on save

The _Update on save_ option sends only the modifications made to the catalog via the API, ensuring that the indexed data in Doofinder is updated accordingly.

_Automatic Indexing (Update on save)_ is **disabled by default**. To enable the Update on save function go to your **Magento module** > click on the **Store** tab > open **Configuration**. You will find all sections related to settings. Unfold **Doofinder’s tab** > click on **Configuration**.

Once there, go to the **Automatic Indexing** section > **Automatically process modified products** > choose how many times a day you would like your changes to be sent to Doofinder for indexation > _Save Config_.

Notice that with Magento automatic indexation will only occur if changes have been made. Magento’s Automatic Indexing is compatible with Doofinder’s regular indexation if enabled.

![Allow OAuth Access Tokens in Magento](https://support.doofinder.com/images/update-onsave-magento.png)

## 2. Manual Indexing

If preferred, you can always update your changes manually within Magento using _Manual Indexing (advanced)_ instead of configuring automatic indexing. Just click on Manual Indexing and accept. As with automatic indexing, manual indexation will only occur if changes have been made.

::: note
Manual Indexing and Update on save options are compatible and available to use simultaneously with Doofinder indexing.
:::

## Indexation of Custom Attributes

You can configure the custom attributes you want to display. Find [_this_](https://support.doofinder.com/plugins/magento/installation-guide/custom-fields-magento.html) helpful documentation about Custom Fields on Magento and how to manage them.

# Troubleshooting

**Q: I have my indexes conﬁgured to be updated on save - when will the items be indexed?**

**A:** They should be indexed after a while, assuming cron works properly \- indexes are revalidated in case of invalidation in cron no matter of index mode. You can also configure manually the time to be: each 5, 10, 15, 30, or 60 minutes. This parameter can be changed in the global “Update on save” configuration section.

**Q: In the configuration section my API Key, Hash Id and/or my Installation Id are empty**

**A:** Go to the Doofinder options menu "Initial Configuration" and follow the steps described in the Configuration Flow section of this manual.

**Q: I Can't See My Layer**

**A:** If you can't see the Layer in your store, you probably need to clean the cache after installing the module. Go to your Admin Panel > System > Cache Management and choose Flush Magento Cache.
