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
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

JalangHelperContent::registerAdapter(
	__FILE__,
	'menu_types',
	9,
	Text::_('MENU_TYPES'),
	Text::_('MENU_TYPES')
);

class JalangHelperContentMenuTypes extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table_type = 'alias';
		$this->table = 'menu_types';
		$this->alias_field = 'menutype';
		$this->edit_context = 'com_menus.edit.menu';
		$this->associate_context = 'menu_types.item';
		$this->translate_filters = array("menutype <> 'default-all'");
		$this->translate_fields = array('title', 'description');
		parent::__construct($config);
	}

	public function getEditLink($id) {
		return 'index.php?option=com_menus&view=menu&layout=edit&id='.$id;
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 */
	public function getSortFields()
	{
		return array(
			'a.title' => Text::_('JGLOBAL_TITLE'),
			'a.menutype' => Text::_('MENU_TYPE'),
			'a.id' => Text::_('JGRID_HEADING_ID')
		);
	}
	
	/**
	 * Returns an array of fields will be displayed in the table list
	 */
	public function getDisplayFields()
	{
		return array(
			'a.id' => 'JGRID_HEADING_ID',
			'a.title' => 'JGLOBAL_TITLE'
		);
	}

	public function beforeTranslate(&$translator) {
		//Fix bug: menutype's lenght is limited 24 characters and make duplicated error
		$db = Factory::getDbo();
		$query = "ALTER TABLE #__{$this->table} MODIFY menutype  varchar(100) NOT NULL";

		$db->setQuery($query);
		$db->execute();
	}
}