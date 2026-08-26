# Manago AI PHP SDK

> A lightweight integration toolkit for connecting PHP applications with Manago AI.

`managoai/php-sdk` provides reusable PHP components for authentication, contact sync, event tracking, batch export, and API v3 product catalog operations.

For backward compatibility, the code-level namespace remains `SALESmanago\...`.

## Install

```bash
composer require managoai/php-sdk
```

## Highlights

- Authenticate an integration against Manago AI
- Sync contacts and behavioral events
- Export contacts and events in batches
- Manage product catalogs and product data through API v3
- Plug into your own cookie, session, and configuration storage layers

## Architecture At A Glance

| Layer | Responsibility |
|---|---|
| Entities | Configuration and request payload objects |
| Controllers | High-level integration flows |
| Services | Lower-level API communication |
| Collections | Batch processing for contacts, events, and products |
| Adapters | Optional bridge to your application storage |

## Typical Flow

1. Build a configuration object
2. Authenticate or provide existing API credentials
3. Use a controller or service for the chosen scenario
4. Read the returned `Response` object or API result array

## Quick Start

### Login and initialize configuration

Use this flow when your integration needs to authenticate against the Manago AI application and automatically obtain an API v3 key.

```php
<?php

use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Entity\User;

$configuration = UnionConfigurationEntity::getInstance();

$configuration
    ->setEndpoint('https://app2.salesmanago.pl')
    ->setPlatformName('MyPlatform')
    ->setPlatformVersion('1.0.0')
    ->setVersionOfIntegration('1.0.0')
    ->setPlatformDomain('example.com')
    ->setPlatformLang('en')
    ->setPlatformCountry('US');

$user = (new User())
    ->setEmail('owner@example.com')
    ->setPass('secret');

$accountController = new AccountController($configuration, null);
$response = $accountController->login($user);

$apiV3Key = $response->getField('conf')->getApiV3Key();
```

### Transfer a contact

Use `ContactAndEventTransferController` for standard contact synchronization.

```php
<?php

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Entity\Configuration;
use SALESmanago\Entity\Contact\Contact;

$configuration = Configuration::getInstance();

$configuration
    ->setEndpoint('https://app2.salesmanago.pl')
    ->setClientId('client-id')
    ->setApiKey('api-key')
    ->setApiSecret('api-secret')
    ->setOwner('owner@example.com')
    ->setSha('sha1-hash');

$contact = new Contact();

$contact
    ->setEmail('john@example.com')
    ->setName('John Doe')
    ->setPhone('+1 555 100 200')
    ->setExternalId('customer-123')
    ->setCompany('Example Inc.')
    ->setAddress(
        $contact->getAddress()
            ->setCity('New York')
            ->setCountry('US')
            ->setStreetAddress('5th Avenue 1')
            ->setZipCode('10001')
    )
    ->setOptions(
        $contact->getOptions()
            ->setIsSubscribes(true)
            ->setTags('NEWSLETTER')
            ->setCreatedOn(time())
    );

$controller = new ContactAndEventTransferController($configuration);
$response = $controller->transferContact($contact);

$contactId = $response->getField('contactId');
```

### Transfer a contact and event together

This is a good fit for checkout, purchase, and cart recovery flows.

```php
<?php

use SALESmanago\Controller\ContactAndEventTransferController;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Entity\Event\Event;

$controller = new ContactAndEventTransferController($configuration);

$contact = (new Contact())
    ->setEmail('john@example.com');

$event = (new Event())
    ->setEmail('john@example.com')
    ->setContactExtEventType(Event::EVENT_TYPE_PURCHASE)
    ->setProducts(['SKU-1', 'SKU-2'])
    ->setDescription('Order #1001')
    ->setDate(time())
    ->setExternalId('order-1001')
    ->setLocation('store-1')
    ->setValue(199.99);

$response = $controller->transferBoth($contact, $event);
```

## Double Opt-In

The SDK supports API-based double opt-in when sending contacts.

You can configure it using `ApiDoubleOptIn` and attach it to the shared configuration before calling contact transfer methods.

```php
<?php

use SALESmanago\Entity\ApiDoubleOptIn;
use SALESmanago\Entity\Configuration;

$doubleOptIn = (new ApiDoubleOptIn())
    ->setEnabled(true)
    ->setEmailId('newsletter-confirmation')
    ->setLang('en');

Configuration::getInstance()->setApiDoubleOptIn($doubleOptIn);
```

Legacy fields such as `templateId` and `accountId` are still available for backward compatibility, but `emailId` and `lang` are the preferred approach.

## Batch Export

Use `ExportController` when you want to export larger contact or event collections.

```php
<?php

use SALESmanago\Controller\ExportController;
use SALESmanago\Entity\Contact\Contact;
use SALESmanago\Model\Collections\ContactsCollection;

$contacts = new ContactsCollection();

$contacts->addItem(
    (new Contact())
        ->setEmail('first@example.com')
        ->setName('First User')
);

$contacts->addItem(
    (new Contact())
        ->setEmail('second@example.com')
        ->setName('Second User')
);

$exportController = new ExportController($configuration);
$response = $exportController->export($contacts);
```

## API v3 Product Catalogs

Use the API v3 product services when syncing catalog and product data with Manago AI commerce and recommendation features.

### Create or use a catalog

```php
<?php

use SALESmanago\Entity\Api\V3\CatalogEntity;
use SALESmanago\Entity\UnionConfigurationEntity;
use SALESmanago\Services\Api\V3\Product\CatalogService;

$configuration = UnionConfigurationEntity::getInstance();

$configuration
    ->setApiV3Endpoint('https://api.salesmanago.com')
    ->setApiV3Key('api-v3-key');

$catalogService = new CatalogService($configuration);

$catalog = (new CatalogEntity())
    ->setName('Main catalog')
    ->setCurrency('USD')
    ->setLocation('store-1');

$result = $catalogService->createCatalog($catalog);
```

### Upsert products

```php
<?php

use SALESmanago\Entity\Api\V3\Product\CustomDetailsEntity;
use SALESmanago\Entity\Api\V3\Product\ProductEntity;
use SALESmanago\Entity\Api\V3\Product\SystemDetailsEntity;
use SALESmanago\Model\Collections\Api\V3\ProductsCollection;
use SALESmanago\Services\Api\V3\Product\ProductService;

$systemDetails = (new SystemDetailsEntity())
    ->setBrand('BrandX')
    ->setManufacturer('BrandX')
    ->setColor('black')
    ->setPopularity(80)
    ->setBestseller(true);

$customDetails = (new CustomDetailsEntity())
    ->set('cotton', 1)
    ->set('regular-fit', 2);

$product = (new ProductEntity())
    ->setProductId('SKU-100')
    ->setName('Classic T-Shirt')
    ->setMainCategory('Apparel')
    ->setCategories(['Apparel', 'T-Shirts'])
    ->setDescription('Basic product description')
    ->setProductUrl('https://example.com/products/sku-100')
    ->setMainImageUrl('https://example.com/images/sku-100.jpg')
    ->setAvailable(true)
    ->setActive(true)
    ->setQuantity(25)
    ->setPrice(29.99)
    ->setSystemDetails($systemDetails)
    ->setCustomDetails($customDetails);

$products = (new ProductsCollection())
    ->addItem($product);

$productService = new ProductService($configuration);
$result = $productService->upsertProducts($catalog, $products);
```

## Adapter Integration

The SDK can cooperate with your application through adapters instead of managing persistence on its own.

### Configuration storage adapter

Use `ConfigurationStoreAdapterInterface` if you want login and logout flows to persist or remove configuration in your own storage.

```php
<?php

use SALESmanago\Adapter\ConfigurationStoreAdapterInterface;
use SALESmanago\Controller\User\Vendor\AccountController;
use SALESmanago\Entity\ConfigurationInterface;
use SALESmanago\Entity\UnionConfigurationInterface;
use SALESmanago\Entity\Api\V3\ConfigurationInterface as V3ConfigurationInterface;

class InMemoryConfigurationStore implements ConfigurationStoreAdapterInterface
{
    public function storeUnionConfiguration(UnionConfigurationInterface $configuration): bool
    {
        return true;
    }

    public function storeConfiguration(ConfigurationInterface $configuration): bool
    {
        return true;
    }

    public function storeV3Configuration(V3ConfigurationInterface $configuration): bool
    {
        return true;
    }

    public function removeConfiguration()
    {
    }

    public function removeAuthConfiguration()
    {
    }
}

$store = new InMemoryConfigurationStore();
$accountController = new AccountController($configuration, $store);
```

### Cookie and session adapters

Use cookie and session adapters when you want the SDK to store temporary identifiers such as `smclient` and `smevent` in your application layer.

```php
<?php

use SALESmanago\Adapter\CookieManagerAdapter;
use SALESmanago\Adapter\SessionManagerAdapter;
use SALESmanago\Controller\ContactAndEventTransferController;

class NativeCookieManager implements CookieManagerAdapter
{
    public function setCookie($name, $value, $expiry, $httpOnly = false, $path = '/')
    {
        return setcookie($name, $value, $expiry, $path, '', false, $httpOnly);
    }

    public function deleteCookie($name)
    {
        return setcookie($name, '', time() - 3600, '/');
    }

    public function getCookie($name)
    {
        return $_COOKIE[$name] ?? null;
    }
}

class NativeSessionManager implements SessionManagerAdapter
{
    public function setToSession($varName, $sessionId = null)
    {
        $_SESSION[$varName] = $sessionId;
        return true;
    }

    public function getFromSession($varName)
    {
        return $_SESSION[$varName] ?? null;
    }

    public function deleteFromSession($varName)
    {
        unset($_SESSION[$varName]);
        return true;
    }
}

$controller = new ContactAndEventTransferController($configuration);
$controller->setCookieManager(new NativeCookieManager());
$controller->setSessionManager(new NativeSessionManager());
```

## Compatibility Notes

- Composer package: `managoai/php-sdk`
- PHP namespaces: `SALESmanago\...`
- Older classes still exist in some areas for backward compatibility
- Prefer the newer API v3 product services under `SALESmanago\Services\Api\V3\Product\...`

## Migration From The Previous Package Name

- Old package: `salesmanago/api-sso-util`
- New package: `managoai/php-sdk`
- Existing PHP namespaces stay unchanged
