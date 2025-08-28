SALESmanago library for integrations
------------------------------------
Version 3.8.0 06.05.2025
- Added chunk method to ProductsCollection for splitting collections into smaller parts;
- Fixed potential error if API V3 service is not available;
- Fixed parsing API V3 Exception code 14 message

Version 3.7.1 06.03.2025
- Fixed empty custom details and system details serialization in ProductEntity;

Version 3.7.0 26.02.2025
- Added product/updatePrice APIv3 method;

Version 3.6.0 11.12.2024
- Added setAsNull in ProductEntity, supporting details and discountPrice;
- Fixed filtering empty data in product entity, sending empty system details still available;

Version 3.5.1 09.12.2024
- Fixed SystemDetails serialization for color;

Version 3.5.0 07.10.2024
- Added web logger helper;

Version 3.4.2 06.11.2024
- Fixed issue with ProductEntity empty system and custom details serialization;

Version 3.4.1 30.10.2024
- Fixed issue with ProductEntity serialization;

Version 3.4.0 08.09.2024
- Added mapping to ProductCatalog Service

Version 3.3.0 29.08.2024
- Added support for new API attribute validations;
- Added callback in AccountController::login() method;
- Added update product qty methods to Product Catalogs;

Version 3.2.0 22.07.2024
- Added support for Product Collections API;
- Changed the name of api v3 key;
- Added webhookUrl to ApiKeyMetaEntityInterface;
- Changed empty field of 'mainCategory' by default set to 'Uncategorized';

Version 3.1.14 03.04.2024
- Changed access to SALESmanago\Entity\Configuration::$smApp to protected;

Version 3.1.13 14.02.2024
- Added smapp number to configuration;
- Added Api V3 and Api V2 test suites;
- Removed old api DOI attributes;

Version 3.1.12 05.01.2024
- Added default main category '-' to product entity;

Version 3.1.11 24.11.2023
- Added JSON_INVALID_UTF8_IGNORE flag to json encoded request body;
- Set min. PHP version to 7.2;
- Increase connection timeout to 30 sec;
- Added regex to set main image url in product catalogs;

Version 3.1.10 03.10.2023
- Refactored reporting service;

Version 3.1.9 28.07.2023
- Fixed logout method in case of empty or revoked api v3 key;
- Added escaping double slashes in product urls;

Version 3.1.8 30.06.2023
- Added CatalogService::getLimit() method to get catalog limits;
- Added prevent send empty events to application;
- Changed do not throw ApiV3Exception for reasonCode 12;
- Added CURLOPT_FOLLOWLOCATION for cUrlClient;

Version 3.1.7 27.04.2023
- Added automatically get apiKeyV3 after vendor login;
- Added new method in ApiV3Exception - setRequiredFields();
- Added new method of setting apiDoubleOptIn through emailId and lang only;
- Increased timeouts;
- Added DOI Email ID;

Version 3.1.6 21.03.2023
- Changed export event api method;
- Remove repeating request in case of time out;

Version 3.1.5 22.02.2023
- Added SALESmanago\Exception\ApiV3Exception::getAllLogMessages

Version 3.1.4 06.02.2023
- Standardized SALESmanago\Entity\Api\V3\CatalogEntityInterface methods names;
- Fixed set null while throwing ApiV3Exception;
- Added getViewMessages() for ApiV3Exception;

Version 3.1.3 23.12.2022
- Added method to get product catalogs;
- Added Product catalogs integration;

Version 3.1.2 01.09.2022
- add coupons feature support for SALESmanago API;

Version 3.1.1 06.07.2022
- add configurable timeOuts for SALESmanago\Helper\ConnectionClients\cURLClient though Entity\Configuration;
- add configurable repeat request in case of timeout;

Version 3.1.0 10.05.2022
- upgrade required minimum PHP version to 7.0;

Version 3.0.12 30.03.2022
- Add loyaltyProgram field to request contact/UPSERT structure;
- add new reporting logic;

Version 3.0.11 16.02.2022
- Added ignoring contacts in export by email domains;
- Change reporting;

Version 3.0.10 09.02.2022
 - Added ConsentsCollection to Contact entity
 - New service LoyaltyProgram

Version 3.0.9 22.10.2021
 - Added separated flags for newsletter and mobile consent status

Version 3.0.8 16.09.2021
 - support for custom cookie TTL (smclient)

Version 3.0.7 01.09.2021
 - setters fixes
 - appendTag(s) fixed
 - endpoint can now be updated after constructing Request Service

Version 3.0.6 21.07.2021
 - fixed synchronization with enabled double opt-in;
 - replace guzzlehttp with simple cUrl client;

Version 3.0.5 07.07.2021
 - fix set tags from array parameter in Entity\Contact\Options::setTags

Version 3.0.4 17.05.2021
 - Truncated fields in request to max length supported by API
 - Moved toArray() from abstract to specific classes

Version 3.0.3 07.04.2021
 - Changed export method
 - Add reporting services

Version 3.0.2 09.03.2021
 - Fix cookie setting via TSCT.php
 - Added error code resolver

Version 3.0.1 03.03.2021
 - fix response in ContactController

Version 3.0.0 24.02.2021
 - change structure;
 - remove unnecessary functionality;
 - add new mechanism for a login;
 - add configuration schema version in Entity\Configuration;
 - change Entity\Configuration to implement ConfigurationInterface;

Version 2.6.2 20.01.2021
 - Add Event types const to Entity\Event\Event;

Version 2.6.1 04.01.2021
 - Add adapter class for GuzzleHttp client;
 - Added Properties;
 - Added IgnoreService to ignore Domains set in plugin settings;
 - Static to object due to PHP5 compatibility;
 - Upgrade guzzle adapter;
 - Refactoring adapter class;
 - Add some unit tests for guzzle adapter class;
 - fix isSubscribes, isUnsubscribes flags;
 - fix checkers;
 - Changes is ApiDoubleOptIn;
 - Moved ApiDoubleOptIn.php Entity to right place;
 - Changes is ApiDoubleOptIn;
 - Moved ApiDoubleOptIn.php Entity to right place;
 - Fix param type in Configuration.php;
 - fix event date, fix synchronization;
 - fix request service prints;
 - fix Contact ExternalID in model;
 - fix ContactModel tagScoring;
 - add an editable endpoint to user settings;
 - fix eventDate;
 - Added AppendTag(s);
 - Implemented Cookie expire time;

Version 2.6.0 23.11.2020
 - add SynchronizationService & ContactService;
 - add CHANGELOG;
