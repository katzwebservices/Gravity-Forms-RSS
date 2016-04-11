=== Gravity Forms RSS Add-On ===
Tags: gravity forms, gravity form, forms, gravity, form,paypal, payment, pay pal, subscription, subscribe, payments
Requires at least: 2.8
Tested up to: 4.5
Stable tag: trunk
Contributors: katzwebdesign, katzwebservices
Donate link: https://gravityview.co

Output Gravity Forms entries as an RSS feed on a per-form basis.

== Description ==

### View all your Gravity Forms entries as an RSS feed

Generate RSS feeds for each Gravity Form form.

* Go to the Forms menu
* Click on the form you want to enable RSS for
* Click on Form Settings
* At the bottom of the "Form Settings" tab, you will see a new "Form RSS" setting under "Form Options"
* Save the form
* You will see a link "Go to RSS feed" - that's the link!

__You must save the form__ before the RSS feed will work

### There's some security, too.
In order to view the RSS feed, users will need to have the correct token (a kind of password). Without the correct token, the RSS feed will not work.

== Screenshots ==
1. The field in the editor
2. The field in a form

== Frequently Asked Questions ==

= How do I modify the output formatting? =

You can use the following filters:

### Feed output

These filters pass one `$form` argument.

* `gforms_rss_feed_title` - Feed title
* `gforms_rss_feed_link` - Feed link

### Entry output

These filters pass two arguments: `$form`, `$lead`.

* `gforms_rss_entry_title` - Each entry title
* `gforms_rss_entry_link` - Each entry link
* `gforms_rss_entry_description` - Each entry description

= What's the license? =
This plugin is released under a GPL license.

== Changelog ==

= 1.1.2 & 1.1.3 =

* Works with Gravity Forms 2.0.x
* Works with WordPress 4.5
* Added translation files

= 1.1 =
* Updated to work with Gravity Forms 1.7+

= 1.0.1 =
* Fixed issue with Gravity Forms 1.6.10 activation fatal error

= 1.0 =
* Launch!

== Upgrade Notice ==

= 1.1 =
* Updated to work with Gravity Forms 1.7+

= 1.0.1 =
* Fixed issue with Gravity Forms 1.6.10 activation fatal error

= 1.0 =
* Launch!

== Installation ==

1. Upload this plugin and activate it
2. Go to Forms
3. Click on Form Settings
4. Go to the "Form Options" section of the form. You will see a
5. Save the form (only necessary if you haven't saved it since installing this plugin)
6. At the bottom of the Advanced tab, you will see a link "Go to RSS feed" - that's the link to use!