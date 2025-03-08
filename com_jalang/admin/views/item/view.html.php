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
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die;

/**
 * View class for a list of articles.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_jalang
 * @since       1.6
 */

if (!class_exists('ViewLegacy')) {
	if (version_compare(JVERSION, 4, 'ge')) {
		class ViewLegacy extends HtmlView{}
	} else {
		class ViewLegacy extends JViewLegacy{}
	}
}

class JalangViewItem extends ViewLegacy
{

	/**
	 * Display the view
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$this->item		= $this->get('Item');

		$adapter = JalangHelper::getHelperContent();
		if($adapter) {
			$this->primarykey = $adapter->primarykey;
			$this->alias_field = $adapter->alias_field;
			$this->translate_fields = $adapter->translate_fields;
			$this->reference_fields = $adapter->reference_fields;
		} else {
			$this->primarykey = null;
			$this->alias_field = null;
			$this->translate_fields = array();
			$this->reference_fields = array();
		}

		parent::display($tpl);
	}
}
