# Doofinder for Magento 2

![Release](https://img.shields.io/github/v/release/doofinder/doofinder-magento2?style=flat-square)
![Magento](https://img.shields.io/badge/Magento-2.3%20--%202.4-f46f25?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-7.3%20--%208.4-777bb4?style=flat-square)
![License](https://img.shields.io/github/license/doofinder/doofinder-magento2?style=flat-square)

**Transform your Magento 2 search into a conversion machine.** Join thousands of merchants using AI-powered search to increase sales and improve customer experience.

![Doofinder in Action](https://github.com/user-attachments/assets/cac4ec30-02e4-4280-8ba4-8a738ab823f1)

[🚀 Get Started for Free](https://www.doofinder.com/en/solutions/magento) | [🖥️ Live Demo](https://magento.doofinder.com/) | [📖 Full Documentation](https://support.doofinder.com/plugins/magento/installation-guide/installation-steps-magento)

---

## Why Doofinder?

Doofinder turns your basic search bar into an advanced discovery engine. Using AI-powered searchandising and recommendations, we drive measurable gains in conversion and product discovery.

### Key Features

* **AI Assistant** — A smart shopping guide that helps customers find products through natural conversation.
* **AI Smart Search** — Understands intent and handles typos or synonyms effortlessly.
* **Searchandising** — Boost, hide, or pin products to run targeted campaigns.
* **Personalized Recommendations** — Intelligent cross-selling based on real customer behavior.
* **Visual Search** — Let your shoppers find products using images.
* **Auto-Indexing** — Your catalog stays in sync automatically as you scale.

---

## 🛠 Installation & Quick Start

**From Adobe Marketplace**
Install [Doofinder from the Adobe Commerce Marketplace](https://marketplace.magento.com/doofinder-doofinder-magento2.html) directly from your Magento admin, or via Composer:

```bash
composer require doofinder/doofinder-magento2
bin/magento setup:upgrade
```

**From GitHub (latest release)**
Download the [latest release zip](https://github.com/doofinder/doofinder-magento2/releases) and install it as a local Composer package, or copy directly into `app/code/Doofinder/Feed`.

**Then**
Complete setup using our [step-by-step installation guide](https://support.doofinder.com/plugins/magento/installation-guide/installation-steps-magento).

**Requirements**

| | Supported versions |
| -- | -- |
| PHP | 7.3, 7.4, 8.1, 8.2, 8.3, 8.4 (8.0 not supported by Magento 2) |
| Magento | 2.3.x, 2.4.x |

---

## 👨‍💻 Development & Maintainer Guide

This repository is optimized for local development using a **Makefile** and **Docker**.

**`.env`** sits at the repo root and powers both your **Docker** stack and the **generated module files** (what `doofinder-configure` pulls from `templates/`). Create **`.env.local`** for local overrides (credentials, URL, etc.) — the Makefile loads `.env` first, then `.env.local` on top. Then run `make init`.

> [!NOTE]
> `make doofinder-configure` regenerates `Doofinder/Feed/etc/config.xml` and `Doofinder/Feed/Helper/Constants.php` from `templates/`. Many other targets depend on it — do not commit those files with non-production values.

### Environment and shop access

The most common variables to set before `make init`:

| Variable | Role |
| -------- | ---- |
| `BASE_URL` | Shop hostname (your tunnel URL, e.g. `${HANDLER}-magento.ngrok.doofinder.com`). |
| `COMPOSER_AUTH_USERNAME` | Public key from your [Magento Marketplace access key](https://marketplace.magento.com/customer/accessKeys/). |
| `COMPOSER_AUTH_PASSWORD` | Private key from the same access key. |
| `MAGENTO_VERSION` | Magento release to install (adjust `PHP_VERSION` and `COMPOSER_VERSION` to match). |

After `make init`, the storefront is at `http://BASE_URL` and the admin panel at `http://BASE_URL/admin` (default credentials: `admin` / `admin123`).

### Common make targets

| Target | What it does |
| ------ | ------------ |
| `make init` | Fresh Magento install + module setup. |
| `make init-with-data` | Same as above, with sample data loaded. |
| `make doofinder-upgrade` | Switch the module to a different branch/tag. |
| `make doofinder-uninstall` | Remove the Doofinder module. |
| `make db-backup [prefix=_name]` | Snapshot the database. |
| `make db-restore file=backup.sql.gz` | Restore a previous snapshot. |
| `make consistency` | Run PHP Code Sniffer inside Docker (`Magento2` standard). |

**Xdebug** is pre-configured via `XDEBUG_CONFIG` in `docker-compose.yml` — configure your IDE to listen and start debugging.

**Varnish** is included but commented out in `docker-compose.yml`. Uncomment to enable; remember to comment the `9012:80` port in the `web` container.

To enable Magento to use Varnish as cache manager, you can follow the official doc from Adobe: [Configure the Commerce application to use Varnish](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/configure-varnish-commerce).

---

## Compatibility Matrix

| Magento | PHP |
| ------- | --- |
| 2.4.5 – 2.4.8 | 8.1, 8.2, 8.3, 8.4 |
| 2.4.0 – 2.4.4 | 7.4, 8.1, 8.2 |
| 2.3.x | 7.3, 7.4 |

> PHP 8.0 is not supported by Magento 2. PHP versions below 7.4 are not recommended.

---

## Support & Contributing

* **Need Help?** Visit our [Support Portal](https://support.doofinder.com/).
* **Found a Bug?** Please [contact Doofinder Support](https://support.doofinder.com/pages/contact-us).
* **Want to contribute?** PRs are welcome! Before pushing, make sure PHP Code Sniffer passes — use `make consistency` (see targets above) or run `composer install && vendor/bin/phpcs` directly (requires PHP >= 8.3).

**If you find this plugin useful, please give us a ⭐ to support the project!**
