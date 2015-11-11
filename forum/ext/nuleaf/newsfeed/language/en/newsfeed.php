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
	'NEWSFEED'	   => 'Newsfeed',
	'NF_NO_TOPICS' => 'There are currently no new topics.',
));
