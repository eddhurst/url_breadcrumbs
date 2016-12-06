# url_breadcrumbs
Wordpress Plugin for 'URL Breadcrumbs' a set of developer functions to easily generate breadcrumbs from your URL.

#Upcoming

#Known issues

1.3 brings support to link to pseudo pages, however custom post type root pages are ignored in this also, as they are not included in the wp->matched_queries call.

get_cat_ID uses category Names to identify, rather than slugs, so isn't a great solution. Will need to rectify this.

#Changelog

1.3 - Support added to identify pseudo pages (i.e. date archives) to allow for breadcrumb links to incorporate hard to pinpoint pages, as well as to strip out unwanted breadcrumbs to non-pages such as category and tag base.

1.2.1 -  Support for post status identification.

1.2 - Add in conditional check for functions, filters for custom hooks.

1.1 - Add in support for taxonomies and custom post types through use of 'type' array element.

1.0 - Initial commit. Adds in two functions to allow developers to generate breadcrumb output, or to create array of breadcrumb variables to create their own breadcrumbs.