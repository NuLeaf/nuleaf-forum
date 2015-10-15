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

namespace nuleaf\newsfeed\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'forum_newsfeed' => array('TINT:1', 1),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums' => array(
					'forum_newsfeed',
				),
			),
		);
	}

	public function update_data()
	{
		return array(
			// Add new config vars
			array('config.add', array('nf_version', '1.0.0')),
			array('config.add', array('nf_number', 5)),
			array('config.add', array('nf_page_number', 0)),
			array('config.add', array('nf_parents', 1)),
			array('config.add', array('nf_unreadonly', 0)),
			array('config.add', array('nf_persistent_display', false)),
			array('config.add', array('nf_display_topics_in_announcements', 1)),
			array('config.add', array('nf_display_topics_by_user', 0)),
			array('config.add', array('nf_display_topics_responded_by_user', 0)),
			array('config.add', array('nf_display_topics_by_user_team', 0)),
			array('config.add', array('nf_display_topics_in_user_most_active_forum', 0)),
			array('config.add', array('nf_index', 1)),

			// Add new modules
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'NEWSFEED'
			)),

			array('module.add', array(
				'acp',
				'NEWSFEED',
				array(
					'module_basename'	=> '\nuleaf\newsfeed\acp\newsfeed_module',
					'modes'	=> array('newsfeed_config'),
				),
			)),
		);
	}

	public function revenf_data()
	{
		return array(
			array('config.remove', array('nf_version')),
			array('config.remove', array('nf_number')),
			array('config.remove', array('nf_page_number')),
			array('config.remove', array('nf_parents')),
			array('config.remove', array('nf_unreadonly')),
			array('config.remove', array('nf_persistent_display')),
			array('config.remove', array('nf_display_topics_in_announcements')),
			array('config.remove', array('nf_display_topics_by_user')),
			array('config.remove', array('nf_display_topics_responded_by_user')),
			array('config.remove', array('nf_display_topics_by_user_team')),
			array('config.remove', array('nf_display_topics_in_user_most_active_forum')),
			array('config.remove', array('nf_index')),

			array('module.remove', array(
				'acp',
				'NEWSFEED',
				array(
					'module_basename'	=> '\nuleaf\newsfeed\acp\newsfeed_module',
					'modes'	=> array('newsfeed_config'),
				),
			)),
			array('module.remove', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'NEWSFEED'
			)),
		);
	}
}
