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

namespace nuleaf\likes\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
  public static function getSubscribedEvents()
  {
    return array(
      'core.viewtopic_modify_post_row' => 'add_likes_to_post',
      'core.permissions' => 'add_new_permissions'
    );
  }

  /* @var \phpbb\config\config */
  private $config;

  /* @var \phpbb\controller\helper */
  private $helper;

  /** @var \nuleaf\likes\service\likes_manager */
  private $likes_manager;

  /* @var \phpbb\template\template */
  private $template;

  /** @var \phpbb\user */
  private $user;

  /**
  * Constructor
  */
  public function __construct(
    \phpbb\config\config $config,
    \phpbb\controller\helper $helper,
    \nuleaf\likes\service\likes_manager $likes_manager,
    \phpbb\template\template $template,
    \phpbb\user $user
  )
  {
    $this->config = $config;
    $this->helper = $helper;
    $this->likes_manager = $likes_manager;
    $this->template = $template;
    $this->user = $user;
  }

  /**
  * Event: core.viewtopic_modify_post_row.
  * 
  * Adds varibles needed for the likes extension to be displayed on each post.
  */
  public function add_likes_to_post($event)
  {
    // Add language files to postrow display loop.
    $this->user->add_lang_ext('nuleaf/likes', 'likes');

    $post_row = $event['post_row'];

    // Modify post row data to include variables needed for the likes to be displayed on post.
    $post_id = $post_row['POST_ID'];
    $post_row['LIKES_COUNT'] = $this->likes_manager->get_likes_count($post_id);
    $post_row['LIKED'] = $this->likes_manager->is_liked($post_id);
    $post_row['U_LIKES'] = $this->helper->route('nuleaf_likes_controller', array('post_id' => $post_id));

    $event['post_row'] = $post_row;
    
    // Include necessary functions and css.
    if ($this->config['likes_on'])
    {
      $this->template->assign_vars(array(
        'LIKES_INCLUDEJS' => true,
        'LIKES_INCLUDECSS' => true,
        'LIKES_ON' => true,
      ));
    }
  }

  /**
  * Event: core.permissions.
  *
  * Adds permissions for users to like posts.
  */
  public function add_new_permissions($event)
  {
    $permissions = $event['permissions'];
    $permissions['f_like'] = array('lang' => 'ACL_F_LIKE', 'cat' => 'misc');
    $event['permissions'] = $permissions;
  }
}
