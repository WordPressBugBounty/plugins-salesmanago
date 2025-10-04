# 3.8.2 released 30.09.2025
- Fixed: CVE-2025-57971, CVE-2025-57970

# 3.8.1 released 25.08.2025
- Fixed bug with Leadoo script loading

# 3.8.0 released 07.08.2025
- Added combine Leadoo and SALESmanago features to the plugin
- Added Leadoo integration to the plugin
- Added Discover SALESmanago to Leadoo view
- Added reporting for Leadoo
- Changed discover view
- Fixed compatibility of purchase events with WordPress 6.8.2

# 3.7.1 released 24.06.2025
- Added keyboard accessibility to marketing consent checkboxes to comply with European Accessibility Act

# 3.7.0 released 22.05.2025
- Fixed bug with wrong cast to bool during enabling CRON
- Improved storeProduct() method with error handling and validation
- Fixed issue in Product Catalog tab where WooCommerce data was being loaded even if WooCommerce was not installed
- Fixed sending more than 100 variants to Product Catalog
- Updated API-SSO-UTIL library to 3.8.0
- Moved CRON settings from Integration Settings tab to Product Catalog tab
- Added possibility to choose how products will be sent to Product Catalogs (hook, cron or wp-cron)
- Fixed bug with changing email address in user profile
- Handled deleting product from Woocommerce

# 3.6.1 released 30.01.2025
- Fixed bug during creating CRON schedules
- Fixed bug during creating default Mapping for Product Catalogs

# 3.6.0 released 28.01.2025
- Added support for Details Mapping in Product Catalogs
- Added support for sending products via WordPress CRON
- Updated API-SSO-UTIL library to 3.6.0

# 3.5.0 released 02.12.2024
- Fixed method for getting main image url
- Added sending currency ISO in detail9 for external events
- Added sending product images from WC gallery to Product Catalog
- Added sending new fields in woocommerce registration form
- Changed hook for removing item from cart to 'woocommerce_cart_item_removed'

# 3.4.1 released 18.10.2024
- Fixed bug when SM_APP is not defined in monitoring code
- Fixed configuration error when logging in for the first time
- Updated API-SSO-UTIL library to 3.3.0

# 3.4.0 released 29.08.2024
- Fixed bug with mapping product identifiers in Product Catalogs
- Added automated process of API V3 key connection
- Added checking limits of product catalogs

# 3.3.1 released 11.07.2024
- Fixed bug while creating product catalogs, wrong error notification
- Secured creating catalog view when there is no API v3 key

# 3.3.0 released 04.06.2024
- Fixed sending product to Product Catalog with too many categories assigned
- Fixed bug that prevents changing order status if product no longer exists
- Added product identifier mapping for products export and upsert to Product Catalogs

# 3.2.7 released 17.05.2024
- Added to svn missing files for Marketplace

# 3.2.6 released 20.03.2024
- Fixed filtering admin notifications 
- Fixed problems with deprecated warnings in debug mode
- Upgraded api-sso to 3.1.13 and improved tracking code
- Fixed problem with empty return while setting DOI
- Changed language code for Polish translation

# 3.2.5
- Improved API v3 logging

# 3.2.4
- Improved Product Export
- Added rgif request to CF7 integration
- Improved plugin views
- Upgraded api-sso to 3.1.9

# 3.2.3
- Improved Subscription Confirmation setup in the plugin
- Added more information to notifications during product export

# 3.2.2
- Added customization of Product Catalogs for Product API

# 3.2.1
- Added birthdate in Contact transfer for Contact Form 7, Fluent Forms and Gravity Forms

# 3.2.0
- Introduced Product API

# 3.1.7
- Fixed WP error on location validation

# 3.1.6
- Improved location field validation
- Added sm_ prefix to the default value of the location field
- Added owners list updating after logging in to the plugin panel
- Fixed Contact transfer for Contact Form 7 (select type)

# 3.1.5
- Added trimming of Custom Details inputs
- Fixed error when transferring new Contacts (error on WP5.8 and lower)
- Fixed error with Location field for CANCELLATION events

# 3.1.4
- Improved 'profile_update' hook: Email address change will now update an existing Contact
- Added Contact transfer from Admin scope
- Fixed error when transferring order without customer data

# 3.1.3
- Fixed Contact and External Event counting in exports
- Added new detail to External Events containing Contact language (detail8)
- Changed default label for Mobile Marketing checkbox
- Added location field in External Events export
- Fixed backup function for assigning Contact to order
- Included additional measures to prevent multiple double opt-in emails

# 3.1.2
 - Added "About" page with support pages and debug info
 - Fixed sending contacts with ignored domains in export
 - Change capability for plugin menu pages to const SALESMANAGO. Now it's easy to customize access to the plugin
 - Added count entities to export
 - Added checkboxes to choosing with order statuses will be export
 - Added select to choose external events type for export
 - Added button to abort interrupt export
 - Added sending new orders created in admin panel

# 3.1.1
- Added hooks to modify contacts and events
- Added support for popup.js 
- Added creating sw.js file (necessary for native web push consents)
- Added refresh owners button
- Added sending event CANCELLATION after refund or cancel from admin

# 3.1.0
 - Added client reporting
 - Support for separated consents (email and mobile marketing) in gravity form, contact form 7 and fluent forms
 - Added separated consents (email and mobile marketing) in WooCommerce and WordPress
 - Added configurable custom properties types
 
# 3.0.8
 - Fixed bug with double opt in in gravity form, contact form 7 and fluent forms

# 3.0.7
 - Updated library (fix for multiple tags)
 - Support for custom monitoring cookie TTL
 - Support for separated consents (email and mobile marketing) in gravity form, contact form 7 and fluent forms
 - Fixed product ids for external events
 - Added variant id option

# 3.0.6
- Configurable product identifier type (SKU, Product ID, Variation ID) for external events added
- Configurable location field for external events added
- Account details fields added
- Monitoring code options added

# 3.0.5
 - CF7/GF/FF: It is now possible to specify confirmation emails per-form using sm-doi-template-id, sm-doi-account-id, sm-doi-subject hidden fields
 - Fluent Forms integration added
 - Fixed WooCommerce events assigning to wrong contact
 
# 3.0.4
 - Consecutive PURCHASE events can now be ignored
 - Contact Form 7 integration now supports acceptance field
 - Fixed issue with ignoredDomains warning after first login

#  3.0.3
 - Added Cart Recovery
 - Fix for GF tags & address
 - Added shipping method name

# 3.0.2
 - Fix for GF custom details

# 3.0.1
 - Settings from older plugin version are now applied to the new plugin;
 - User language is now sent to SALESmanago
 
# 3.0.0
 - Plugin has been completely rewritten from scratch;
 - New settings structure;
 - Removed unnecessary functionality;
 - Added support for a custom endpoint;
 - Added configuration schema version in Entity\Configuration;
 - Gravity Forms now supports custom properties;
 - Export will no longer fail if max_execution_time has been reached;
 - Monitoring code can now be turned off;

# 2.7.0
  - New methods for event and contact transfer
  - Performance improvements
  
# 2.6.9
  - fix for REST API registration /recover
  - updating cart now updates an external event instead of creating a new one.

# 2.6.8
  - fixed contact submission for CF7
  - fixed contact submission for GF
  - forms are now identified by id, not title

# 2.6.7
  - fix send PURCHASE event
  - add substring for details to gravity forms

# 2.6.6
  - fix export model (exclude order with product quantity lower than 0)
  - fixing ProductModel file. Add float type
  - fix response message after contacts export 
  - fix sql query in exportModel 
  
# 2.6.5
  - downgrade api-sso-util library to 2.5.2 version;

# 2.6.4
  - bug fix;
  - smoptst cookie status fixed;

# 2.6.3
  - added guest purchase tags;

# 2.6.2
  - added ignore domains;
  - updated views;
  
# 2.6.1
  - fix bug with inputs type checkbox in GF;
  - fix payment bug in WC integration;
  - update views;
 
# 2.6.0
 - removed PrePurchase method and hook connected to it;
 - added new PURCHASE event export types;

# 2.5.7
 - add consents integration to Gravity Forms;
 
# 2.5.6
 - add new API EXT_EVENT endpoint;
 
# 2.5.5
 - add events cancellation & return;

# 2.5.4
- fix CART for new API;

# 2.5.3
- new API;

# 2.5.2
- refactor GF & CF7;
- remove synchronize rule;
- fix api double optin;

# 2.5.1
- add synchronize from SALESmanago;
- fix recovery cart by url;
- core update;

# 2.5.0
- added Cf7 integration module;
- added Gravity Forms integration module;
- new modules structure;
- add double opt in;
- update advanced export;

# 2.3.2
- fix plugin admin assets

# 2.3.1
- fix login;

# 2.3.0
- added Newsletter with mapper option to get contact opt status;
- fix tagging and add newsletter tags;
- fix contact upsert;

# 2.2.1
 - fix export contacts;
 
# 2.2.0
 - update file structure;
 - update service;
 - fix count() problem on php7.2;
 - add switch options to check contacts status before add them;
 - fix exports and cart content data like details, descriptions and etc.;
 - view changes;
 
# 2.1.0
 - update file structure;
 - check if contact is optouted;
 - added basket recovery; 
 
# 2.0.9
- check opt out status;
- fix order total on purchase;

# 2.0.8
- fix with pointer problem;
- fix notices;

# 2.0.7
- fix create cookie and contact upsert;

# 2.0.6
- fix bug repeated purchase event;

# 2.0.5
- fix bug with different endpoint in login

# 2.0.4
- fix product location and details in cart & orders

# 2.0.3
- fix no account user purchase with create account
- fix optout bug when create user

# 2.0.2
- fix repeated PURCHASE after guest purchase,
- fix city and zipCode in user requests

# 2.0.1
- incorrect monitoring code fix

# 2.0.0 

Changes:
- Release of revamped plugin
- added SALESmanago SSO service
