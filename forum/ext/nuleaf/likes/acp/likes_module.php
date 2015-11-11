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

namespace nuleaf\likes\acp;

class likes_module
{
  public $u_action;

  function main($id, $mode)
  {
    global $config, $request, $template, $user;

    $user->add_lang('common');
    $this->tpl_name = 'acp_likes';
    $this->page_title = $user->lang('ACP_LIKES');

    $form_key = 'acp_likes';
    add_form_key($form_key);

    if ($request->is_set_post('submit'))
    {
      if (!check_form_key($form_key))
      {
        trigger_error('FORM_INVALID');
      }

      $config->set('likes_on', $request->variable('likes_on', true));

      trigger_error($user->lang('ACP_LIKES_SETTINGS_SAVED') . adm_back_link($this->u_action));
    }

    $template->assign_vars(array(
      'U_ACTION' => $this->u_action,
      
      'LIKES_ON' => isset($config['likes_on']) ? $config['likes_on'] : false
    ));
  }
}
