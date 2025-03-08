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

use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

if(version_compare(JVERSION, '3.4.0', 'lt')) {
	JalangHelperContent::registerAdapter(
		__FILE__,
		'weblinks',
		2,
		Text::_('WEB_LINKS'),
		Text::_('WEB_LINKS')
	);
}

class JalangHelperContentWeblinks extends JalangHelperContent
{
	public function __construct($config = array())
	{
		$this->table = 'weblinks';
		$this->edit_context = 'com_weblinks.edit.weblink';
		$this->associate_context = 'com_weblinks.item';
		$this->alias_field = 'alias';
		$this->translate_fields = array('title', 'description', 'metakey', 'metadesc');
		$this->reference_fields = array('catid'=>'categories');
		$this->title_field = 'title';
		$this->fixed_fields = array();
		parent::__construct($config);
	}

	public function getEditLink($id) {
		if($this->checkout($id)) {
			return 'index.php?option=com_weblinks&view=weblink&layout=edit&id='.$id;
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
			'a.title' => Text::_('JGLOBAL_TITLE')
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
}