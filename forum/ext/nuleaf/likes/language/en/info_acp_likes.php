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
  'ACP_LIKES' => 'Likes',
  'ACP_LIKES_SETTINGS' => 'Settings',
  'ACP_LIKES_SETTINGS_SAVED' => 'Likes settings changed.',

  'LIKES_ON' => 'Enable Likes',
  'LIKES_ON_EXP' => 'Enable to display likes icon on the header bar of each post.',
));