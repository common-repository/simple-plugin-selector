=== Simple Plugin Selector ===
Contributors: lorro
Tags: plugins, performance
Requires at least: 5.6
Tested up to: 6.5
Requires PHP: 7.3
Stable tag: 1.3.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Select which plugins are activated on a page-by-page basis.

== Description ==

By default, WordPress will load every plugin on every page. The Simple Plugin Selector (SPS) plugin allows the administrator to select which plugins are used on a page-by-page basis. This prevents unneeded plugins and plugin support files from loading. HTTP requests and download size will be reduced, so improving website performance for the visitor.

For example, you probably use your contact form plugin only on your Contact Us page. SPS allows you to deactivate it when the page request is for one of your other pages to avoid downloading your contact form style sheets and scripts unnecessarily.

The plugin has a user-friendly interface, so the administrator can see easily which plugins are running on each page.

Not for newbies! The administrator must have a clear grasp of which plugins are required for each page, otherwise some pages will have errors because a required plugin is not loaded.

== Installation ==

Load and activate the plugin in the normal way at Dashboard > Plugins > Add New

Once activated, the plugin will copy its files to the /wp-content/mu-plugins directory. Code in mu-plugins runs before normal plugins are loaded. That's why the plugin filter code must be run from there.

== Deactivating ==

Deactivating SPS in the normal plugins list will deactivate plugin filtering and remove SPS files from mu-plugins.

Removing the SPS plugin using FTP will leave unwanted SPS files in mu-plugins, but plugin filtering will be disabled.

SPS settings are not deleted by deactivation or deletion. Reactivating should pickup filtering where it left off.

== Settings ==

Go to Dashboard > Settings > Simple Plugin Selector to set which plugins are required on a page by page basis. If the site has cache or optimize plugins, they should be cleared after changing any SPS settings.

If a new page, post or product is created, all plugins will be activated for that by default until set otherwise in SPS settings.

If a new plugin is activated, it will be loaded for all pages by default until set otherwise in SPS settings.

== Frequently Asked Questions ==

= How much faster will my site run? =

The degree of improvement will vary between sites. Users with slow connections are likely to see a big improvement. Mobile users in particular should see quicker load times.

= A page doesn't work properly or look as it should? =

Check the plugin settings for that page. Maybe a required plugin has not been enabled. If the page still doesn't work, enable all plugins for that page.

= The theme customizer is not working properly =

The page inside the customizer is working in front-side context. If there is a plugin which is set to not load on the front-side page, then it won't be available to the admin-side customizer either. In case of issues, temporarily deactivate SPS at Dashboard > Plugins > Installed Plugins while using the customizer.

= Will it work with WooCommerce =

Yes.

= Will it work with Polylang =

Yes, from SPS v1.0.4
Not tested with the premium plugin: Polylang for WooCommerce.

= Will it work with WPML (Wordpress Multilingual Plugin) =

Not tested with the premium plugin WPML.

= Will it work on multi-site installations? =

Not tested on multi-site installations.

= It works on some pages but not others =

If you create a new page, post or product, SPS does not make assumptions about which plugins are needed for it, so SPS will load all plugins. Run through the settings screens to set plugins for any new pages, posts or products.

= It doesn't work at all =

SPS won't work if permalinks are set to "Plain"

== Screenshots ==

1. Settings - Set the number of items per settings tab
2. Settings - Set plugin type: Always / Never / Sometimes
3. Settings - Set plugin loading per page for "Sometimes" plugins
4. Settings - Set plugin loading per post for "Sometimes" plugins

== Changelog ==

= 1.3.2 =
* Tested with WP 6.5.2

= 1.3.0 =
* Now installs as a normal plugin. No FTP install needed.
* Tested with WP 6.2.2

= 1.2.0 =
* Support for Tags, Product Tags and Product Attributes withdrawn as too buggy.
* Tested with WP 5.8

= 1.1.1 =
* Fixed minor bug that occurred infrequently.

= 1.1.0 =
* Added support for post, category and tag pages.
* Added support for WooCommerce pages: products, product categories, product tags and product attributes.
* Added "Copy settings" button on page settings tabs to make settings easier.
* Added pagination of long page lists in settings.

= 1.0.4 =
* Amended to work on with Polylang sites where language codes are embedded in page urls.

= 1.0.3 =
* Amendments to meet WordPress.org guidelines.

= 1.0.2 =
* Submitted to WordPress.org for review.

== Upgrade Notice ==

= 1.3.2 =
Tested to work with WP 6.5.2