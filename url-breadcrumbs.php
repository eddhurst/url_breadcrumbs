<?php
/**
 * Plugin Name: URL Breadcrumbs
 * Plugin URI: http://wordpress.org/plugins/url-breadcrumbs
 * Description: A set of developer functions to easily generate breadcrumbs from your URL.
 * Author: Edd Hurst
 * Version: 1.3
 * Author URI: http://eddhurst.co.uk/
 *
 * @package URL_Breadcrumbs
 * @version 1.3
 */

if ( ! function_exists( 'get_breadcrumbs' ) ) {

	/**
	 * Retrieves hierarchical post information from current page URL.
	 *
	 * @return array	$breadcrumb_items	[x] => ( ['id'], ['title'], ['slug'], ['url'], ['type'] ).
	 */
	function get_breadcrumbs() {

		global $wp;

		// Split page URL into component parts by / character.
		$breadcrumb_items = explode( '/', $wp->request );

		$breadcrumb_url = esc_url( home_url( '/' ) );

		// Loop through each breadcrumb item, to identify what content it links to.
		foreach ( $breadcrumb_items as $key => $breadcrumb ) :

			$breadcrumb_url = esc_url( $breadcrumb_url . $breadcrumb . '/' );

			$breadcrumb_id = url_to_postid( $breadcrumb_url );

			// If breadcrumb_id has returned as non 0, it is a post / page / custom post.
			if ( 0 !== $breadcrumb_id ) :

				$breadcrumb_title = get_the_title( $breadcrumb_id );
				$breadcrumb_type = get_post_type( $breadcrumb_id );

			else :		// If breadcrumb_id is 0, page could be a taxonomy.

				$breadcrumb_id = get_cat_ID( $breadcrumb );
				$breadcrumb_type = 'taxonomy';
				$breadcrumb_title = get_cat_name( $breadcrumb_id );

				// If breadcumb_id is still null, breadcrumb is likely a pseudo-page.
				if ( 0 === $breadcrumb_id ) :

					$breadcrumb_id = '';
					$breadcrumb_title = $breadcrumb;
					$breadcrumb_type = '';

				endif;

			endif;

			$breadcrumb_items[ $key ] = array(
				'id'	=> $breadcrumb_id,
				'title'	=> $breadcrumb_title,
				'slug'	=> $breadcrumb,
				'url'	=> $breadcrumb_url,
				'type'	=> $breadcrumb_type,
			);

		endforeach;

		return $breadcrumb_items;

	}
}

if ( ! function_exists( 'generate_breadcrumb_matched_query' ) ) {


	/**
	 * Strips apart the wp->matched_query to identify which parts of URL are valid pages.
	 *
	 * @return array	$breadcrumb_matches		( ['total_items'] => array(), ['{query_type}'] => {post-slug}, ... )
	 */
	function generate_breadcrumb_matched_query() {

		global $wp;

		// Split wp->matched_query into query parameters
		$breadcrumb_matched_query_items = explode( '&', $wp->matched_query );

		// Set a top-level array to easily identify items in, as well as individual items in case we need to refine.
		$breadcrumb_matches['total_items'] = array();

		foreach ( $breadcrumb_matched_query_items as $query_item ) :

			// Split individual query parameters into their keys and values
			$exploded_query_item = explode( '=', $query_item );

			if ( ! empty( $exploded_query_item[1] ) ) :

				// Check if value is singular or mulitple, If multiple, save as array.
				if ( preg_match( '/%2F/', $exploded_query_item[1] ) ) :

					$exploded_values = explode( '%2F', $exploded_query_item[1] );

					foreach ( $exploded_values as $exploded_value ) :

						// Include value in top-level array.
						$breadcrumb_matches['total_items'][] .= $exploded_value;
						;

						// Include value in array with specific query key.
						$breadcrumb_matches[ $exploded_query_item[0] ][] = $exploded_value;

					endforeach;

				else :

					// Include value in top-level array.
					$breadcrumb_matches['total_items'][] .= $exploded_query_item[1];

					// Include value in array with specific query key.
					$breadcrumb_matches[ $exploded_query_item[0] ] = $exploded_query_item[1];

				endif;

			endif;

		endforeach;

		return $breadcrumb_matches;

	}
}

if ( ! function_exists( 'generate_breadcrumb_output' ) ) {

	/**
	 * Generates breadcrumbs into user-friendly output.
	 *
	 * @param string	$base_title     The title of the root element.
	 * @param string	$separator      The separator between elements.
	 * @param bool		$links			Whether or not to have links in elements.
	 *
	 * @return string	$breadcrumb_output		Output for breadcrumb trail, complete with links and separators as necessary.
	 */
	function generate_breadcrumb_output( $base_title = 'Home', $separator = '&raquo;', $links = true ) {

		// If breadcrumb items does not return as array, stop.
		if ( ! is_array( $breadcrumb_items = get_breadcrumbs() ) ) {
			return false;
		}

		if ( ! is_array( $breadcrumbs_queried = generate_breadcrumb_matched_query() ) ) {
			return false;
		}

		global $wp;

		// Add white space around separator.
		$separator = ' ' . $separator . ' ';

		// Escape variables, just in case.
		$base_title = esc_attr( $base_title );
		$separator = esc_attr( $separator );
		$links = esc_attr( $links );

		// Generate output for base url.
		$breadcrumb_base_link = '<a href="' . esc_url( home_url() ) . '" title="' . $base_title . '">' . $base_title . '</a>';

		// Allow users the option to filter / remove / replace the home link.
		$breadcrumbs_output = apply_filters( 'breadcrumbs_home_link', $breadcrumb_base_link );

		foreach ( $breadcrumb_items as $key => $breadcrumb ) :

			// Check to see if breadcrumb slug is in wp->matched_queries.
			$breadcrumb_in_query = in_array( $breadcrumb['slug'], $breadcrumbs_queried['total_items'], true );

			// If breadcrumb not in matched queries, skip.
			if ( ! $breadcrumb_in_query ) {
				continue;
			}

			// If post is a page / post / attachment identify the current post status.
			if ( 'taxonomy' !== $breadcrumb['type'] && $breadcrumb['type'] ) :

				$breadcrumb_post_status = get_post_status( $breadcrumb['id'] );

			else :

				$breadcrumb_post_status = 'N/A';

			endif;

			// If links enabled and breadcrumb type is empty or set to taxonomy, or post status is set to publish, output link.
			if ( $links && ( ( empty( $breadcrumb['type'] ) || 'taxonomy' === $breadcrumb['type'] ) || 'publish' === $breadcrumb_post_status ) ) :

				$breadcrumbs_output .= $separator . '<a href="' . $breadcrumb['url'] . '" title="' . $breadcrumb['title'] . '">' . $breadcrumb['title'] . '</a>';

				// Else, just output page title.
			else :

				$breadcrumbs_output .= $separator . $breadcrumb['title'];

			endif;

		endforeach;

		return apply_filters( 'breadcrumbs_output', $breadcrumbs_output );

	}
}
