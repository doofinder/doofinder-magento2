# Magento Architecture & Diagrams


## Relevant information

* Doofinder module is ALWAYS installed via composer. 
* Magento creates a store in Doofinder per website. It registers its website's id information inside its corresponding's store's options in order to get the correct products in the feed for the search engines corresponding to the store_views from that website.
* If the Magento app is uninstalled then the associated Doofinder store is NOT deleted, neither its search engines nor indices.

## Diagrams

### Install Doofinder plugin from Magento

```mermaid
sequenceDiagram
  Client ->> Magento: Install Doofinder app via composer
  Magento ->> Client: truthy response
  Client ->> Magento: bin/magento setup:upgrade (refreshes setup_module table)
  Magento ->> Client: truthy response
  Client ->> Magento: select platform's sector
  Magento ->> Database: save sector
  Database ->> Magento: truthy response
  Magento ->> Magento: create integration token
  Magento ->> Database: save integration token
  Database ->> Magento: truthy response
  Magento ->> Client: truthy response
  Client ->> Magento: login/sign up
  Magento ->> Doomanager: login/sign up
  Doomanager ->> Magento: ok postMessage
  Note left of Magento: postMessage: 
    %{
        admin_endpoint: "admin_endpoint",
        api_endpoint: "api_endpoint",
        api_token: "api_token",
        search_endpoint: "search_endpoint",
        token: "token"
    }
  Magento ->> Magento: create store structure
  Magento ->> DoofAPI: send store structure
  DoofAPI ->> Magento: ok response
  Note left of Magento: response: 
    %{
        installation_id: "installation_id",
        script: "script",
        search_endpoints: "search_endpoints",
    }
  Magento ->> Client: Enjoy Doofinder :)
```