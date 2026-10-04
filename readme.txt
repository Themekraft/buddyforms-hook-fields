=== BuddyForms Hook Fields ===
Contributors: svenl77, konradS, themekraft, buddyforms, gfirem, marin250189
Tags: form submission data, display form data, dynamic templates, content templates, form content templates
Requires at least: 5.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.17
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display data submitted with BuddyForms on the front end, through hooks or dynamic content templates for the single view of any post type.

== Description ==

BuddyForms Hook Fields displays the data submitted with a BuddyForms form on the front end. Show each field where you need it through hooks, or create dynamic content templates that define how every submission of a form looks in its single view. It works with ACF, Pods, BuddyPress and BuddyBoss fields that are part of a BuddyForms form.

### Display any field type
The plugin handles the different field types (links, categories, text fields and more) and displays each one the way it should look. You can show only the value, or the value with the field name.

### Create dynamic templates
Create dynamic templates that display field values. Use them in the Form Builder to override the single view of posts, pages or any other post type.

[youtube https://youtu.be/sCGIIfmF9hY]

### Block Editor and Full Site Editing
Display any form value in the Block Editor or in block themes.

### Dynamic content templates with blocks
Use form field data as dynamic data in your block templates.

[youtube https://youtu.be/swDcSpn-psg]

### Use it with page builders like Elementor or Divi
Use any template or field value in your preferred page builder to create dynamic single pages or a complete dynamic layout.

### Create a list and reorder items with drag and drop
To display several fields as a list in one place, reorder them with drag and drop in the Form Builder. The fields are displayed in the same order as in the form.

### Hook into the content
For the single view there are four default positions:
1. Before the title
2. After the title
3. Before the content
4. After the content

### Global hooks
Site-wide, you can hook into any action. Enter the hook name in the field options.

### How to display form data on the front end
[Display your website data anywhere you choose](https://themekraft.com/wordpress-solutions/display-form-data/)

### Documentation and support
The code is documented inline and in the BuddyForms documentation. If you get stuck, the help links in the BuddyForms settings panel in your dashboard lead to our support.

== Installation ==

Install BuddyForms Hook Fields from the Plugins screen in your dashboard, or upload the plugin folder to "/wp-content/plugins/".

Activate BuddyForms Hook Fields on the Plugins screen. BuddyForms must be installed and active.

== Frequently Asked Questions ==

= Can I display post meta fields? =
Yes. You can display any post meta that is handled by a BuddyForms post form, including post meta that already exists. Create the form element and assign it to the meta key, so the plugin knows which type of data it has to render.

= Can I display ACF and Pods fields? =
Yes, when they are part of a BuddyForms form. They are displayed like any other form field.

== Screenshots ==

1. **BuddyForms Hook Fields - Form Builder "Field Options"** - Hook your BuddyForms form fields through the field options.

== Changelog ==
= 1.3.17 - 03 Oct 2026 =
* Required plugins are now declared with the WordPress "Requires Plugins" header instead of the bundled TGM Plugin Activation library.
* Fixed translations: every string now uses the plugin's own text domain.
* Requires WordPress 5.9 and PHP 7.4. Tested up to WordPress 7.1.

= 1.3.16 - 19 Nov 2023 =
* Updated Freemius SDK.
* Tested up to WordPress 6.4.1

= 1.3.15 - 21 Mar 2023 =
* Fixed issue with undefined variable.

= 1.3.14 - 30 Dec 2022 =
* Improved readme file description.

= 1.3.13 - 25 Dec 2022 =
* Fixed issue with shortcode loop.
* Tested up to WordPress 6.1.1

= 1.3.12 - 06 Nov 2022 =
* Updated download link in TGM class.
* Tested up to WordPress 6.1

= 1.3.11 - 04 Oct 2022 =
* Added auto activation when using bundle license.
* Tested up to WordPress 6.0.2

= 1.3.10 - 29 Aug 2022 =
* Fixed issue with field values output.
* Tested up to WordPress 6.0.1

= 1.3.9 - 26 May 2022 =
* Fixed vulnerability issue.
* Tested up to WordPress 6.0

= 1.3.8 - 17 May 2022 =
* Updated readme.txt

= 1.3.7 - 5 May 2022 =
* Fixed conflict between fields of different forms when displaying its value through shortcode.
* Added gutenberg block support.

= 1.3.6 - 23 Mar 2022 =
* Added new option to show Edit link in frontend.
* Added new shortcode to display single field in frontend.
* Tested up to WordPress 5.9

= 1.3.5 - 31 May 2021 =
* Hotfix: Fixed CSS issues for CPT other than posts. Eg: Product (WooCommerce).
* Hotfix: Fixed issue related with duplicate hooked media files on the Post Single View.

= 1.3.4 - 22 May 2021 =
* Fixed to do not show default thumbnail if no media was uploaded. 
* Improved thumbnail/preview support for PDF, Video, Audio (MP3) and Compressed files on the Upload and File fields.
* Tested up to WordPress 5.7

= 1.3.3 - 8 Mar 2021 =
* Fixed issue related with hooked fields on the single view list.
* Added improvements on the integration of Upload and File fields.
* Tested up 5.6.2

= 1.3.2 - 2 Nov 2020 =
* Fixed: Show upload field value.

= 1.3.1 - 23 April 2020 =
* Removed the limitation of the hook tab to appear in all form elements.
* Added a custom post type to handle the templates.
* Added a security to make all template private.
* Getting the form element output form buddyforms to show the table values.

= 1.3.0 - 27 March 2020 =
* Added option to use a page as template to customize the single post view with form shortcodes.

= 1.2.6 30 Sept 2019 =
* Fixed the output for the elements Category, Upload and File.

= 1.2.5 30 Sept 2019 =
* Fixed the option to output the content after/before the Title.

= 1.2.4 19 Sept 2019 =
* Added support for the File element.

= 1.2.3 -  Mar. 02 2019 =
* Freemius SDK Update

= 1.2.2 =
* Remove create function to use closures.

= 1.2.1 =
* Added a new option to display all form elements as table on the single under the post content

= 1.2 =
* Added Freemius Integration
* Fixed and issue with the dependencies check. The function tgmpa does not accepted an empty array.
* Added user_website as supported field

= 1.1.9.2 =
* Fixed and issue with the dependencies check. The function tgmpa does not accepted an empty array.

= 1.1.9.1 =
* Fixed an issue with the dependencies management. If pro was activated it still ask for the free version. Fixed now with a new default BUDDYFORMS_PRO_VERSION in the core to check if the pro is active.

= 1.1.9 =
* Add dependencies management with tgm

= 1.1.8 =
* fixed some smaller issues
* fixed some notice of undefined index

= 1.1.7 =
* Make it work with the latest version of buddyforms. the buddyforms array has changed so I adjust the code too the new structure
* Limit the hook options to supported form elements

= 1.1.6 =
* Make it work with the latest version of BuddyForms. the BuddyForms array has changed so I adjust the code too the new structure
* limit the hook options to supported form elements

= 1.1.5 =
* change the url to BuddyForms.com
* move the options to the new section addons

= 1.1.4 =
* Fixed a issue with the checkbox form element. Props to Thomas for the detailed issue Report.

= 1.1.3 =
* Reduce the query for a better performance
* Clean up the code

= 1.1.2 =
* Fixed some bugs reported by users.

= 1.1 =
* Spelling Corrections

= 1.0 =
* final 1.0 version
