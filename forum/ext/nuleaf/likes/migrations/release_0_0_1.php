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

namespace nuleaf\likes\migrations;

class release_0_0_1 extends \phpbb\db\migration\migration
{
  private $version = '0.0.1';

  public function effectively_installed()
  {
    $installed_version = $this->config['likes_version'];
    return isset($installed_version) && version_compare($installed_version, $this->version, '>=');
  }

  public function update_schema()
  {
    return array(
      'add_tables' => array(
        $this->table_prefix . 'likes' => array(
          'COLUMNS' => array(
            'id'      => array('UINT', null, 'auto_increment'),
            'post_id' => array('UINT', 0),
            'user_id' => array('UINT', 0)
          ),
          'PRIMARY_KEY' => 'id',
          'KEYS' => array(
            'idx_post' => array('INDEX', array('post_id')),
            'idx_user' => array('INDEX', array('user_id'))
          )
        )
      ),
      'add_columns' => array(
        POSTS_TABLE => array(
          'post_likes' => array('UINT', 0)
        )
      )
    );
  }

  public function update_data()
  {
    return array(
      array('config.add', array('likes_version', $this->version)),

      array('module.add', array(
        'acp',
        'ACP_CAT_DOT_MODS',
        'ACP_LIKES'
      )),

      array('module.add', array(
        'acp', 'ACP_LIKES', array(
          'module_basename' => '\nuleaf\likes\acp\likes_module',
          'modes'           => array('settings')
        )
      ))
    );
  }

  public function revert_schema()
  {
    return array(
      'drop_tables' => array(
        $this->table_prefix . 'likes'
      ),
      'drop_columns' => array(
        POSTS_TABLE => array(
          'post_likes'
        )
      )
    );
  }

  public function revert_data()
  {
    return array(
      array('config.remove', array('likes_version', $this->version)),

      array('module.remove', array(
        'acp', 'ACP_LIKES', array(
          'module_basename' => '\nuleaf\likes\acp\likes_module'
        ),
      )),
      array('module.remove', array(
        'acp', 'ACP_CAT_DOT_MODS', 'ACP_LIKES'
      )),
    );
  }
}
