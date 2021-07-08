LifterLMS_WooCommerce Hooks Reference
=====================================

Actions
-------

### llms_wc_updated

Runs after LifterLMS WooCommerce is updated.

**Since**   1.3.6<br>
**Version** 2.0.0<br>


### llms_wc_before_install

Runs before LifterLMS WooCommerce DB ugrades are run.

**Since**   2.0.0<br>
**Version** 2.0.0<br>


### llms_wc_after_install

Runs after LifterLMS WooCommerce DB ugrades are run.

**Since**   2.0.0<br>
**Version** 2.0.0<br>


### llms_wc_updates_queued

Runs when LifterLMS WC Updates are queued before they're saved and dispatched.

**Since**   2.0.0<br>
**Version** 2.0.0<br>


Filters
-------

### llms_wc_add_{$type}_notes

Determine whether or not notes should be recorded on a WC Order when enrollment or unenrollment occurs.
{$type} can be either "enrollment" or "unenrollment".

**Since**   1.3.0<br>
**Version** 1.3.0<br>

**Parameters**
`bool` `$bool` Whether or not to record notes. Defaults to true.


### llms_wc_account_endpoints

Modify the LifterLMS dashboard endpoints which can be added to the WC My Account page as custom tabs.

**Since**   1.3.6<br>
**Version** 1.3.6<br>

**Parameters**
`array` `$endpoints` Array of endpoint data.


### llms_wc_members_only_button_html

Modify the HTML of a WC Product "Members Only" button.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Parameters**
`string` `$html`    HTML of the button.
`int`    `$post_id` WP_Post_ID of the WC product or product variation.


### llms_wc_members_only_button_default_text

Modify the default text of a WC Product "Members Only" button.
This text only shows up if the postmeta value is empty.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Parameters**
`string` `$text`    Default text of the button.
`int`    `$post_id` WP_Post_ID of the WC product or product variation.


### llms_wc_members_only_button_text

Modify the text of a WC Product "Members Only" button.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Parameters**
`string` `$text`    Saved text of the button.
`int`    `$post_id` WP_Post_ID of the WC product or product variation.


### llms_wc_unenrollment_new_status

Customize the student unenrollment status when the student is unenrolled as a result of WC order status changes.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Example** `add_filter( 'llms_wc_plan_has_wc_product', '__return_false' );`

**Parameters**
`string` `$status`   The new status, should be a valid LifterLMS enrollment status. Defaults to 'expired'.
`int`    `$order_id` WC_Post ID of the WooCommerce Order.


### llms_wc_allowed_relationship_types

Determines what post types can be associated with a WooCommerce product.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Parameters**
`array` `$post_types` Indexed array of post types. Default is [ 'course', 'llms_membership' ].


### llms_wc_trigger_registration_actions

Determine if LifterLMS registration actions should be triggered during WooCommerce account registration.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Example** `add_filter( 'llms_wc_plan_has_wc_product', '__return_false' );`

**Parameters**
`bool`  `$bool`        Whether or not to trigger LLMS actions. Defaults to true.
`int`   `$customer_id` WP_User ID of the customer
`array` `$data`        Associative array of customer information.


### llms_wc_customer_keys_map

Maps WooCommerce customer information fileds to LifterLMS customer information fields.

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Parameters**
`array` `$array` Associative array where the key is the WC field name and the value is the LLMS field name.


### llms_wc_plan_has_wc_product

Modify the default return of the `llms_wc_plan_has_wc_product()` function
which determines if an LLMS_Access_Plan has an associated WC Product

**Since**   2.0.0<br>
**Version** 2.0.0<br>

**Example** `add_filter( 'llms_wc_plan_has_wc_product', '__return_false' );`

**Parameters**
`bool` `$bool` whether or not the plan has a WooCommerce product.
`obj`  `$plan` LLMS_Acces_Plan object.
