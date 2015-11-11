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

namespace nuleaf\likes\acp;

class likes_info
{
  function module()
  {
    return array(
      'filename'  => '\nuleaf\likes\likes_module',
      'title'   => 'LIKES',
      'modes'   => array(
        'settings' => array('title' => 'ACP_LIKES_SETTINGS', 'auth' => 'ext_nuleaf/likes && acl_a_board', 'cat' => array('LIKES')),
      ),
    );
  }
}
