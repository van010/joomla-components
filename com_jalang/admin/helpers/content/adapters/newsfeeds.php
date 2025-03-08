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

defined('_JEXEC') or die;

JalangHelperContent::registerAdapter(
	__FILE__,
	'newsfeeds',
	2,
	Text::_('NEWS_FEEDS'),
	Text::_('NEWS_FEEDS')
);

class JalangHelperContentNewsfeeds extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table = 'newsfeeds';
		$this->edit_context = 'com_newsfeeds.edit.newsfeed';
		$this->associate_context = 'com_newsfeeds.item';
		$this->alias_field = 'alias';
		$this->translate_fields = array('name', 'metakey', 'metadesc');
		$this->reference_fields = array('catid'=>'categories');
		$this->title_field = 'name';
		$this->fixed_fields = array();
		parent::__construct($config);
	}

	public function getEditLink($id) {
		if($this->checkout($id)) {
			if(JalangHelper::isJoomla32()) {
				return 'index.php?option=com_newsfeeds&view=newsfeed&layout=modal&id='.$id;
			} else {
				return 'index.php?option=com_newsfeeds&view=newsfeed&layout=edit&id='.$id;
			}
		}
		return false;
	}
	
	/**
	 * Returns an array of fields the table can be sorted by
	 */
	public function getSortFields()
	{
		return array(
			'a.ordering' => Text::_('JGRID_HEADING_ORDERING'),
			'a.name' => Text::_('JGLOBAL_TITLE')
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