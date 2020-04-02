- XML Feed and all related components (logs, cron, scheduler, configuration on backend, frontend controllers) were deleted, except Generator\Map\Product, which is needed for indexing Doofinder fields
- Information about the Feed was deleted from the Config controller
- AtomicUpdates were deleted- they were replaced entirely by Delayed Updates
- Delayed Updates are set as default and they are disabled in the configuration. They work only during Index on Save
- All products, which are visible in the shop are indexed now. Their appearance in Search or in the Catalog depends on correct setting of their Visibility in admin panel
- Doofinder attributes are indexed at the same time as Magento attributes
- Price attributes have a new name: mage_price_CUSTOMER_GROUP_ID Depending on customer group, the price may vary. Indexer and Search take that into consideration.
- Additional Attributes validation was added. It checks if any Additional Attributes are used by Magento/Doofinder- eg. you cannot add field ‘visibility’ because it is already indexed by Magento
- HashID has to be configured for every store from now on. Changes related to Allow StoreView without HashID has been rolled back.
- MySQL was removed from the indexing/searching process
- Plugins which ensure compatibility between Magento 2.2 and 2.3 were added
- Support for Magento 2.1 was stopped, also in the file composer.json
- Change of indexer from By Schedule to On Save after first whole reindex was deleted- if we changed API, it wouldn’t be needed
- Updated Delayed Updates logic to use IndexerHandler. In addition, the "disabled" operation has been removed when turning off the product because search and indexer is based on attribute values
- Whole search engine works on our code

Refactor Doofinder Attributes

- Change serializer from phpserializer to json
- Remove configuration for default Doofinder fields, leave for couple of them
- Remove "label" field from Additional Attributes
- Add attributes provider for Image link field
