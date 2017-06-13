# Product Category Sort Order

Provides a sort order for category position.

The default Magento2 `/rest/V1/products` REST API endpoint does not allow to sort by products category position.
This module addresses this problem by plugging in seamlessly into the Magento2 API.

## Getting Started

Install the module via composer

```
$ composer require "mediaman/module-product-category-sort-order: 1.*"
```

Enable the module

```
$ ./bin/magento module:enable Mediaman_ProductCategorySortOrder
```

Upgrade your Magento database schemas

```
$ ./bin/magento setup:upgrade
```

### Usage

You can use the `/rest/V1/products` endpoint as you normally would:
 
```
$ curl -X GET http://magento.example.com/rest/V1/products?
searchCriteria[filterGroups][0][filters][extension_attributes][field]=category_id&
searchCriteria[filterGroups][0][filters][extension_attributes][value]=42&
searchCriteria[filterGroups][0][filters][extension_attributes][conditionType]=eq&
searchCriteria[sortOrders][0][field]=position&
searchCriteria[sortOrders][0][direction]=ASC 
--header "Authorization: Bearer pbhercbtk6dd3eatf1pyx8jj45avjluu"
```

## License

MIT © [mediaman GmbH](mailto:hallo@mediaman.de)
