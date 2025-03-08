<?php
/**
 * ------------------------------------------------------------------------
 * JA Multilingual J2x-J3x.
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class JalangControllerItem extends FormController
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Guess the option as com_NameOfController
		if (empty($this->option))
		{
			$this->option = 'com_' . strtolower($this->getName());
		}
	}

	public function edit($key = null, $urlVar = null) {
		$input = Factory::getApplication()->input;
		$id = $input->getInt('id');
		$refid = $input->get('refid');

		$adapter = JalangHelper::getHelperContent();
		if($adapter) {
			$linkEdit = $adapter->getEditLink($id);
			if(!$linkEdit) {
				Factory::getApplication()->enqueueMessage($adapter->getError(), 'warning');
				$this->setRedirect(Route::_('index.php?option=com_jalang&view=items', false));
				return false;
			}

			/*$return = Route::_('index.php?option=com_jalang&view=items', false);
			$return = urlencode(base64_encode($return));*/

			$linkEdit = Route::_($linkEdit.'&jaref='.$adapter->table.'.'.$refid, false);

			if($adapter->edit_context) {
				$app = Factory::getApplication();

				$this->holdEditId($adapter->edit_context, $id);
				$app->setUserState($adapter->edit_context . '.data', null);
			}
			$this->setRedirect($linkEdit);
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('INVALID_REQUEST'), 'error');
		}
	}
}