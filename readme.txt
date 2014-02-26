=== Plugin Name ===
Contributors: jerelabs
Donate link: http://www.jerelabs.com/wordpress/2014/02/donate/
Tags: ccb
Requires at least: 3.0.1
Tested up to: 3.8.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin utilizes the API to display group or event data within wordpress.  This is a very early development version so features are limited.

== Description ==
(Just so it's clear -- this plugin is *NOT* an official plugin from Church Community Builder)

Church Community Builder (CCB) includes a few options for displaying content from your CCB database on a web page.  Unfortunately this consists of an iframe  which gives you no control over what is displayed or how it’s displayed.  While this is great for the novice, it’s constraining for those of us who want a seamless look (and jquery doesn’t work since it’s in an iframe from another domain which you can’t access).

CCB does however have a fairly simple web service API that allows you to query for some of the basic information you may need. 

This plugin utilizes the API to display group or event data within wordpress.  This is a very early development version so features are limited.

I’d welcome your feedback so I can make this more useful to churches across the country.  It uses XSLT so you can make the output look like almost anything you want.

== Installation ==

1. Place the folder "jerelabs-ccb" and it's contents into the /wp-content/plugins folder in your wordpress installation
2. Activate the plugin
3. Obtain a CCB API login.  You add an API user in CCB from the Settings | API menu.
4. Back in wordpress, go to Settings | Jerelabs CCB
5. Fill in the information in the CCB Integration Settings section using the information from the CCP API screen.
6. For now you can skip the rest of the options.  

== Usage ==

If you use the built in templates, usage is as follows
Groups:  [ccbgroups group_type='Growth Groups']
  group_type: Display only groups of the type specified (optional)

Calendar of Events:  [ccbevents num_days=1]
  num_days: The number of days of events to display (optional, defaults to 7)

If you run into problems contact me and I'll do my best to help you.

Caching

After you get everything working, I highly recommend enabling caching.  It will save the last API call for 4 hours, instead of calling CCB for every page request.  Leaving the cache off in a production environment is a sure way to cause CCB to disable your API account.


== Frequently Asked Questions ==

= How can I customize the display of the data? =

Output is controlled by the applicable XSLT file within the plugin directory.  Feel free to modify it as you see fit.

== Screenshots ==

1. Group grid display format

== Changelog ==

= 0.5 Alpha =
* Initial publication of the plugin to the wordpress plugin repository.
* Redesigned backend which should address activation problems
* Introduces new grid mode

