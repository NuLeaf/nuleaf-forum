<?php
/**
 *
 * @package Newsfeed Extension
 * @copyright //
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * Based on the Recent Topics Extension by PayBas
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'NEWSFEED'						=> 'Newsfeed',
	'NEWSFEED_LIST'					=> 'Display on "newsfeed"',
	'NEWSFEED_LIST_EXPLAIN'			=> 'Enable to display topics in this forum in the "newsfeed" extension.',

	'NF_CONFIG'						=> 'Configuration',
	'NF_NUMBER'						=> 'Newsfeed',
	'NF_NUMBER_EXP'					=> 'Number of topics to display.',
	'NF_PAGE_NUMBER'				=> 'Newsfeed pages',
	'NF_PAGE_NUMBER_EXP'			=> 'You can display more topics using pagination. Just enter 1 to disable this feature. If you enter 0 there will be as much pages as needed to display all topics of your forum (not advised).',
	'NF_PARENTS'					=> 'Display parent forums',
	'NF_PARENTS_EXP'				=> 'Display parent forums inside the topic row of newsfeed.',
	'NF_UNREADONLY'					=> 'Only display unread topics',
	'NF_UNREADONLY_EXP'				=> 'Enable to only display unread topics. This function uses the same settings (excluding forums/topics etc.) as normal mode. Note: this only works for logged-in users; guests will get the normal list.',
	'NF_PERSISTENT_DISPLAY'			=> 'Persistent display',
	'NF_PERSISTENT_DISPLAY_EXP'     => 'Enable to display the Newsfeed box even when there are no topics to display.',

	'NF_VIEW_ON'					=> 'Display newsfeed on:',

	'NF_TOPICS_TO_DISPLAY'				  	  => 'Topics to display',
	'NF_TOPICS_TO_DISPLAY_EXP'				  => 'Options to be shown on newsfeed',
	'NF_TOPICS_IN_ANNOUNCEMENTS'          	  => 'Topics in Announcements',
	'NF_TOPICS_BY_USER'					  	  => 'Topics made by user',
	'NF_TOPICS_RESPONDED_BY_USER'         	  => 'Topics the user has posted in',
	'NF_TOPICS_BY_USER_TEAM'		 	      => 'Topics made by team members',
	'NF_TOPICS_IN_USER_MOST_ACTIVE_FORUM'     => 'Topics in forums where user is most active'
));
