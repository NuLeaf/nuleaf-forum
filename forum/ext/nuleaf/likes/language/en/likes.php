<?php
/**
 *
 * @package Likes Extensions
 * @copyright (c) 2015 NuLeaf Technologies
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * Extension to add a likes feature to all posts.
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
  'LIKE_POST' => 'Like post',
  'UNLIKE_POST' => 'Unlike post',
  'LIKE_BUTTON' => 'Like',
  'UNLIKE_BUTTON' => 'Unlike',

  'LIKE_NOT_AUTHORIZED' => 'You are not allowed to like posts in this forum.'
));