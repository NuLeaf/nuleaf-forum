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

namespace nuleaf\newsfeed\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/* @var \nuleaf\newsfeed\core\newsfeed */
	protected $nf_functions;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\request\request */
	protected $request;

	public function __construct(\nuleaf\newsfeed\core\newsfeed $functions, \phpbb\config\config $config, \phpbb\request\request $request)
	{
		$this->nf_functions = $functions;
		$this->config = $config;
		$this->request = $request;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.index_modify_page_title'           => 'display_nf',

			'core.acp_manage_forums_request_data'    => 'acp_manage_forums_request_data',
			'core.acp_manage_forums_initialise_data' => 'acp_manage_forums_initialise_data',
			'core.acp_manage_forums_display_form'    => 'acp_manage_forums_display_form',
		);
	}

	// The main magic
	public function display_nf()
	{
		if (isset($this->config['nf_index']) && $this->config['nf_index'])
		$this->nf_functions->display_newsfeed();
	}

	// Submit form (add/update)
	public function acp_manage_forums_request_data($event)
	{
		$array = $event['forum_data'];
		$array['forum_newsfeed'] = $this->request->variable('forum_newsfeed', 1);
		$event['forum_data'] = $array;
	}

	// Default settings for new forums
	public function acp_manage_forums_initialise_data($event)
	{
		if ($event['action'] == 'add')
		{
			$array = $event['forum_data'];
			$array['forum_newsfeed'] = '1';
			$event['forum_data'] = $array;
		}
	}

	// ACP forums template output
	public function acp_manage_forums_display_form($event)
	{
		$array = $event['template_data'];
		$array['NEWSFEED'] = $event['forum_data']['forum_newsfeed'];
		$event['template_data'] = $array;
	}
}
