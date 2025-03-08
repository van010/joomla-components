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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;

defined('_JEXEC') or die;

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_k2/models/categories.php')) {
	//Register if K2 is installed
	JalangHelperContent::registerAdapter(
		__FILE__,
		'k2_categories',
		3,
		Text::_('K2_CATEGORIES'),
		Text::_('K2_CATEGORIES')
	);

	//require_once( JPATH_ADMINISTRATOR . '/components/com_k2/models/categories.php' );

	class JalangHelperContentK2Categories extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table = 'k2_categories';
			$this->edit_context = 'com_k2.edit.category';
			$this->associate_context = 'com_k2.category';
			$this->translate_fields = array('name', 'description');
			$this->translate_filters = array('trash <> 1');
			$this->alias_field = 'alias';
			$this->nested_field = 'parent';
			$this->nested_value = 0;
			$this->title_field = 'name';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_k2&view=category&cid='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.name' => Text::_('JGLOBAL_TITLE'),
				'a.access' => Text::_('JGRID_HEADING_ACCESS'),
				'language' => Text::_('JGRID_HEADING_LANGUAGE'),
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
				'a.name' => 'JGLOBAL_TITLE'
			);
		}
	}
}
