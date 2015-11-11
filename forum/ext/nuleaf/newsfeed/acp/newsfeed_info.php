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

class newsfeed_info
{
	function module()
	{
		return array(
			'filename'	=> '\nuleaf\newsfeed\newsfeed_module',
			'title'		=> 'NEWSFEED',
			'modes'		=> array(
				'newsfeed_config' => array('title' => 'NF_CONFIG', 'auth' => 'ext_nuleaf/newsfeed && acl_a_board', 'cat' => array('NEWSFEED')),
			),
		);
	}
}
