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

if(File::exists(JPATH_ADMINISTRATOR . '/components/com_easyblog/models/categories.php')) {
	//Register if Easy Blog is installed
	/**
	 * @TO_DO category table of Easy blog does not support multilingual now,
	 * below code will be enabled after EB adding language filed for category table
	 */
	/*JalangHelperContent::registerAdapter(
		__FILE__,
		'easyblog_category',
		3,
		Text::_('EASYBLOG_CATEGORIES'),
		Text::_('EASYBLOG_CATEGORIES')
	);*/

	//require_once( JPATH_ADMINISTRATOR . '/components/com_easyblog/models/categories.php' );

	class JalangHelperContentEasyblogCategory extends JalangHelperContent
	{
		public function __construct($config = array())
		{
			$this->table = 'easyblog_category';
			$this->edit_context = 'com_easyblog.edit.category';
			$this->associate_context = 'com_easyblog.category';
			$this->translate_fields = array('title', 'description');
			//$this->translate_filters = array('trash <> 1');
			$this->alias_field = 'alias';
			$this->nested_field = 'parent_id';
			$this->nested_value = 0;
			$this->title_field = 'title';
			parent::__construct($config);
		}

		public function getEditLink($id) {
			return 'index.php?option=com_easyblog&c=category&task=edit&catid='.$id;
		}

		/**
		 * Returns an array of fields the table can be sorted by
		 */
		public function getSortFields()
		{
			return array(
				'a.title' => Text::_('JGLOBAL_TITLE'),
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
