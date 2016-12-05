# url_breadcrumbs
Wordpress Plugin for 'URL Breadcrumbs' a set of developer functions to easily generate breadcrumbs from your URL.

#Upcoming

Need to find a way to identify if a page is a valid archive page, or if it's a pseudo page (i.e. 'category' ) that will result in a 404.

In 1.2.1 we attempt to identify post status and strip out links where post is not a published post (i.e. private or draft), but subsequently has knocked out links for taxonomy pages, need to fix this before pushing to major release.

#Changelog

1.2.1 -  Support for post status identification.

1.2 - Add in conditional check for functions, filters for custom hooks.

1.1 - Add in support for taxonomies and custom post types through use of 'type' array element.

1.0 - Initial commit. Adds in two functions to allow developers to generate breadcrumb output, or to create array of breadcrumb variables to create their own breadcrumbs.