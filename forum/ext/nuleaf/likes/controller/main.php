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

namespace nuleaf\likes\controller;

/**
 * @ignore
 */
use phpbb\json_response;

class main
{
  /** @var \phpbb\auth\auth */
  private $auth;

  /** @var \phpbb\db\driver\driver_interface */
  private $db;

  /** @var \nuleaf\likes\service\likes_manager */
  private $likes_manager;

  /** @var \phpbb\user */
  private $user;

  /** @var \phpbb\request\request_interface */
  private $request;

  /**
  * Constructor
  */
  public function __construct(
    \phpbb\auth\auth $auth,
    \phpbb\db\driver\driver_interface $db,
    \nuleaf\likes\service\likes_manager $likes_manager,
    \phpbb\user $user,
    \phpbb\request\request_interface $request
  )
  {
    $this->auth = $auth;
    $this->db = $db;
    $this->likes_manager = $likes_manager;
    $this->user = $user;
    $this->request = $request;
  }

  /**
  * Likes controller for route /like_post/{like}
  *
  * @param  int   @post_id  The post to be edited.
  */
  public function like_post($post_id)
  {
    // If unknown user or bot, cannot like.
    if ($this->user->data['user_id'] == ANONYMOUS || $this->user->data['is_bot'])
      return;

    // Add language variables for response.
    $this->user->add_lang_ext('nuleaf/likes', 'likes');
    
    // Grab forum id for permission.
    $sql = 'SELECT forum_id
    FROM ' . POSTS_TABLE . '
    WHERE post_id = ' . $post_id;
    
    $result = $this->db->sql_query_limit($sql, 1);
    $forum_id = $this->db->sql_fetchrow($result)['forum_id'];
    $this->db->sql_freeresult($result);

    // Does the user have permission to like posts in this forum?
    if ($this->auth->acl_get('!f_like', $forum_id))
    {
      $json_response = new json_response();
      $json_response->send(array(
        'error' => $this->user->lang('LIKE_NOT_AUTHORIZED')
      ));
      return;
    }

    if ($this->request->is_ajax())
    {
      $liked = $this->likes_manager->is_liked($post_id);
      if ($liked)
      {   
        // If post is already liked, unlike it.  
        $likes_count = $this->likes_manager->unlike($post_id);
      }
      else
      {
        // Else like the post.
        $likes_count = $this->likes_manager->like($post_id);
      }

      // Since the post has now been liked/unliked, $liked is reversed.
      $json_response = new json_response();
      $json_response->send(array(
        'likes_count'   => $likes_count,
        'liked'         => !$liked,
        
        'LIKE_POST'     => $this->user->lang('LIKE_POST'),
        'UNLIKE_POST'   => $this->user->lang('UNLIKE_POST'),
        'LIKE_BUTTON'   => $this->user->lang('LIKE_BUTTON'),
        'UNLIKE_BUTTON' => $this->user->lang('UNLIKE_BUTTON')
      ));
    }
  }
}