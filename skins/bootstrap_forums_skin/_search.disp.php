<?php
/**
 * This is the template that displays the site map (the real one, not the XML thing) for a blog
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template.
 * To display the archive directory, you should call a stub AND pass the right parameters
 * For example: /blogs/index.php?disp=postidx
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 * @subpackage bootstrap_forums
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

echo '<div class="forums_table_search panel panel-default">';

// --------------------------------- START OF COMMON LINKS --------------------------------
skin_widget( array(
		// CODE for the widget:
		'widget' => 'coll_search_form',
		// Optional display params
		'block_start' => '<div class="panel-heading">',
		'block_end' => '</div></div>',
		'block_display_title' => false,
		'disp_search_options' => 0,
		'search_class' => 'extended_search_form',
		'use_search_disp' => 1,
		'button' => T_('Search')
	) );
// ---------------------------------- END OF COMMON LINKS ---------------------------------

// Display the search result
search_result_block( array(
		'title_prefix_post'     => T_('Topic: '),
		'title_prefix_comment'  => T_('Reply: '),
		'title_prefix_category' => T_('Forum: '),
		'title_prefix_tag'      => T_('Tag: '),
		'block_start' => '<div class="panel panel-default">',
		'block_end'   => '</div>',
		'row_start'   => '<div class="panel-body">',
		'row_end'     => '</div>',
		'pagination'  => array(
				'block_start' => '<div class="evo_search_navigation center">',
				'block_end' => '</div>',
				'page_current_template' => '<strong class="current_page">$page_num$</strong>',
				'page_item_before'      => '',
				'page_item_after'       => '',
				'prev_text'             => T_('Previous'),
				'next_text'             => T_('Next'),
				'prev_class'            => 'prev',
				'next_class'            => 'next',
			)
	) );

echo '</div>';
?>