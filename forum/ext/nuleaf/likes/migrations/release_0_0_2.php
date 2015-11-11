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

class release_0_0_2 extends \phpbb\db\migration\migration
{
  private $version = '0.0.2';

  public function effectively_installed()
  {
    $installed_version = $this->config['likes_version'];
    return isset($installed_version) && version_compare($installed_version, $this->version, '>=');
  }

  static public function depends_on()
  {
    return array('\nuleaf\likes\migrations\release_0_0_1');
  }

  public function update_data()
  {
    return array(
      array('config.update', array('likes_version', $this->version)),

      array('config.add', array('likes_on', true)),
      array('permission.add', array('f_like', false, 'f_read')),    
    );
  }

  public function revert_data()
  {
    return array(
      array('config.remove', array('likes_on')),
      array('permission.remove', array('f_like')),
    );
  }
}
