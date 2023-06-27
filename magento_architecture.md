# Magento Architecture & Diagrams


## Relevant information

* Doofinder module is ALWAYS installed via composer. 
* Magento creates a store in Doofinder per store group. It registers its website's id information inside its corresponding's store's options in order to get the correct products in the feed for the search engines corresponding to the store_views from that website.
* It also stores its store view's id into the datasource options with the same goal as above, but more accurately.
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
  Magento ->> Magento_database: save sector
  Magento_database ->> Magento: truthy response
  Magento ->> Magento: create integration token
  Magento ->> Magento_database: save integration token
  Magento_database ->> Magento: truthy response
  Magento ->> Client: truthy response
  Client ->> Magento: login/sign up
  Magento ->> Doomanager: login/sign up
  Doomanager ->> Magento: ok postMessage
  Note left of Magento: {<br/>"admin_endpoint": admin_endpoint,<br/>"api_endpoint": api_endpoint,<br/>"api_token": "api_token",<br/>"search_endpoint": search_endpoint,<br/>"token": token<br/>}
  Magento ->> Magento: create store structure
  Magento ->> Doomanager: send store structure
  Note over Magento,Doomanager: includes callback_url to be called after it
  Doomanager ->> Magento: ok response
  Doomanager --) Magento: calls callback_url
  Magento --) Magento: enables doofinder layer
  Note left of Magento: {<br/>"installation_id": installation_id,<br/>"script": script,<br/>"search_endpoints": "search_endpoints",<br/>}
  Magento ->> Client: Enjoy Doofinder :)
```

### Index Magento feed 

```mermaid
sequenceDiagram
  Doomanager ->> Dftasks: send payload to dftasks
  loop Pagination
    Dftasks ->> Magento: ask for product's feed to our custom endpoint (paginated)
    Magento ->> Magento: ask for product's feed to Magento's default API and wraps it's response appending some necessary information for us
    Magento ->> Dftasks: Products' feed
  end
  Dftasks ->> DoofAPI: Index products' feed
  DoofAPI ->> Dftasks: ok
  Dftasks ->> Doomanager: ok
  Note left of Dftasks: If the multiindex is activated, it goes the same for pages and categories

```

### Update on save process
```mermaid
sequenceDiagram
  Magento ->> Magento: Activate update on save every: Any value except for "everyday"
  Magento ->> Magento: Change, create or delete a Page, Category or Product
  Magento ->> Magento: Normalize entity into doofinder_feed_changed_item db's format
  Magento ->> Magento's DB: Registers into doofinder_feed_changed_item the normalized entity
  Magento's DB ->> Magento: ok
  loop Stores
    loop indices
      Magento ->> Magento: get items to create/update in Doomanager by indice type
      Magento ->> Doomanager: ask if the indice exists
      alt it does exist
        Doomanager ->> Magento: true
        Magento ->> Doomanager: send id list and indice type
        Doomanager ->> Magento: ask for the information of the ids received
        Magento ->> Doomanager: information of items
        Doomanager ->> Items transformation: normalize the information
        Items transformation ->> Doomanager: items normalized for doofindex
        Doomanager ->> Doofindex: create/update the items
        Doofindex ->> Doomanager: ok
        Doomanager ->> Magento: ok
      end
      Magento ->> Magento: get items to delete in Doomanager by indice type
      Magento ->> Doomanager: ask if the indice exists
      alt it does exist
        Doomanager ->> Magento: true
        Magento ->> Doomanager: send id list and indice type
        Doomanager ->> Doofindex: delete the items
        Doofindex ->> Doomanager: ok
        Doomanager ->> Magento: ok
      end
    end
  end
```