=== Meta for taxonomies ===
Contributors: momo360modena
Donate link: http://www.beapi.fr/donate/
Tags: tags, taxonomies, custom taxonomies, termmeta, meta, term meta, taxonomy
Requires at least: 3.0
Tested up to: 3.0
Stable tag: 1.0.0

Add meta for any taxonomies. Meta is attached to taxonomy context and not terms, this way allow to have metas different for the same term on 2 different taxonomies.

== Description ==

Add meta for any taxonomies. Meta is attached to taxonomy context and not terms, this way allow to have metas different for the same term on 2 different taxonomies.

This plugin don't any interface on WordPress ! Only somes methods for developpers.

This plugin propose many functions for terms :
	* add_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $meta_value = '', $unique = false )
	* delete_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $meta_value = '')
	* get_term_meta( $taxonomy = '', $term_id = 0, $meta_key = '', $single = false )
	* update_term_meta( $taxonomy = '', $term_id = 0, $meta_key, $meta_value, $prev_value = '' )
	
And many others functions term taxonomy context :
	* add_term_taxonomy_meta( $term_taxonomy_id = 0, $meta_key = '', $meta_value = '', $unique = false )
	* delete_term_taxonomy_meta( $term_taxonomy_id = 0, $meta_key = '', $meta_value = '')
	* get_term_taxonomy_meta( $term_taxonomy_id = 0, $meta_key = '', $single = false )
	* update_term_taxonomy_meta( $term_taxonomy_id = 0, $meta_key, $meta_value, $prev_value = '' )
	
And many others...
	
For full info go the [Meta for taxonomies](http://redmine.beapi.fr/projects/show/meta-for-taxonomies) page.

== Installation ==

1. Download, unzip and upload to your WordPress plugins directory
2. Activate the plugin within you WordPress Administration Backend
3. Develop your plugin for used it !