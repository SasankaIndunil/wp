LifterLMS WooCommerce Changelog
===============================

v2.0.12 - 2019-11-11
--------------------

+ Tested to WordPress 5.3, LifterLMS 3.36.5, and WooCommerce 3.8.
+ Only print WC Notices if `wc_print_notices()` is defined. Fixes issue when using the LifterLMS Rest API.
+ Fix issue producing malformed enrollment trigger encountered when updating the enrollment status to "enrolled" during order updates.


v2.0.11 - 2019-05-10
--------------------

+ Only output admin course/membership relationship information for existing items. Fixes an issue caused by deleting courses/memberships associated with orders through order item meta data.


v2.0.10 - 2019-05-02
--------------------

+ Fixed an issue causing fatal errors encountered during admin panel updates of subscriptions with no parent order.
+ Resolve LifterLMS Student Dashboard lost password 404s by redirecting links to the LifterLMS lost password endpoint to the WooCommerce My Account lost password endpoint.
+ Outputs CSS and JS related to course/membership expiration in the admin subscriptions management area so it looks and functions on subscriptions like it does on orders.


v2.0.9 - 2019-03-21
-------------------

+ Disable checkout redirects as configured by LifterLMS 3.30.0 access plan checkout redirect settings for access plans connected to a WooCommerce Product.


v2.0.8 - 2019-03-06
-------------------

+ WooCommerce Order Review (on the account page) and Thank You Page tables will now output the expiration date for a courses and memberships attached to items in the order.
+ Fixes a bug preventing access expiration settings from being applied on order completion when two access plans (on different courses or memberships) are both attached to the same WC product.


v2.0.7 - 2019-03-01
-------------------

+ Fixes validation issues introduced in version 2.0.6


v2.0.6 - 2019-02-28
-------------------

+ Fix access plan validation issues caused by changes introduced in LifterLMS 3.29.0
+ Resolve a compatibility issue with the EventOn add-on for WooCommerce


v2.0.5 - 2019-02-11
-------------------

+ Product sale dates will now show on course & membership plan pricing tables.


v2.0.4 - 2019-02-04
-------------------

+ Fixed an issue preventing access plans connected via a variation from being properly related to the course during WC checkout.
+ Fixed an issue preventing the removal of a Members Only restriction on simple products/subscriptions.


v2.0.3 - 2019-01-23
-------------------

+ Updated tested-to WC version to 3.5.4
+ Fix conflict with Elementor preventing Elementor templates from being used for a WooCommerce product.


v2.0.2 - 2019-01-11
-------------------

+ Displays WC notices on LifterLMS course and membership pages. Ensures that adding products to the cart shows expected feedback on screen.


v2.0.1 - 2019-01-11
-------------------

+ In conjuction with LifterLMS 3.26.3, resolves an issue causing LifterLMS tabs with pagination on the My Account page to redirect to the Student Dashboard when paging.


v2.0.0 - 2019-01-03
-------------------

##### Access Plans Instead of Related Products

+ Instead of relating courses & membership to products you can now relate Access Plans to Products (and product variations!)
+ Course & Membership pricing tables will now look like native LifterLMS pricing tables except information will be pulled from the product
+ It is now possible to create one-time payment products which EXPIRE by utilizing access plan expiration functionality!
+ You can use FREE access plans which will move students through the native "Free Enrollment" checkout process provided by the LifterLMS Core instead of having to create a $0.00 product. It is additionally possible to link an access plan to a $0.00 product if you prefer that user experience.
+ All the above is additionally possible with subscription products (and subscription variations) when using WooCommerce Subscriptions

##### Members Only Product Restrictions

+ Members Only product functionality has been reworked and extended to product variations
+ Each product (subscription) variation can have it's own membership restriction. Simple products retain preexisting functionality.
+ Added the ability to customize the language for the "Members Only" button displayed when the visitor/user doesn't meet the product restriction requirements. Each product/variation can be customized to be unique or use the sitewide default "Members Only"

##### Updates and enhancements

+ Added course & membership relationship data to the frontend when reviewing orders on the account screen, on the checkout confirmation page, and in order emails.
+ LifterLMS student registration actions are now triggered automatically during WooCommerce checkout registration ensuring that student welcome notification emails are sent automatically and registration engagement actions are triggered.
+ WooCommerce customer billing information is automatically synced to LifterLMS student fields to ensure LifterLMS merge codes work and the information is available on LifterLMS reporting screens.
+ Added course & membership association metadata lists to help users navigate from a product to it's related course(s) and/or membership(s)
+ Added plugin integration description to the integration settings screen
+ Added plugin dependency helper message to display when WooCommerce itself is not available
+ Added helper messages to show information related to WooCommerce Subscriptions features when the extension isn't available
+ Added a documentation link on the settings screen to help new users learn how to create orders for courses and membership automatically complete
+ Added an information message when viewing LifterLMS Checkout & Gateway settings on the admin panel to help orient users to the related WooCommerce settings panels which should be used when using this integration.
+ Slightly improved course & membership page load performance by removing stylesheets used to build WooCommerce-powered pricing tables
+ Moved methods related to WC order item meta to their own class for improved code clarity

##### Bug fixes

+ Fixed an issue with logging functions.
+ Fixed an issue preventing enrollments from taking place when manually creating orders on the admin panel.
+ Fixed an issue with 1.3.6 migration scripts causing empty options to throw a PHP notice.
+ Fixed an issue causing incomplete orders to display enrollment status as "Cancelled". This status was displaying due to enrollment not yet taking place. The correct status is now "None" when an order has not yet completed to trigger related enrollments.
+ Fixes an issue causing account endpoint defaults to not display when first enabling the integration

##### Removed Functions & Methods

+ `llms_wc_output_pricing_table()` has no replacement
+ `llms_product_get_wc_product_ids()` has no replacement
+ `LLMS_Integration_WooCommerce->order_status_actions()` replaced with `LLMS_WC_Order_Actions->add_status_actions()`
+ `LLMS_Integration_WooCommerce->do_order_enrollments()` replaced with `LLMS_WC_Order_Actions->do_order_enrollments()`
+ `LLMS_Integration_WooCommerce->do_order_unenrollments()` replaced with `LLMS_WC_Order_Actions->do_order_unenrollments()`
+ `LLMS_Integration_WooCommerce->before_wc_product()` replaced with `LLMS_WC_Availability_Buttons->before_product()`
+ `LLMS_Integration_WooCommerce->save_wc_product_fields()` replaced with `LLMS_WC_Product_Meta->save()`
+ `LLMS_Integration_WooCommerce->add_wc_product_fields()` replaced with `LLMS_WC_Product_Meta->add_advanced_fields()`
+ `LLMS_Integration_WooCommerce->output_item_meta()` replaced with `LLMS_WC_Order_Item_Meta->output()`
+ `LLMS_Integration_WooCommerce->save_order_enrollments()` replaced with `LLMS_WC_Order_Item_Meta->save()`
+ `LLMS_Integration_WooCommerce->output_endpoint_courses()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_courses()`
+ `LLMS_Integration_WooCommerce->output_endpoint_memberships()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_memberships()`
+ `LLMS_Integration_WooCommerce->output_endpoint_certificates()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_certificates()`
+ `LLMS_Integration_WooCommerce->output_endpoint_vouchers()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_redeem_vouchers()`


v2.0.0-beta.5 - 2018-12-26
--------------------------

+ Adds migration scripts to migrate to the 2.0.0 data structure.


v2.0.0-beta.4 - 2018-12-21
--------------------------

+ WooCommerce customer billing information is automatically synced to LifterLMS student fields to ensure LifterLMS merge codes work and the information is available on LifterLMS reporting screens.
+ LifterLMS student registration actions are now triggered automatically during WooCommerce checkout registration ensuring that student welcome notification emails are sent automatically and registration engagement actions are triggered.
+ Added course & membership relationship data to the frontend when reviewing orders on the account screen, on the checkout confirmation page, and in order emails.
+ Fixed an issue with migration scripts causing empty options to throw a PHP notice.
+ Fixed an issue preventing enrollments from taking place when manually creating orders on the admin panel.


v2.0.0-beta.3 - 2018-12-14
--------------------------

+ Fixed bug causing access plans connected to paid products to not display. Must upgrade to LifterLMS Core 3.25.2 to see this fully resolved.
+ Fixed bug causing variation member's only button text from appearing to be incorrectly saved on the admin panel.


v2.0.0-beta.2 - 2018-11-07
--------------------------

+ Fix issue with logging functions


v2.0.0-beta.1 - 2018-08-30
--------------------------

##### Access Plans Instead of Related Products

+ Instead of relating courses & membership to products you can now relate Access Plans to Products (and product variations!)
+ Course & Membership pricing tables will now look like native LifterLMS pricing tables except information will be pulled from the product
+ It is now possible to create one-time payment products which EXPIRE by utilizing access plan expiration functionality!
+ You can use FREE access plans which will move students through the native "Free Enrollment" checkout process provided by the LifterLMS Core instead of having to create a $0.00 product. It is additionally possible to link an access plan to a $0.00 product if you prefer that user experience.
+ All the above is additionally possible with subscription products (and subscription variations) when using WooCommerce Subscriptions

##### Members Only Product Restrictions

+ Members Only product functionality has been reworked and extended to product variations
+ Each product (subscription) variation can have it's own membership restriction. Simple products retain preexisting functionality.
+ Added the ability to customize the language for the "Members Only" button displayed when the visitor/user doesn't meet the product restriction requirements. Each product/variation can be customized to be unique or use the sitewide default "Members Only"

##### Updates and enhancements

+ Added course & membership association metadata lists to help users navigate from a product to it's related course(s) and/or membership(s)
+ Added plugin integration description to the integration settings screen
+ Added plugin dependency helper message to display when WooCommerce itself is not available
+ Added helper messages to show information related to WooCommerce Subscriptions features when the extension isn't available
+ Added a documentation link on the settings screen to help new users learn how to create orders for courses and membership automatically complete
+ Added an information message when viewing LifterLMS Checkout & Gateway settings on the admin panel to help orient users to the related WooCommerce settings panels which should be used when using this integration.
+ Slightly improved course & membership page load performance by removing stylesheets used to build WooCommerce-powered pricing tables
+ Moved methods related to WC order item meta to their own class for improved code clarity

##### Bug fixes

+ Fixed an issue causing incomplete orders to display enrollment status as "Cancelled". This status was displaying due to enrollment not yet taking place. The correct status is now "None" when an order has not yet completed to trigger related enrollments.
+ Fixes an issue causing account endpoint defaults to not display when first enabling the integration

##### Removed Functions & Methods

+ `llms_wc_output_pricing_table()` has no replacement
+ `llms_product_get_wc_product_ids()` has no replacement
+ `LLMS_Integration_WooCommerce->order_status_actions()` replaced with `LLMS_WC_Order_Actions->add_status_actions()`
+ `LLMS_Integration_WooCommerce->do_order_enrollments()` replaced with `LLMS_WC_Order_Actions->do_order_enrollments()`
+ `LLMS_Integration_WooCommerce->do_order_unenrollments()` replaced with `LLMS_WC_Order_Actions->do_order_unenrollments()`
+ `LLMS_Integration_WooCommerce->before_wc_product()` replaced with `LLMS_WC_Availability_Buttons->before_product()`
+ `LLMS_Integration_WooCommerce->save_wc_product_fields()` replaced with `LLMS_WC_Product_Meta->save()`
+ `LLMS_Integration_WooCommerce->add_wc_product_fields()` replaced with `LLMS_WC_Product_Meta->add_advanced_fields()`
+ `LLMS_Integration_WooCommerce->output_item_meta()` replaced with `LLMS_WC_Order_Item_Meta->output()`
+ `LLMS_Integration_WooCommerce->save_order_enrollments()` replaced with `LLMS_WC_Order_Item_Meta->save()`
+ `LLMS_Integration_WooCommerce->output_endpoint_courses()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_courses()`
+ `LLMS_Integration_WooCommerce->output_endpoint_memberships()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_memberships()`
+ `LLMS_Integration_WooCommerce->output_endpoint_certificates()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_view_certificates()`
+ `LLMS_Integration_WooCommerce->output_endpoint_vouchers()` replaced with `LLMS_Integration_WooCommerce->output_endpoint_redeem_vouchers()`


v1.3.6 - 2018-06-15
-------------------

+ Account endpoint labels will now obey translations from the LifterLMS core
+ Account slugs will now obey settings defined in LifterLMS core settings


v1.3.5 - 2018-05-16
-------------------

+ Tested up to WooCommere 3.4.0
+ Using `[products]` shortcode on a course or membership will now display the product's image as expected
+ Updated assets path for stylesheets
+ Added RTL language support


v1.3.4 - 2018-03-19
-------------------

+ Fix courses pagination on courses "My Account" endpoint


v1.3.3 - 2018-02-28
-------------------

+ When adding custom endpoints to WC Account, only move the logout link if it actually exists. Fixes theme/plugin compatibility issues with other 3rd parties who move or remove the logout item.
+ Fix an issue causing a PHP warning when all LifterLMS endpoints are removed from the WooCommerce account dashboard.


v1.3.2 - 2017-10-21
-------------------

+ Fix order line item type check causing issues with tax & shipping line items


v1.3.1 - 2017-10-11
-------------------

+ Add LifterLMS 3.14.0 compatibility for LifterLMS WC account endpoints


v1.3.0 - 2017-09-18
-------------------

+ Added ability to choose which LifterLMS Student Dashboad Endpoints are added to the WooCommerce Account Page
+ Ensure that order meta unenrollment works for variable products and variable subscriptions
+ Moved the class method outputting the pricing table on Courses and Memberships to a function which can be removed using `remove_action()`
+ Improved data logged when debug logging is enabled
+ Updated `WC_Order_Item` instances to utilize WooCommerce 3.0 class methods
+ Add filter to hide the "no payment gateways found" notice output by the LifterLMS Core when this integration is enabled, relies on a forthcoming LifterLMS Core update.


v1.2.0 - 2017-09-07
-------------------

+ Add the ability to unenroll students from courses and memberships when initial enrollment occurred as a result of WooCommerce order or subscription purchase


v1.1.3 - 2017-07-25
-------------------

+ Fix error causing members only restrictions to be incorrectly applied to other products on the WC Shop page.


v1.1.2 - 2017-06-30
-------------------

+ Fix PHP notices generated by direct references to WC_Order properties
+ Fixed incorrect text-domain on a few translation functions on the integration settings screen.


v1.1.1 - 2017-03-31
-------------------

+ Fix match height scripts to only load on course and membership pages
+ fix serialized array product query to prevent missed enrollments


v1.1.0 - 2017-03-29
-------------------

+ Allow up to 6 products (rather than one) to be associated with each course or membership
+ Adjusted Products attached to a course and membership to render in a manner visually similar to the native LifterLMS access plans pricing table
+ Now completely translateable! For information on translating LifterLMS WooCommerce please see our [translation documentation](https://lifterlms.com/docs/translating-lifterlms-woocommerce/)


v1.0.2 - 2017-01-11
-------------------

+ Ensure that course enrollment period restrictions are enforced


v1.0.1 - 2017-01-04
-------------------

+ Better handling for WC Subscriptions during de-enrollment events


v1.0.0 - 2016-10-04
-------------------

**Official Public Release**

+ Combine your LMS with the power of the popular WordPress ecommerce platform you know and love
+ Sell LifterLMS Courses and Memberships via WooCommerce
+ Match courses to products via a simple dropdown interace. No more multi-step SKU matching
+ Mark products as "Members Only" to prevent purchase by non-members
+ Automatically enroll customers in and un-enroll customers from the matching courses and memberships when the order reaches the status(es) of your choice
+ Integration with WooCommerce Subsrciptions for your recurring payment needs
+ Automatic enrollment and un-enrollment based on the subscription statuses of your choice
+ LifterLMS course, membership, certificate, and acheivements data automatically added as tabs to the WooCommerce My Account page


v1.0.0-beta.1 - 2016-09-11
--------------------------

+ Initial beta release
