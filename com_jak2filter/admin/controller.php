<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die;
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
jimport('joomla.application.component.controller');

/**
 * Search master display controller.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_jak2filter
 * @since		2.5
 */
class Jak2filterController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 */
	protected $default_view = 'jak2filter';

	/**
	 * Method to display a view.
	 *
	 */
	public function display($cachable = false, $urlparams = false)
	{

		parent::display();
	}
}
