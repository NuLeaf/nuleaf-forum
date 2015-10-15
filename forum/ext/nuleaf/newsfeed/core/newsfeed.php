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

namespace nuleaf\newsfeed\core;

class newsfeed
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\content_visibility */
	protected $content_visibility;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\event\dispatcher_interface */
	protected $dispatcher;

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $phpEx;

	public function __construct(\phpbb\auth\auth $auth, \phpbb\cache\service $cache, \phpbb\config\config $config, \phpbb\content_visibility $content_visibility, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $dispatcher, \phpbb\pagination $pagination, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, $root_path, $phpEx)
	{
		$this->auth = $auth;
		$this->cache = $cache;
		$this->config = $config;
		$this->content_visibility = $content_visibility;
		$this->db = $db;
		$this->dispatcher = $dispatcher;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->phpEx = $phpEx;
	}

	// Modified from /includes/functions.php
	protected function get_unread_topics($user_id = false, $sql_extra = '', $sql_sort = '', $sql_limit = 1001, $sql_limit_offset = 0)
	{
		global $config, $db, $user;
		global $phpbb_dispatcher;

		$user_id = ($user_id === false) ? (int) $user->data['user_id'] : (int) $user_id;

		// Data array we're going to return
		$unread_topics = array();

		if (empty($sql_sort))
		{
			$sql_sort = 'ORDER BY t.topic_last_post_time DESC, t.topic_last_post_id DESC';
		}

		if ($config['load_db_lastread'] && $user->data['is_registered'])
		{
			// Get list of the unread topics
			$last_mark = (int) $user->data['user_lastmark'];

			$sql_array = array(
				'SELECT'		=> 't.topic_id, t.topic_last_post_time, tt.mark_time as topic_mark_time, ft.mark_time as forum_mark_time',

				'FROM'			=> array(TOPICS_TABLE => 't'),

				'LEFT_JOIN'		=> array(
					array(
						'FROM'	=> array(TOPICS_TRACK_TABLE => 'tt'),
						'ON'	=> "tt.user_id = $user_id AND t.topic_id = tt.topic_id",
					),
					array(
						'FROM'	=> array(FORUMS_TRACK_TABLE => 'ft'),
						'ON'	=> "ft.user_id = $user_id AND t.forum_id = ft.forum_id",
					),
					array(
						'FROM' 	=> array(TOPICS_POSTED_TABLE => 'tp'),
						'ON'   	=> "tp.user_id = $user_id AND t.topic_id = tp.topic_id",
					),
					array(
						'FROM' 	=> array(FORUMS_TABLE => 'f'),
						'ON'   	=> 'f.forum_id = t.forum_id',
					),
					array(
						'FROM' => array(USERS_TABLE => 'u'),
						'ON'   => 't.topic_poster = u.user_id'
					)
				),

				'WHERE'			=> "
					 t.topic_last_post_time > $last_mark AND
					(
					(tt.mark_time IS NOT NULL AND t.topic_last_post_time > tt.mark_time) OR
					(tt.mark_time IS NULL AND ft.mark_time IS NOT NULL AND t.topic_last_post_time > ft.mark_time) OR
					(tt.mark_time IS NULL AND ft.mark_time IS NULL)
					)
					$sql_extra
					$sql_sort",
			);

			/**
			 * Change SQL query for fetching unread topics data
			 *
			 * @event core.get_unread_topics_modify_sql
			 * @var array     sql_array    Fully assembled SQL query with keys SELECT, FROM, LEFT_JOIN, WHERE
			 * @var int       last_mark    User's last_mark time
			 * @var string    sql_extra    Extra WHERE SQL statement
			 * @var string    sql_sort     ORDER BY SQL sorting statement
			 * @since 3.1.4-RC1
			 */
			$vars = array(
				'sql_array',
				'last_mark',
				'sql_extra',
				'sql_sort',
			);
			extract($phpbb_dispatcher->trigger_event('core.get_unread_topics_modify_sql', compact($vars)));

			$sql = $db->sql_build_query('SELECT', $sql_array);
			$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

			while ($row = $db->sql_fetchrow($result))
			{
				$topic_id = (int) $row['topic_id'];
				$unread_topics[$topic_id] = ($row['topic_mark_time']) ? (int) $row['topic_mark_time'] : (($row['forum_mark_time']) ? (int) $row['forum_mark_time'] : $last_mark);
			}
			$db->sql_freeresult($result);
		}
		else if ($config['load_anon_lastread'] || $user->data['is_registered'])
		{
			global $tracking_topics;

			if (empty($tracking_topics))
			{
				$tracking_topics = request_var($config['cookie_name'] . '_track', '', false, true);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();
			}

			if (!$user->data['is_registered'])
			{
				$user_lastmark = (isset($tracking_topics['l'])) ? base_convert($tracking_topics['l'], 36, 10) + $config['board_startdate'] : 0;
			}
			else
			{
				$user_lastmark = (int) $user->data['user_lastmark'];
			}

			$sql = 'SELECT t.topic_id, t.forum_id, t.topic_last_post_time
				FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . TOPICS_POSTED_TABLE . ' tp
					ON tp.user_id = ' . $user_id . ' AND t.topic_id = tp.topic_id
				LEFT JOIN ' . FORUMS_TABLE . ' f
					ON f.forum_id = t.forum_id
				LEFT JOIN ' . USERS_TABLE . ' u
					ON t.topic_poster = u.user_id
				WHERE t.topic_last_post_time > ' . $user_lastmark . "
				$sql_extra
				$sql_sort";
			$result = $db->sql_query_limit($sql, $sql_limit, $sql_limit_offset);

			while ($row = $db->sql_fetchrow($result))
			{
				$forum_id = (int) $row['forum_id'];
				$topic_id = (int) $row['topic_id'];
				$topic_id36 = base_convert($topic_id, 10, 36);

				if (isset($tracking_topics['t'][$topic_id36]))
				{
					$last_read = base_convert($tracking_topics['t'][$topic_id36], 36, 10) + $config['board_startdate'];

					if ($row['topic_last_post_time'] > $last_read)
					{
						$unread_topics[$topic_id] = $last_read;
					}
				}
				else if (isset($tracking_topics['f'][$forum_id]))
				{
					$mark_time = base_convert($tracking_topics['f'][$forum_id], 36, 10) + $config['board_startdate'];

					if ($row['topic_last_post_time'] > $mark_time)
					{
						$unread_topics[$topic_id] = $mark_time;
					}
				}
				else
				{
					$unread_topics[$topic_id] = $user_lastmark;
				}
			}
			$db->sql_freeresult($result);
		}

		return $unread_topics;
	}

	protected function add_to_query($query, $condition, $additional_option)
	{
		if ($additional_option)
		{
			$query .= ' OR ';
		}
		$query .= $condition;

		return $query;
	}

	public function display_newsfeed($tpl_loopname = 'newsfeed', $spec_forum_id = 0, $include_subforums = true)
	{
		$this->user->add_lang_ext('nuleaf/newsfeed', 'newsfeed');

		/**
		 * Set some internal needed variables
		 */
		$topics_per_page = $this->config['nf_number'];
		$num_pages = $this->config['nf_page_number'];
		$display_parent_forums = $this->config['nf_parents'];
		$unread_only = $this->config['nf_unreadonly'];
		$display_topics = array(
			'in_announcements' 			=> (bool) $this->config['nf_display_topics_in_announcements'],
			'by_user' 					=> (bool) $this->config['nf_display_topics_by_user'],
			'responded_by_user' 		=> (bool) $this->config['nf_display_topics_responded_by_user'],
			'by_user_team' 				=> (bool) $this->config['nf_display_topics_by_user_team'],
			'in_user_most_active_forum' => (bool) $this->config['nf_display_topics_in_user_most_active_forum']
		);
		$persistent_display = $this->config['nf_persistent_display'];
		if ($persistent_display && $this->user->data['is_registered'])
		{
			$this->template->assign_vars(array(
				strtoupper($tpl_loopname) . '_DISPLAY' => true,
			));
		}

		if (!sizeof($display_topics))
		{
			// No options selected, no topics to display
			return;
		}

		$start = $this->request->variable($tpl_loopname . '_start', 0);
		$total_limit = $topics_per_page * $num_pages;

		if (!function_exists('display_forums'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->phpEx);
		}

		/**
		 * Get the forums we take our topics from
		 */
		// Get the allowed forums
		$forum_ary = array();
		$forum_read_ary = $this->auth->acl_getf('f_read');
		foreach ($forum_read_ary as $forum_id => $allowed)
		{
			if ($allowed['f_read'])
			{
				$forum_ary[] = (int)$forum_id;
			}
		}
		$forum_ids = array_unique($forum_ary);

		if (!sizeof($forum_ids))
		{
			// No forums with f_read
			return;
		}

		$spec_forum_ary = array();
		if ($spec_forum_id)
		{
			// Only take a special-forum
			if (!$include_subforums)
			{
				if (!in_array($spec_forum_id, $forum_ids))
				{
					return;
				}
				$forum_ids = array();
				$sql = 'SELECT 1 as display_forum
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . intval($spec_forum_id) . '
						AND forum_recent_topics = 1';
				$result = $this->db->sql_query_limit($sql, 1);
				$display_forum = (bool)$this->db->sql_fetchfield('display_forum');
				$this->db->sql_freeresult($result);

				if ($display_forum)
				{
					$forum_ids = array($spec_forum_id);
				}
			}
			else
			{
				// ... and it's subforums
				$sql = 'SELECT f2.forum_id
					FROM ' . FORUMS_TABLE . ' f1
					LEFT JOIN ' . FORUMS_TABLE . " f2
						ON (f2.left_id BETWEEN f1.left_id AND f1.right_id
							AND f2.forum_recent_topics = 1)
					WHERE f1.forum_id = $spec_forum_id
						AND f1.forum_recent_topics = 1
					ORDER BY f2.left_id DESC";
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$spec_forum_ary[] = $row['forum_id'];
				}
				$this->db->sql_freeresult($result);

				$forum_ids = array_intersect($forum_ids, $spec_forum_ary);

				if (!sizeof($forum_ids))
				{
					return;
				}
			}
		}
		else
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $this->db->sql_in_set('forum_id', $forum_ids) . '
					AND forum_recent_topics = 1';
			$result = $this->db->sql_query($sql);

			$forum_ids = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_ids[] = $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}

		// No forums with f_read
		if (!sizeof($forum_ids))
		{
			return;
		}

		// Remove duplicated ids
		$forum_ids = array_unique($forum_ids);

		$forums = $topic_list = array();
		$topics_count = 0;
		$obtain_icons = false;

		// Get the allowed topics
		$sql_array = array(
			'SELECT'    => 't.forum_id, t.topic_id, t.topic_type, t.icon_id, tt.mark_time, ft.mark_time as f_mark_time',
			'FROM'      => array(TOPICS_TABLE => 't'),
			'LEFT_JOIN' => array(
				array(
					'FROM' => array(TOPICS_TRACK_TABLE => 'tt'),
					'ON'   => 'tt.topic_id = t.topic_id AND tt.user_id = ' . $this->user->data['user_id'],
				),
				array(
					'FROM' => array(FORUMS_TRACK_TABLE => 'ft'),
					'ON'   => 'ft.forum_id = t.forum_id AND ft.user_id = ' . $this->user->data['user_id'],
				),
				array(
					'FROM' => array(TOPICS_POSTED_TABLE => 'tp'),
					'ON'   => 't.topic_id = tp.topic_id AND tp.user_id = ' . $this->user->data['user_id'],
				),
				array(
					'FROM' => array(FORUMS_TABLE => 'f'),
					'ON'   => 'f.forum_id = t.forum_id',
				)
			),
			'WHERE'     => 't.topic_status <> ' . ITEM_MOVED . '
				AND ' . $this->content_visibility->get_forums_visibility_sql('topic', $forum_ids, $table_alias = 't.'),
			'ORDER_BY'  => 't.topic_last_post_time DESC'
		);

		// Build $sql_extra along with $sql_array for unread_only case
		$sql_extra = ' AND (' . $this->content_visibility->get_forums_visibility_sql('topic', $forum_ids, $table_alias = 't.');

		// Add topic to display options to query
		$condition = '';
		$additional_option = false;
		if (sizeof($display_topics))
		{
			$sql_extra 			.= ' AND (';
			$sql_array['WHERE'] .= ' AND (';
		}

		/**
		* Topics the user has made
		*/
		if ($display_topics['by_user'])
		{
			$condition 			= 't.topic_poster = ' . $this->user->data['user_id'];
			$sql_extra 			= $this->add_to_query($sql_extra, $condition, $additional_option);
			$sql_array['WHERE'] = $this->add_to_query($sql_array['WHERE'], $condition, $additional_option);
			$additional_option = true;
		}


		/**
		* Topics the user has responded to
		*/
		if ($display_topics['responded_by_user'])
		{
			$condition = 'tp.user_id = ' . $this->user->data['user_id'];
			$sql_extra 			= $this->add_to_query($sql_extra, $condition, $additional_option);
			$sql_array['WHERE'] = $this->add_to_query($sql_array['WHERE'], $condition, $additional_option);
			$additional_option = true;
		}

		/**
		* Topics made by the user's team
		*/
		if ($display_topics['by_user_team'])
		{
			$sql_array['LEFT_JOIN'][] = array(
				'FROM' => array(USERS_TABLE => 'u'),
				'ON'   => 't.topic_poster = u.user_id'
			);
			$condition = 'u.user_rank = ' . $this->user->data['user_rank'];
			$sql_extra 			= $this->add_to_query($sql_extra, $condition, $additional_option);
			$sql_array['WHERE'] = $this->add_to_query($sql_array['WHERE'], $condition, $additional_option);
			$additional_option = true;
		}

		/**
		* Topics in forum where user is most active
		*/
		if ($display_topics['in_user_most_active_forum'])
		{
			// Grab forum_id of forum user most active in
			$sql = 'SELECT f.forum_id, COUNT(*) AS t_counts
				FROM ' . TOPICS_TABLE . ' t
				LEFT JOIN ' . POSTS_TABLE . ' p
					ON t.topic_id = p.topic_id
				LEFT JOIN ' . FORUMS_TABLE . ' f
			 		ON t.forum_id = f.forum_id
				WHERE p.poster_id = ' . $this->user->data['user_id'] . '
				GROUP BY f.forum_id
				ORDER BY t_counts DESC';

			$result = $this->db->sql_query_limit($sql, 1);
			$user_most_active_forum_id = $this->db->sql_fetchrow($result)['forum_id'];
			if (!$user_most_active_forum_id) {
				$user_most_active_forum_id = -1;
			}
			$this->db->sql_freeresult($result);

			// Grab topics in forum where user is most active
			$condition = 'f.forum_id = ' . $user_most_active_forum_id;
			$sql_extra 			= $this->add_to_query($sql_extra, $condition, $additional_option);
			$sql_array['WHERE'] = $this->add_to_query($sql_array['WHERE'], $condition, $additional_option);
			$additional_option = true;
		}


		/**
		* Topics in the Announcements forum
		*/
		if ($display_topics['in_announcements'])
		{
			$condition = 't.forum_id = 4';
			$sql_extra 			= $this->add_to_query($sql_extra, $condition, $additional_option);
			$sql_array['WHERE'] = $this->add_to_query($sql_array['WHERE'], $condition, $additional_option);
			$additional_option = true;
		}

		if (sizeof($display_topics))
		{
			$sql_extra 			.= ')';
			$sql_array['WHERE'] .= ')';
		}
		$sql_extra .= ')';

		// Either use the phpBB core function to get unread topics, or the custom function for default behavior
		if ($unread_only && $this->user->data['user_id'] != ANONYMOUS)
		{
			// Get unread topics
			$unread_topics = $this->get_unread_topics(false, $sql_extra, '', $total_limit);

			foreach ($unread_topics as $topic_id => $mark_time)
			{
				$topics_count++;
				if (($topics_count > $start) && ($topics_count <= ($start + $topics_per_page)))
				{
					$topic_list[] = $topic_id;
				}
			}
		}
		else
		{
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, $total_limit);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$topics_count++;
				if (($topics_count > $start) && ($topics_count <= ($start + $topics_per_page)))
				{
					$topic_list[] = $row['topic_id'];

					$rowset[$row['topic_id']] = $row;
					if (!isset($forums[$row['forum_id']]) && $this->user->data['is_registered'] && $this->config['load_db_lastread'])
					{
						$forums[$row['forum_id']]['mark_time'] = $row['f_mark_time'];
					}
					$forums[$row['forum_id']]['topic_list'][] = $row['topic_id'];
					$forums[$row['forum_id']]['rowset'][$row['topic_id']] = & $rowset[$row['topic_id']];

					if ($row['icon_id'] && $this->auth->acl_get('f_icons', $row['forum_id']))
					{
						$obtain_icons = true;
					}
				}
			}
			$this->db->sql_freeresult($result);
		}

		// No topics to display
		if (!sizeof($topic_list))
		{
			return;
		}

		// Grab icons
		if ($obtain_icons)
		{
			$icons = $this->cache->obtain_icons();
		}
		else
		{
			$icons = array();
		}

		// Borrowed from search.php
		foreach ($forums as $forum_id => $forum)
		{
			if ($this->user->data['is_registered'] && $this->config['load_db_lastread'])
			{
				$topic_tracking_info[$forum_id] = get_topic_tracking($forum_id, $forum['topic_list'], $forum['rowset'], array($forum_id => $forum['mark_time']), ($forum_id) ? false : $forum['topic_list']);
			}
			else if ($this->config['load_anon_lastread'] || $this->user->data['is_registered'])
			{
				$tracking_topics = $this->request->variable($this->config['cookie_name'] . '_track', '', true, \phpbb\request\request_interface::COOKIE);
				$tracking_topics = ($tracking_topics) ? tracking_unserialize($tracking_topics) : array();

				$topic_tracking_info[$forum_id] = get_complete_topic_tracking($forum_id, $forum['topic_list'], ($forum_id) ? false : $forum['topic_list']);

				if (!$this->user->data['is_registered'])
				{
					$this->user->data['user_lastmark'] = (isset($tracking_topics['l'])) ? (int)(base_convert($tracking_topics['l'], 36, 10) + $this->config['board_startdate']) : 0;
				}
			}
		}

		// Now only pull the data of the requested topics
		$sql_array = array(
			'SELECT'    => 't.*, tp.topic_posted, f.forum_name',
			'FROM'      => array(TOPICS_TABLE => 't'),
			'LEFT_JOIN' => array(
				array(
					'FROM' => array(TOPICS_POSTED_TABLE => 'tp'),
					'ON'   => 't.topic_id = tp.topic_id AND tp.user_id = ' . $this->user->data['user_id'],
				),
				array(
					'FROM' => array(FORUMS_TABLE => 'f'),
					'ON'   => 'f.forum_id = t.forum_id',
				),
			),
			'WHERE'     => $this->db->sql_in_set('t.topic_id', $topic_list),
			'ORDER_BY'  => 't.topic_last_post_time DESC',
		);

		if ($display_parent_forums)
		{
			$sql_array['SELECT'] .= ', f.parent_id, f.forum_parents, f.left_id, f.right_id';
		}

		/**
		* Event to modify the SQL query before the topics data is retrieved
		*
		* @event nuleaf.newsfeed.sql_pull_topics_data
		* @var    array    sql_array        The SQL array
		*/
		$vars = array('sql_array');
		extract($this->dispatcher->trigger_event('nuleaf.newsfeed.sql_pull_topics_data', compact($vars)));

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $topics_per_page);

		$rowset = $topic_icons = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[] = $row;
		}
		$this->db->sql_freeresult($result);

		// No topics returned by the DB
		if (!sizeof($rowset))
		{
			return;
		}

		foreach ($rowset as $row)
		{
			$topic_id = $row['topic_id'];
			$forum_id = $row['forum_id'];

			$s_type_switch_test = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;
			$replies = $this->content_visibility->get_count('topic_posts', $row, $forum_id) - 1;

			if ($unread_only)
			{
				topic_status($row, $replies, true, $folder_img, $folder_alt, $topic_type);
				$unread_topic = true;
			}
			else
			{
				topic_status($row, $replies, (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false, $folder_img, $folder_alt, $topic_type);
				$unread_topic = (isset($topic_tracking_info[$forum_id][$row['topic_id']]) && $row['topic_last_post_time'] > $topic_tracking_info[$forum_id][$row['topic_id']]) ? true : false;
			}

			$view_topic_url = append_sid("{$this->root_path}viewtopic.$this->phpEx", 'f=' . $forum_id . '&amp;t=' . $topic_id);
			$view_forum_url = append_sid("{$this->root_path}viewforum.$this->phpEx", 'f=' . $forum_id);

			$topic_unapproved = ($row['topic_visibility'] == ITEM_UNAPPROVED && $this->auth->acl_get('m_approve', $forum_id));
			$posts_unapproved = ($row['topic_visibility'] == ITEM_APPROVED && $row['topic_posts_unapproved'] && $this->auth->acl_get('m_approve', $forum_id));

			$u_mcp_queue = ($topic_unapproved || $posts_unapproved) ? append_sid("{$this->root_path}mcp.$this->phpEx", 'i=queue&amp;mode=' . (($topic_unapproved) ? 'approve_details' : 'unapproved_posts') . "&amp;t=$topic_id", true, $this->user->session_id) : '';
			$s_type_switch = ($row['topic_type'] == POST_ANNOUNCE || $row['topic_type'] == POST_GLOBAL) ? 1 : 0;

			if (!empty($icons[$row['icon_id']]))
			{
				$topic_icons[] = $topic_id;
			}

			// Get folder img, topic status/type related information
			$folder_img = $folder_alt = $topic_type = '';
			topic_status($row, $replies, $unread_topic, $folder_img, $folder_alt, $topic_type);

			$tpl_ary = array(
				'FORUM_ID'                => $forum_id,
				'TOPIC_ID'                => $topic_id,
				'TOPIC_AUTHOR'            => get_username_string('username', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'TOPIC_AUTHOR_COLOUR'     => get_username_string('colour', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'TOPIC_AUTHOR_FULL'       => get_username_string('full', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'FIRST_POST_TIME'         => $this->user->format_date($row['topic_time']),

				'LAST_POST_SUBJECT'       => censor_text($row['topic_last_post_subject']),
				'LAST_POST_TIME'          => $this->user->format_date($row['topic_last_post_time']),
				'LAST_VIEW_TIME'          => $this->user->format_date($row['topic_last_view_time']),
				'LAST_POST_AUTHOR'        => get_username_string('username', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_COLOUR' => get_username_string('colour', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'LAST_POST_AUTHOR_FULL'   => get_username_string('full', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),

				'REPLIES'                 => $replies,
				'VIEWS'                   => $row['topic_views'],
				'TOPIC_TITLE'             => censor_text($row['topic_title']),
				'FORUM_NAME'              => $row['forum_name'],

				'TOPIC_TYPE'              => $topic_type,
				'TOPIC_IMG_STYLE'         => $folder_img,
				'TOPIC_FOLDER_IMG'        => $this->user->img($folder_img, $folder_alt),
				'TOPIC_FOLDER_IMG_ALT'    => $this->user->lang[$folder_alt],

				//'NEWEST_POST_IMG'		=> $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'), // dupe?
				'TOPIC_ICON_IMG'          => (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['img'] : '',
				'TOPIC_ICON_IMG_WIDTH'    => (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['width'] : '',
				'TOPIC_ICON_IMG_HEIGHT'   => (!empty($icons[$row['icon_id']])) ? $icons[$row['icon_id']]['height'] : '',
				'ATTACH_ICON_IMG'         => ($this->auth->acl_get('u_download') && $this->auth->acl_get('f_download', $forum_id) && $row['topic_attachment']) ? $this->user->img('icon_topic_attach', $this->user->lang['TOTAL_ATTACHMENTS']) : '',
				'UNAPPROVED_IMG'          => ($topic_unapproved || $posts_unapproved) ? $this->user->img('icon_topic_unapproved', ($topic_unapproved) ? 'TOPIC_UNAPPROVED' : 'POSTS_UNAPPROVED') : '',
				'REPORTED_IMG'            => ($row['topic_reported'] && $this->auth->acl_get('m_report', $forum_id)) ? $this->user->img('icon_topic_reported', 'TOPIC_REPORTED') : '',
				'S_HAS_POLL'              => ($row['poll_start']) ? true : false,

				'S_TOPIC_TYPE'            => $row['topic_type'],
				'S_USER_POSTED'           => (isset($row['topic_posted']) && $row['topic_posted']) ? true : false,
				'S_UNREAD_TOPIC'          => $unread_topic,
				'S_TOPIC_REPORTED'        => ($row['topic_reported'] && $this->auth->acl_get('m_report', $forum_id)) ? true : false,
				'S_TOPIC_UNAPPROVED'      => $topic_unapproved,
				'S_POSTS_UNAPPROVED'      => $posts_unapproved,
				'S_POST_ANNOUNCE'         => ($row['topic_type'] == POST_ANNOUNCE) ? true : false,
				'S_POST_GLOBAL'           => ($row['topic_type'] == POST_GLOBAL) ? true : false,
				'S_POST_STICKY'           => ($row['topic_type'] == POST_STICKY) ? true : false,
				'S_TOPIC_LOCKED'          => ($row['topic_status'] == ITEM_LOCKED) ? true : false,
				'S_TOPIC_MOVED'           => ($row['topic_status'] == ITEM_MOVED) ? true : false,
				'S_TOPIC_TYPE_SWITCH'     => ($s_type_switch == $s_type_switch_test) ? -1 : $s_type_switch_test,

				'U_NEWEST_POST'           => $view_topic_url . '&amp;view=unread#unread',
				'U_LAST_POST'             => $view_topic_url . '&amp;p=' . $row['topic_last_post_id'] . '#p' . $row['topic_last_post_id'],
				'U_LAST_POST_AUTHOR'      => get_username_string('profile', $row['topic_last_poster_id'], $row['topic_last_poster_name'], $row['topic_last_poster_colour']),
				'U_TOPIC_AUTHOR'          => get_username_string('profile', $row['topic_poster'], $row['topic_first_poster_name'], $row['topic_first_poster_colour']),
				'U_VIEW_TOPIC'            => $view_topic_url,
				'U_VIEW_FORUM'            => $view_forum_url,
				'U_MCP_REPORT'            => append_sid("{$this->root_path}mcp.$this->phpEx", 'i=reports&amp;mode=reports&amp;f=' . $forum_id . '&amp;t=' . $topic_id, true, $this->user->session_id),
				'U_MCP_QUEUE'             => $u_mcp_queue,
			);

			$this->template->assign_block_vars($tpl_loopname, $tpl_ary);

			$this->pagination->generate_template_pagination($view_topic_url, $tpl_loopname . '.pagination', 'start', $replies + 1, $this->config['posts_per_page'], 1, true, true);

			if ($display_parent_forums)
			{
				$forum_parents = get_forum_parents($row);

				foreach ($forum_parents as $parent_id => $data)
				{
					$this->template->assign_block_vars($tpl_loopname . '.parent_forums', array(
						'FORUM_ID'     => $parent_id,
						'FORUM_NAME'   => $data[0],
						'U_VIEW_FORUM' => append_sid("{$this->root_path}viewforum.$this->phpEx", 'f=' . $parent_id),
					));
				}
			}
		}

		// Get URL-parameters for pagination
		$url_params = explode('&', $this->user->page['query_string']);
		$append_params = false;
		foreach ($url_params as $param)
		{
			if (!$param)
			{
				continue;
			}
			if (strpos($param, '=') === false)
			{
				// Fix MSSTI Advanced BBCode MOD
				$append_params[$param] = '1';
				continue;
			}
			list($name, $value) = explode('=', $param);
			if ($name != $tpl_loopname . '_start')
			{
				$append_params[$name] = $value;
			}
		}

		$pagination_url = append_sid($this->root_path . $this->user->page['page_name'], $append_params);
		$this->pagination->generate_template_pagination($pagination_url, 'nf_pagination', $tpl_loopname . '_start', $topics_count, $topics_per_page, $start);

		$this->template->assign_vars(array(
			'S_TOPIC_ICONS'                        => (sizeof($topic_icons)) ? true : false,
			'NEWEST_POST_IMG'                      => $this->user->img('icon_topic_newest', 'VIEW_NEWEST_POST'),
			'LAST_POST_IMG'                        => $this->user->img('icon_topic_latest', 'VIEW_LATEST_POST'),
			'POLL_IMG'                             => $this->user->img('icon_topic_poll', 'TOPIC_POLL'),
			strtoupper($tpl_loopname) . '_DISPLAY' => true,
		));
	}
}
