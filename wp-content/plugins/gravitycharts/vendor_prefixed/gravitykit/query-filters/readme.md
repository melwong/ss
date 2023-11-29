# Query Filters

Advanced field filters (think: conditional logic on steroids) for Gravity Forms' `GF_Query`. Based on [GravityView Advanced Filter](https://github.com/gravityview/Advanced-Filter) extension.    

The library is used by [GravityExport](https://github.com/GFExcel/gfexcel-pro) (Filters and Save add-ons), [GravityView Calendar](https://github.com/gravityview/calendar), and [GravityCharts](https://github.com/gravityview/gravitycharts).

### Changelog

v1.8
* Fixed: "Greater than" and "less than" comparison not working with the "Total" field

v1.7
* Added: Custom JS event is triggered on the `document` when filters are updated. Default: `#gk-query-filters/updated` where `#gk-query-filters` is a `targetElementSelector` parameter that's passed to `QueryFilters::enqueue_scripts()`.

v1.6
* Fixed: Merge tags were not being processed
* Fixed: Potential fatal error when GravityView is not installed due to the missing `gravityview_get_field_type` function

v1.5
* Improved: Prevent multiple initialization of Query Filters on the same DOM element
* Fixed: Multiple operators were set on the first condition resulting in a backend PHP warning because a single operator is expected

v1.4
* Improved: Added "Entry Approval Status" filter condition

v1.3
* Improved: Allow having multiple Query Filters instances on the same page
 
v1.2
* Improved: It is now possible to filter only those entries that have been updated since creation

v1.1
* Fixed: JavaScript error when a Conditional Logic filter is configured for a multi-input form field that no longer exists
* Fixed: MySQL error on certain hosts when "Date Updated", "Date Created" or "Payment Date" entry meta are filtered using the "is empty" condition

v1.0
* Launch!
