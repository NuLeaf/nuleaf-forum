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

namespace nuleaf\newsfeed\acp;

class newsfeed_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $config, $phpbb_extension_manager, $request, $template, $user;

		$user->add_lang('acp/common');
		$this->tpl_name = 'acp_newsfeed';
		$this->page_title = $user->lang('NEWSFEED');

		$form_key = 'acp_newsfeed';
		add_form_key($form_key);

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			// variable should be '' as it is a string ("1, 2, 3928") here, not an integer.
			$nf_number = $request->variable('nf_number', 5);
			$config->set('nf_number', $nf_number);

			$nf_page_number = $request->variable('nf_page_number', 0);
			$config->set('nf_page_number', $nf_page_number);

			$nf_parents = $request->variable('nf_parents', false);
			$config->set('nf_parents', $nf_parents);

			$nf_unreadonly = $request->variable('nf_unreadonly', false);
			$config->set('nf_unreadonly', $nf_unreadonly);

			$array = $request->variable('nf_topics_to_display', array(''));
			foreach ($array as $option)
			{
				$nf_topics_to_display[$option] = true;
			}
			if($nf_topics_to_display)
				extract($nf_topics_to_display);
			if($nf_topics_in_announcements) 		 $config->set('nf_display_topics_in_announcements', $nf_topics_in_announcements);
			else 									 $config->set('nf_display_topics_in_announcements', 0);
			if($nf_topics_by_user)   				 $config->set('nf_display_topics_by_user', $nf_topics_by_user);
			else 									 $config->set('nf_display_topics_by_user', 0);
			if($nf_topics_responded_by_user) 	     $config->set('nf_display_topics_responded_by_user', $nf_topics_responded_by_user);
			else 									 $config->set('nf_display_topics_responded_by_user', 0);
			if($nf_topics_by_user_team) 	         $config->set('nf_display_topics_by_user_team', $nf_topics_by_user_team);
			else 									 $config->set('nf_display_topics_by_user_team', 0);
			if($nf_topics_in_user_most_active_forum) $config->set('nf_display_topics_in_user_most_active_forum', $nf_topics_in_user_most_active_forum);
			else 									 $config->set('nf_display_topics_in_user_most_active_forum', 0);

			$nf_persistent_display = $request->variable('nf_persistent_display', false);
			$config->set('nf_persistent_display', $nf_persistent_display);

			$nf_index = $request->variable('nf_index', 0);
			$config->set('nf_index', $nf_index);

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'NF_NUMBER'             => isset($config['nf_number']) ? $config['nf_number'] : '',
			'NF_PAGE_NUMBER'        => isset($config['nf_page_number']) ? $config['nf_page_number'] : '',
			'NF_PARENTS'            => isset($config['nf_parents']) ? $config['nf_parents'] : false,
			'NF_UNREADONLY'         => isset($config['nf_unreadonly']) ? $config['nf_unreadonly'] : false,
			'NF_PERSISTENT_DISPLAY' => isset($config['nf_persistent_display']) ? $config['nf_persistent_display'] : false,

			'NF_TOPICS_IN_ANNOUNCEMENTS' 		  => isset($config['nf_display_topics_in_announcements']) ? $config['nf_display_topics_in_announcements'] : false,
			'NF_TOPICS_BY_USER' 				  => isset($config['nf_display_topics_by_user']) ? $config['nf_display_topics_by_user'] : false,
			'NF_TOPICS_RESPONDED_BY_USER' 		  => isset($config['nf_display_topics_responded_by_user']) ? $config['nf_display_topics_responded_by_user'] : false,
			'NF_TOPICS_BY_USER_TEAM' 			  => isset($config['nf_display_topics_by_user_team']) ? $config['nf_display_topics_by_user_team'] : false,
			'NF_TOPICS_IN_USER_MOST_ACTIVE_FORUM' => isset($config['nf_display_topics_in_user_most_active_forum']) ? $config['nf_display_topics_in_user_most_active_forum'] : false,

			'NF_INDEX'           => isset($config['nf_index']) ? $config['nf_index'] : false,

			'U_ACTION'           => $this->u_action,
		));
	}
}

