<?php
/**
 * WPClubManager Club Functions.
 *
 * Functions for clubs.
 *
 * @author 		ClubPress
 * @category 	Core
 * @package 	WPClubManager/Functions
 * @version     1.5.9
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wpcm_head_to_heads( $post ) {
	
	global $wpdb;

	$club = get_default_club();
	$querystr = "
	SELECT {$wpdb->prefix}posts.*
		
		FROM {$wpdb->prefix}posts 

		INNER JOIN {$wpdb->prefix}postmeta
		ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) 
		AND( {$wpdb->prefix}postmeta.meta_key = 'wpcm_home_club' 
		AND {$wpdb->prefix}postmeta.meta_value IN ('$post','$club') ) 

		INNER JOIN {$wpdb->prefix}postmeta AS mt1
		ON ( {$wpdb->prefix}posts.ID = mt1.post_id  
		AND  mt1.meta_key = 'wpcm_away_club' and mt1.meta_value IN ('$post','$club') )

		WHERE 
		1=1 AND 
		{$wpdb->prefix}posts.post_type = 'wpcm_match' AND 
		(({$wpdb->prefix}posts.post_status = 'publish'))

		GROUP BY {$wpdb->prefix}posts.ID
		
		ORDER BY {$wpdb->prefix}posts.post_date ASC
	";
	$matches = $wpdb->get_results($querystr, OBJECT);

	wp_reset_postdata();

	return $matches;
}

function wpcm_head_to_head_count( $matches ) {

	$club = get_default_club();
	$wins = 0;
	$losses = 0;
	$draws = 0;
	$count = 0;
	foreach( $matches as $match ) {

		$count ++;
		$home_club = get_post_meta( $match->ID, 'wpcm_home_club', true );
		$home_goals = get_post_meta( $match->ID, 'wpcm_home_goals', true );
		$away_goals = get_post_meta( $match->ID, 'wpcm_away_goals', true );

		if ( $home_goals == $away_goals ) {
			$draws ++;
		}

		if ( $club == $home_club ) {
			if ( $home_goals > $away_goals ) {
				$wins ++;
			}
			if ( $home_goals < $away_goals ) {
				$losses ++;
			}
		} else {
			if ( $home_goals > $away_goals ) {
				$losses ++;
			}
			if ( $home_goals < $away_goals ) {
				$wins ++;
			}
		}

	}
	$outcome = array();
	$outcome['total'] = $count;
	$outcome['wins'] = $wins;
	$outcome['draws'] = $draws;
	$outcome['losses'] = $losses;

	return apply_filters( 'wpcm_head_to_head_count', $outcome, $matches );

}

function get_club_venue( $post ) {

	$id = get_the_terms( $post, 'wpcm_venue' );
	
	if ( is_array( $id ) ) {
		$venues = reset($id);
		$t_id = $venues->term_id; 
		$venue_meta = get_option( "taxonomy_term_$t_id" );
		$venue['id'] = $t_id;
		$venue['name'] = $venues->name;
		if( is_array( $venue_meta ) ) {
			if( array_key_exists('wpcm_address', $venue_meta) ) {
				$venue['address'] = $venue_meta['wpcm_address'];
			}
			if( array_key_exists('wpcm_capacity', $venue_meta) ) {
				$venue['capacity'] = $venue_meta['wpcm_capacity'];
			}
		}
		$venue['description'] = $venues->description;
	}
	 return $venue;
}
	