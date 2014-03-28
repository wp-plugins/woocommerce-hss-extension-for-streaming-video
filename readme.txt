=== WooCommerce HSS Extension for Streaming Video ===
Author URI: http://www.hoststreamsell.com
Plugin URI: http://wordpres2.hoststreamsell.com
Contributors: hoststreamsell
Tags: sell,video,streaming,cart
Requires at least: 3.3
Tested up to: 3.4
Stable tag: 0.92
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Sell access to streaming videos through WordPress by integrating the HostStreamSell video platform with the great WooCommerce plugin

== Description ==

Sell access to streaming videos with total control over how long you want to
give access for, whether you want to allow downloads or be stream only, and
whether you want to limit the amount of usage in terms of a bandwidth usage cap.


Features of the plugin include:

* Extend the flexibility of the WooCommerce plugin to sell access to videos hosted on the HostStreamSell platform
* Currently only works with one selling price per video
* Currently does not support selling of groups of videos together

More information at [HostStreamSell.com](http://hoststreamsell.com/).

Demo at [woo_demo.hoststreamsell.com](http://woo_demo.hoststreamsell.com/).

== Installation ==

1. Activate the plugin
2. Go to Settings > HSS Admin and enter API key from your HostStreamSell
account
3. Click the Update key to Pull video information from HostStreamSell platform
and insert into the system automatically (also to update)
4. Go to WooCommerce > Settings and make sure the check box for 'Enablee guest checkout' is not checked. If it is, uncheck and press Save Changes


== Frequently Asked Questions ==

= Does this work with other video platforms =

No this only works with the HostStreamSell video platform

= How do I style the text which appears above the video player to say whether it is the trailer or full video?

Add the following to your theme's style.css to for example make the text centered

.hss_watching_video_text { text-align:center; }
.hss_watching_trailer_text { text-align:center; }

You can set what the text says (or whether to show any text at all through the plugin's settings)


== Screenshots ==

1. Download products overview
2. Download configuration
3. Download configuration details
4. Download configuration with variable prices
5. Payment history
6. Discount codes
7. Earnings and sales reports
8. Add to cart / purchase button
9. Checkout screen


== Changelog ==

= 0.1 =

* Initial version uploaded to WordPress. Currently only supports one price per video

= 0.2 =

* Removed some uneeded debug logging

= 0.3 =

* Add support for downloading files

= 0.4 =

* Added check if guest checkout is enabled with wanring that purchase will not work and for admin to disable this or user to make sure they register

= 0.5 =

*Betterlogic whether to show donload links depending on whether the current
user has download access

= 0.6 =

*Only add Video tab to streaming video products

= 0.7 =

*Fix jwplayer cross protocol issue between http/https

= 0.8 =

*Improve functionality around adding video access when order is in processing state. Add default log file

= 0.81 =

*Updated the readme

= 0.9 =

* added support to set a Website Reference ID. The default for this will be 0 (zero), and should only be changed in the event that you want multiple WordPress websites selling the same videos. You set a different ID for each website, which is used to distinguish for example a customer with WordPress user ID of 5 on one website, with a totally different user on another website with the same user ID of 5

= 0.91 =

*fix check-in issue

= 0.92 =

*Added ability to configure text above video when showing trailer or full video through a plugin setting
