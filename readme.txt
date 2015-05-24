=== WooCommerce HSS Extension for Streaming Video ===
Author URI: https://www.hoststreamsell.com
Plugin URI: http://woo_demo.hoststreamsell.com
Contributors: hoststreamsell
Tags: sell,video,streaming,cart
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 1.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest and most advanced solution to selling videos with WordPress and WooCommerce

== Description ==

Get up and running in 3 easy steps!

* Sign up for a free trial account on
[HostStreamSell.com](https://www.hoststreamsell.com/?utm_source=wordpress&utm_medium=link&utm_campaign=woo_plugin)
* Upload, encode, and organize your videos
* Install WooCommerce and our WooCommerce integration plugin on your website and create all video products on your website with one click

Everything you could need!

* Rent or sell
* Stream only or streaming and download.
* Sell individual videos as well as groups of videos
* Provide multiple purchase options for the one video or video group

Demo at [woo_demo.hoststreamsell.com](http://woo_demo.hoststreamsell.com/).

== Installation ==

1. Sign up for a free trial account on
[HostStreamSell.com](https://www.hoststreamsell.com/?utm_source=wordpress&utm_medium=link&utm_campaign=woo_plugin)
2. Upload, encode, and organize your videos
3. Install WooCommerce and this plugin
 - Go to Settings > HSS WOO Admin and enter API key from your HostStreamSell account and press Save
 - Click the Update key to Pull video information from HostStreamSell platform and insert video all products into the system automatically
 - Go to WooCommerce > Settings and make sure the check box for 'Enablee guest checkout' is not checked. If it is, uncheck and press Save Changes


== Frequently Asked Questions ==

= Does this work with other video platforms =

No this only works with the HostStreamSell video platform

= How do I style the text which appears above the video player to say whether it is the trailer or full video?

Add the following to your theme's style.css to for example make the text centered

.hss_woo_watching_video_text { text-align:center; }
.hss_woo_watching_trailer_text { text-align:center; }

You can set what the text says (or whether to show any text at all through the plugin's settings)


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

= 0.93 =

*Added some beta subtitle support functionality

= 0.94 =

*Added subtitle updates and css styling for the trailer/full video text above the video

= 1.00 =

*Updates to support responsive JW Player, and add ability to change the subtitle font size

= 1.01 =

*Minor update to admin screen checked function

= 1.02 =

*Made improvements to enable videos still be created if upload directory is not present or not writeable

= 1.03 =

*Remove spaces from API key and database ID settings when updated

= 1.04 =

*Added action for outputing content under the video if user has purchased

= 1.05 =

*Fixed PHP open tag issue

= 1.06 =

*Added functionality to create a URL on purchase which will allow access the purchased videos directly without having to log in

= 1.1 =

*Added support for variable pricing options

= 1.11 =

*Added support for selling access to a group of videos

= 1.12 =

* Fixed a bug where video groups with no thumbnail were causing an issue

= 1.13 =

* Fixed bug where video group accessnot being granted unless there were multiple purchase options

= 1.14 =

* Made group product be virtual type and also now allow letting title not be updated just like description

= 1.15 =

* Add localization support for access options
