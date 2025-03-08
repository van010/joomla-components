<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldK2orderfield extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  11.1
	 */
	protected $type = 'K2orderfield';

	protected function getOptions()
	{
		require_once(JPATH_ROOT.'/modules/mod_jak2filter/helper.php');

		$options = jaK2GetOrderFields();
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
}
