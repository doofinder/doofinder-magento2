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

## Stores and Search Engines: How They Map to Magento Concepts

Magento 2 has three levels of scope that are easy to confuse with Doofinder's own concepts.

### Magento's scope hierarchy

| Magento level | What it represents |
|---|---|
| **Website** | A logical grouping of stores, usually representing a brand or domain. Shares customer accounts and order data. |
| **Store** (Store Group) | A catalog scope within a website. Each store group has its own root category. Not directly visible to customers. |
| **Store View** | The customer-facing storefront. Each store view has its own language (locale) and can have a different currency. This is what the URL points to. |

### What is a Doofinder Store in this plugin?

A **Doofinder Store** corresponds to a Magento **Store Group** (the middle level, often just called "Store" in the Magento admin).

One Doofinder store is created per Magento Store Group. The Doofinder store's installation ID is saved at the Store Group scope in `core_config_data` (`doofinder_config_config/doofinder_layer/installation_id`). This means the Magento **Website** level is skipped entirely for the purposes of Doofinder store creation.

### What is a Doofinder Search Engine in this plugin?

A **Doofinder Search Engine** corresponds to a Magento **Store View**.

Each store view gets its own search engine hash ID, stored at the Store View scope (`doofinder_config_config/doofinder_layer/hash_id`). However, if two store views within the same Store Group share the exact same locale and currency, the plugin deduplicates them and assigns them the same search engine rather than creating a redundant one.

The search engine name follows the pattern `{Store Group Name} - {Store View Name}`.

### Summary mapping

```
Magento Website
  └── Store Group  ──────────────→  Doofinder Store  (installation_id, stored at group scope)
        ├── Store View (en_US / USD)  ──→  Search Engine  (hash_id, stored at store view scope)
        ├── Store View (de_DE / EUR)  ──→  Search Engine  (hash_id, stored at store view scope)
        └── Store View (en_US / EUR)  ──→  Search Engine  (hash_id, or shared if locale+currency match)
```

### When are they created?

**Doofinder Stores and Search Engines are created from the Magento admin panel**, not automatically on install. There are two ways to trigger creation:

1. **"Create Store" button** — available in the Doofinder configuration section (`Stores > Configuration > Doofinder`). Clicking it sends an AJAX request to `doofinderfeed/integration/createStore` which calls the Doofinder Management API (`POST /install`) for each Store Group that does not yet have an installation ID. All search engines for that Store Group (one per unique locale/currency combination) are created in the same API call.

2. **"Create Search Engine" button** — shown per Store View in the same admin config section when a store view does not yet have a hash ID. Clicking it sends an AJAX request to `doofinderfeed/integration/createSearchEngine/store/{storeId}` which calls `POST /install/search-engine` on the Management API and saves the returned hash ID at the Store View scope.

### Callbacks

When Doofinder finishes processing a new search engine (indexation), it sends a POST callback to the URL registered during creation:

```
{base_url}/rest/{store_code}/V1/doofinderfeed/setup/processCallback?storeId={store_id}
```

This endpoint is handled by `Doofinder/Feed/Controller/Setup/ProcessCallback.php` and updates the indexation status at the Store View scope.

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

**`.env`** sits at the repo root and powers both your **Docker** stack and the **generated module files** (what `doofinder-configure` pulls from `templates/`). It ships with sensible defaults — set your `BASE_URL`, Magento version, and Composer auth credentials, then `make init`. Optional overrides go in **`.env.local`**, which loads on top of `.env`.

> [!NOTE]
> `make doofinder-configure` regenerates `Doofinder/Feed/etc/config.xml` and `Doofinder/Feed/Helper/Constants.php` from `templates/`. Many other targets depend on it — do not commit those files with non-production values.

### Environment and shop access

The root **`.env`** lists all variables with comments. For the **dev stack**, these are the ones you usually touch first:

| Variable | Role |
| -------- | ---- |
| `BASE_URL` | Shop hostname as seen by Docker (no `https://`). |
| `MAGENTO_VERSION` | Magento release to install. Adjust `PHP_VERSION` and `COMPOSER_VERSION` to match — see the compatibility matrix below. |
| `COMPOSER_AUTH_USERNAME` | Public key from your [Magento Marketplace access key](https://marketplace.magento.com/customer/accessKeys/). |
| `COMPOSER_AUTH_PASSWORD` | Private key from the same access key. |
| `MYSQL_*` | Database credentials for the local shop. |
| `MAGENTO_ADMIN_USER` / `MAGENTO_ADMIN_PASSWORD` | Admin panel login after install. |
| `XDEBUG_HOST` | Docker bridge IP (`172.17.0.1` on Linux; use `host.docker.internal` on macOS). |

**Default access (Docker dev stack):** After **`make init`**, the stack runs on the ports defined in `docker-compose.yml` (default mapping: **9012** → HTTP, **4012** → HTTPS). With the default `BASE_URL=localhost` from `.env`:

| | URL |
| -- | -- |
| Storefront (HTTP) | `http://localhost:9012/` |
| Storefront (HTTPS) | `https://localhost:4012/` |
| Admin (HTTP) | `http://localhost:9012/admin` |
| Admin (HTTPS) | `https://localhost:4012/admin` |

Admin login is **`MAGENTO_ADMIN_USER`** / **`MAGENTO_ADMIN_PASSWORD`** from `.env` (defaults: `admin` / `admin123`).

**Use cases:**

- **First-time setup:** Run `make init` to build images, install Magento, and start containers. Use `make init-with-data` to also load sample data.
- **Install the Doofinder module:** after `make init`, follow the [installation guide](https://support.doofinder.com/plugins/magento/installation-guide/installation-steps-magento). Alternatively, use `make doofinder-upgrade` from the CLI.
- **Start / stop the stack:** `make start`, `make stop`.
- **Uninstall the module:** `make doofinder-uninstall`.
- **Reinstall the module:** `make doofinder-reinstall`.
- **DB snapshot:** `make db-backup` (optionally `make db-backup prefix=_name`). Restore with `make db-restore file=backup.sql.gz`.
- **Code quality check:** `make consistency` (runs PHP Code Sniffer inside Docker with the `Magento2` standard).
- **Shell in the web container:** `make dev-console`.
- **Start from scratch:** Run `make clean` to drop Docker volumes and `./app`; type `DELETE` when prompted, then run `make init` for a fresh Magento.
- **Debug with Xdebug:** The stack enables Xdebug via `XDEBUG_CONFIG` in `docker-compose.yml`. Set `XDEBUG_HOST` and `XDEBUG_KEY` in `.env` or `.env.local`, configure your IDE to listen for connections, and browse the shop.
- **Varnish:** Included but commented out in `docker-compose.yml`. Uncomment to enable; remember to comment the `9012:80` port in the `web` container. See [Configure the Commerce application to use Varnish](https://experienceleague.adobe.com/en/docs/commerce-operations/configuration-guide/cache/configure-varnish-commerce).

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
* **Want to contribute?** PRs are welcome! Before pushing, make sure PHP Code Sniffer passes — use `make consistency` or run `composer install && vendor/bin/phpcs` directly (requires PHP >= 8.3).

**If you find this plugin useful, please give us a ⭐ to support the project!**

## Try Doofinder / Learn more

Ready to improve your store search? [Get started with Doofinder for Magento 2](https://www.doofinder.com/en/solutions/magento).
