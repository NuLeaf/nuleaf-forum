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

namespace nuleaf\likes\service;

class likes_manager
{
  /** @var \phpbb\db\driver\driver_interface */
  private $db;

  /** @var \phpbb\user */
  private $user;

  /** @var string */
  private $table_prefix;

  /**
  * Constructor
  */
  public function __construct(
    \phpbb\db\driver\driver_interface $db,
    \phpbb\user $user,
    $table_prefix
  )
  {
    $this->db = $db;
    $this->user = $user;
    $this->table_prefix = $table_prefix;
  }

  /**
  * Like a post by first adding an entry into the likes table
  * and then updating the like counter in the post table.
  *
  * @param  int   @post_id        The post to be edited.
  *
  * @return int   @likes_count    The new likes count.
  */
  public function like($post_id)
  {
    // Insert into likes table.
    $sql_ary[] = array(
      'post_id' => $post_id,
      'user_id' => $this->user->data['user_id']
    );
    $this->db->sql_multi_insert($this->table_prefix . 'likes', $sql_ary);

    $likes_count = $this->inc_likes_count($post_id);
    return $likes_count;
  }

  /**
  * Unlike a post by first removing the entry from the likes table
  * and then updating the like counter in the post table.
  *
  * @param  int   @post_id       The post to be edited.
  *
  * @return int   @likes_count   The new likes count.
  */
  public function unlike($post_id)
  {
    $sql = 'DELETE FROM ' . $this->table_prefix . 'likes
      WHERE post_id = ' . $post_id . '
        AND user_id = ' . $this->user->data['user_id'];
    $this->db->sql_query($sql);

    $likes_count = $this->dec_likes_count($post_id);
    return $likes_count;
  }

  /**
  * Check if the post is already liked.
  *
  * @param  int   @post_id    The post in question.
  *
  * @return bool  @liked      True if the post is already liked, false otherwise.
  */
  public function is_liked($post_id)
  {
    $sql = 'SELECT id
        FROM ' . $this->table_prefix . 'likes
        WHERE post_id = ' . $post_id . '
          AND user_id = ' . $this->user->data['user_id'];

    $result = $this->db->sql_query_limit($sql, 1);
    $liked = $this->db->sql_fetchrow($result)['id'];
    $this->db->sql_freeresult($result);

    // id cannot be 0 because it is an unsigned int.
    return ($liked) ? true : false;
  }

  /**
  * Gets the number of likes of a post.
  *
  * @param  int   @post_id      The post in question.
  *
  * @return int   @likes_count  The like count of the post.
  */
  public function get_likes_count($post_id)
  {
    $sql = 'SELECT post_likes
      FROM ' . POSTS_TABLE . '
      WHERE post_id = ' . $post_id;

    $result = $this->db->sql_query_limit($sql, 1);
    $likes_count = $this->db->sql_fetchrow($result)['post_likes'];
    $this->db->sql_freeresult($result);

    return $likes_count;
  }

  /**
  * Increase the like counter in the post table.
  *
  * @param  int   @post_id      The post to be edited.
  *
  * @return int   @likes_count  The new likes count.
  */
  private function inc_likes_count($post_id)
  {
    $likes_count = $this->get_likes_count($post_id) + 1;
    $this->update_posts_table($post_id, $likes_count);
    return $likes_count;
  }

  /**
  * Decrease the like counter in the post table.
  *
  * @param  int   @post_id      The post to be edited.
  *
  * @return int   @likes_count  The new likes count.
  */
  private function dec_likes_count($post_id)
  {
    $likes_count = $this->get_likes_count($post_id) - 1;
    $this->update_posts_table($post_id, $likes_count);
    return $likes_count;
  }

  /**
  * Update the post like counter of the post.
  *
  * @param  int   @post_id        The post to be edited.
  *         int   @likes_count    The new likes count.
  */ 
  private function update_posts_table($post_id, $likes_count)
  {
    $sql = 'UPDATE ' . POSTS_TABLE . '
      SET post_likes = ' . $likes_count . '
      WHERE post_id = ' . $post_id;

    $this->db->sql_query($sql);
  }
}