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
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\Toolbar\ToolbarHelper;

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

class JalangViewItems extends ViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$this->tabbar = JalangHelper::addSubmenu('items');
        $this->items = $this->handleIdItems($this->get('Items'));
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
        if (version_compare(JVERSION, '4.0', 'ge')) {
            Factory::getDocument()->addScriptDeclaration('
            $(document).ready(function(){
              var tbodyHeight = $("div#j-main-container > table > tbody").height();
              if(tbodyHeight < 260){
                tbodyHeight = $("form#adminForm").height($("div#sidebar-wrapper").height());
              }
            })');
        }
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			Factory::getApplication()->enqueueMessage(implode("\n", $errors), 'error');
			return false;
		}
		
		$this->addToolbar();
		if(JalangHelper::equalJoomla3x()) {
			$this->sidebar = JHtmlSidebar::render();
		}
        if (JalangHelper::greaterThanJoomla4x()) {
            $this->sidebar = Sidebar::render();
        }
		
		$fields = array();
		$adapter = JalangHelper::getHelperContent();
		$this->adapter = $adapter;
		if($adapter) {
			$fields = $adapter->getDisplayFields();
		}
		
		$this->fields = $fields;
		$this->languages = JalangHelper::getListContentLanguages();
		$this->mainlanguage = $app->getUserState('com_jalang.mainlanguage', '*');

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		ToolBar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('TRANSLATION_MANAGER'), 'article.png');

		ToolbarHelper::preferences('com_jalang');

		if(JalangHelper::equalJoomla3x()) {
			JHtmlSidebar::setAction('index.php?option=com_jalang&view=items');
		}

        if (JalangHelper::greaterThanJoomla4x()) {
            Sidebar::setAction('index.php?option=com_jalang&view=items');
        }
		
		$app = Factory::getApplication();
		$itemtype = $app->getUserState('com_jalang.itemtype', 'content');
		$adapters = JalangHelperContent::getListAdapters();

		$options = array();
		$options[0]= (object)array('value'=>0, 'text'=>Text::_('SELECT_ITEM_TYPE')) ;
		$types = array();
		foreach ($adapters as $props) {
			$types[$props['name']] = $props['title'];
		}
		//Sort by Alphabet
		ksort($types);
		foreach($types as $name => $title) {
			$options[]	= HTMLHelper::_('select.option', $name, $title);
		}
		$this->jaoptions = $options;
		$this->jaitemtype = $itemtype;
		$mainlanguage = $app->getUserState('com_jalang.mainlanguage', '*');
		$this->jamainlanguage = $mainlanguage;
		
		$jacontentlang = HTMLHelper::_('contentlanguage.existing', true, true);
		array_unshift($jacontentlang, array('value'=>0, 'text'=>Text::_('JOPTION_SELECT_LANGUAGE')));
		$this->jacontentlang = $jacontentlang;
		
		if(JalangHelper::equalJoomla3x()) {
			JHtmlSidebar::addFilter(
				Text::_('SELECT_ITEM_TYPE'),
				'itemtype',
				HTMLHelper::_('select.options', $options, 'value', 'text', $itemtype)
			);

			JHtmlSidebar::addFilter(
				Text::_('JOPTION_SELECT_LANGUAGE'),
				'mainlanguage',
				HTMLHelper::_('select.options', HTMLHelper::_('contentlanguage.existing', true, true), 'value', 'text', $mainlanguage)
			);
		}
        elseif (JalangHelper::greaterThanJoomla4x()){
            Sidebar::addFilter(
				Text::_('SELECT_ITEM_TYPE'),
				'itemtype',
				HTMLHelper::_('select.options', $options, 'value', 'text', $itemtype)
			);

			Sidebar::addFilter(
				Text::_('JOPTION_SELECT_LANGUAGE'),
				'mainlanguage',
				HTMLHelper::_('select.options', HTMLHelper::_('contentlanguage.existing', true, true), 'value', 'text', $mainlanguage)
			);
        }
        else {
			$this->filterByItemtype = HTMLHelper::_('select.options', $options, 'value', 'text', $itemtype);
			$this->filterByLanguage = HTMLHelper::_('select.options', HTMLHelper::_('contentlanguage.existing', true, true), 'value', 'text', $mainlanguage);
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		$adapter = JalangHelper::getHelperContent();
		if(!$adapter) return array();
		
		return $adapter->getSortFields();
	}

  protected function handleIdItems($items){
    foreach ($items as $item){
      switch ($item){
        case isset($item->virtuemart_category_id):
          $item->id = $item->virtuemart_category_id;
          break;
        case isset($item->virtuemart_manufacturercategories_id):
          $item->id = $item->virtuemart_manufacturercategories_id;
          break;
        case isset($item->virtuemart_manufacturer_id):
          $item->id = $item->virtuemart_manufacturer_id;
          break;
        case isset($item->virtuemart_paymentmethod_id):
          $item->id = $item->virtuemart_paymentmethod_id;
          break;
        case isset($item->virtuemart_product_id):
          $item->id = $item->virtuemart_product_id;
          break;
        case isset($item->virtuemart_shipmentmethod_id):
          $item->id = $item->virtuemart_shipmentmethod_id;
          break;
        case isset($item->virtuemart_vendor_id):
          $item->id = $item->virtuemart_vendor_id;
          break;
        case isset($item->category_id):
          $item->id = $item->category_id;
          break;
        case isset($item->product_id):
          $item->id = $item->product_id;
          break;
        default:
          break;
      }
    }
    return $items;
  }
}
