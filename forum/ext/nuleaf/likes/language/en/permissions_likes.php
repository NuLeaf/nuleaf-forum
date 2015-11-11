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
  'ACL_F_LIKE' => 'Can like posts'
));