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
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;


/**
 * Component Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.5
 */
class JalangController extends BaseController
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'tool';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$view   = $input->get('view', 'items');
		$layout = $input->get('layout', 'default');
		$id     = $input->getInt('id');

		// Check for edit form.
		if ($view == 'article' && $layout == 'edit' && !$this->checkEditId('com_content.edit.article', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(Route::_('index.php?option=com_content&view=articles', false));

			return false;
		}

		parent::display();

		return $this;
	}
}
