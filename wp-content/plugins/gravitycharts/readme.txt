=== GravityCharts ===
Tags: gravitykit, gravityview, gravity forms, charts, chart.js
Requires at least: 5.2
Tested up to: 6.3
Contributors: The GravityKit Team
License: GPL 2
Requires PHP: 7.2.0

Beautifully display your Gravity Forms data in charts.

== Description ==

Beautifully display your Gravity Forms entries in charts. Learn more on [gravitykit.com](https://www.gravitykit.com/products/gravitycharts/).

== Installation ==

1. Upload plugin files to your plugins folder, or install using WordPress' built-in Add New Plugin installer
2. Activate the plugin
3. Follow the instructions

== Changelog ==

= 1.6 on September 7, 2023 =

* Added: Aggregate entries on timeline charts by year, quarter, month, week, or day (under "Axis" chart settings)
* Added: Chart language (including date labels) are based on the WordPress locale
* Improved: Support for RTL languages
* Fixed: A timeline chart could show an empty required field on the wrong timeline type
* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.2.2

= 1.5.3 on July 12, 2023 =

* Fixed: Fatal error if the multi-input field choice value is empty
* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.1.1

= 1.5.2 on June 29, 2023 =

__Developer Updates:__

* Fixed: Restored the `gk/gravitycharts/color-palettes` filter, which was accidentally removed in version 1.4
* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.1.0

= 1.5.1 on June 5, 2023 =

* Fixed: 20-entry limit for chart data when conditional logic is enabled

= 1.5 on May 23, 2023 =

* Improved: Chart legends now work with Bar/Column chart types

__Developer Updates:__

* Upgraded: Chart.js from Version 4.1 to 4.3

= 1.4 on May 11, 2023 =

* Added: [Plot data on a timeline](https://docs.gravitykit.com/article/929-plotting-data-against-time) with the new "Data Type" setting (available on Bar/Column and Line/Area chart types)
	- Display entry count, plot average values, or chart summed values
	- Yes, this supports pricing fields!
	- Yes, you can chart the number of entries submitted per day!
* Added: Chart labels can now be displayed above or next to charted values ([learn how to configure labels](https://docs.gravitykit.com/article/930-adding-labels-to-a-chart))
	- Configure labels in a new "Labels" chart configuration panel
 	- Choose what is shown (value, label, or percentage)
	- Define the position of labels (default, border, outside)
	- Set the label font size and colors
* Added: Chart values of a single entry
	- Show on a form confirmation page by adding a [`{gravitycharts}` merge tag](https://docs.gravitykit.com/article/857-gravitycharts-merge-tag) to the form confirmation text and including `entry=true` in the merge tag
	- Add `entry=<entry ID>` to the [`[gravitycharts]` shortcode](https://docs.gravitykit.com/article/850-gravitycharts-shortcode)
* Added: Ability to switch the area fill type for a line or radar chart using the Design panel "Area Fill" setting
* Improved: Keyboard focus is trapped inside active configuration dialog for accessibility
* Improved: Polar Area charts can now have a gap between segments
* Modified: Chart configuration is hidden until a data source is selected
* Fixed: Auto Scale setting was not parsed properly
* Fixed: A merge tag without attributes could throw an exception
* Fixed: Dynamic fields that use [GP Populate Anything](https://gravitywiz.com/documentation/gravity-forms-populate-anything/?ref=263) are now supported
* Fixed: Incompatibility with some plugins/themes that use Laravel components

__Developer Updates:__

* Added: `gk/gravitycharts/api/label-width` filter to split long labels into multiple lines
* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.0.12
* Upgraded: Chart.js to 4.1

= 1.3.5 on February 20, 2023 =

**Note: GravityCharts now requires PHP 7.2 or newer**

* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.0.9

= 1.3.4 on January 5, 2023 =

* Updated: [Foundation](https://www.gravitykit.com/foundation/) to version 1.0.8

= 1.3.3 on December 21, 2022 =

* Fixed: PHP 8.1 notices
* Fixed: Fatal error on some hosts due to a conflict with one of the plugin dependencies (psr/log)

= 1.3.2 on December 1, 2022 =

* Fixed: It was not possible to remove an expired license key

= 1.3.1 on November 29, 2022 =

* Added: Block shows information on adding a feed when no forms are available
* Fixed: Chart autoscaling not working properly
* Fixed: "Undefined index" PHP notice
* Fixed: Fatal error when loading plugin translations
* Fixed: Slow loading times on some hosts
* Fixed: Plugin failing to install on some hosts

= 1.3.0.1 on November 1, 2022 =

* Fixed: Plugin was not appearing in the "Add-Ons" section of the Gravity Forms System Status page

= 1.3 on October 24, 2022 =

* Added: New WordPress admin menu where you can now centrally manage all your GravityKit product licenses and settings ([learn more about the new GravityKit menu](https://www.gravitykit.com/foundation/))
    - Go to the WordPress sidebar and check out the GravityKit menu!
    - We have automatically migrated your existing GravityCharts license, which was previously entered in the Gravity Forms settings page
    - Request support using the "Grant Support Access" menu item
* Fixed: Charts were including entries from the trash and spam folders
* Fixed: Prevent JavaScript and CSS files from loading on every page

= 1.2.0.2 on August 23, 2022 =

* Fixed: Fatal error introduced in GravityCharts 1.2 related to Merge Tags

= 1.2.0.1 on August 19, 2022 =

* Fixed: Fatal error and PHP notices introduced in GravityCharts 1.2 related to Merge Tags

= 1.2 on August 17, 2022 =

* Added: Image charts! Embed images of your charts anywhere Merge Tags or shortcodes are allowed, including email confirmations. **This feature is in beta and may not work properly.** Please [report any issues to support@gravitykit.com](mailto:support@gravitykit.com).
	- Embed image charts in Gravity Forms Confirmations and Notifications using Merge Tags — [Learn more about the `{gravitycharts}` Merge Tag](https://docs.gravitykit.com/article/857-gravitycharts-merge-tag)
	- Added `embed_type` parameter to the `[gravitycharts]` shortcode – [See the shortcode docs](https://docs.gravitykit.com/article/850-gravitycharts-shortcode)

__Developer Notes:__

* Added: `gk/gravitycharts/image-charts/quickchart/instance` action to modify the QuickChart instance before rendering — [See the hook and code examples](https://docs.gravitykit.com/article/855-customizing-image-charts)

= 1.1.1 on July 14, 2022 =

* Fixed: Fatal error when embedding an incorrectly-formatted `[gravitycharts]` shortcode without any parameters

= 1.1 on July 7, 2022 =

* Added: `[gravitycharts]` shortcode to embed charts in a post/page (e.g., `[gravitycharts id="5"]`)
* Fixed: Fatal error on Gravity Forms ≤2.5.10 when using a non-checkbox field as the data source

= 1.0.4 on June 29, 2022 =

* Fixed: Multiple charts embedded in a post/page would get duplicated
* Fixed: When multiple charts using conditional logic are included in a GravityView View via a widget, only the first chart would display the correct data
* Fixed: It was not possible to set the maximum scale value for the Line/Area chart type

= 1.0.3 on May 30, 2022 =

* Fixed: GravityView charts widget would not display when the View is embedded in a page/post
* Fixed: Fatal error due to double initialization of the GravityCharts Gutenberg block
* Improved: GravityView charts widget admin scripts are only loaded in the View editor

= 1.0.2 on May 18, 2022 =

__Developer Updates:__

* Added: `gk/gravitycharts/capabilities/access` filter to control user capability required to access the plugin

= 1.0.1 on May 16, 2022 =

Fixed: Fatal error when GravityView is not installed

= 1.0 on May 10, 2022 =

* Liftoff! 🚀
