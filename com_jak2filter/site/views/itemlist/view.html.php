<?php

/**
 * @version		$Id: view.html.php 1956 2013-04-04 13:40:22Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

jimport('joomla.application.component.view');
require_once JPATH_ROOT . '/components/com_k2/models/item.php';

class JAK2FilterViewItemlist extends JAK2FilterView
{

	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
		//$params = K2HelperUtilities::getParams('com_k2');
		if ($mainframe->isSite()) {
			$params = $mainframe->getParams('com_jak2filter');
		} else {
			$params = JComponentHelper::getParams('com_jak2filter');
		}
		//JA K2 FILTER - custom params to display well search result with category view
		$params->set('show_page_heading', 1);
		$params->set('catItemImage', 1);
		$params->set('catCatalogMode', 0);
		$params->set('theme', $jinput->getString('theme', $params->get('theme', 'default')));
		$params->def('num_leading_items', 0);
		$params->def('num_leading_columns', 1);
		$params->def('num_primary_items', 9);
		$params->def('num_primary_columns', 3);
		$params->def('num_secondary_items', 0);
		$params->def('num_secondary_columns', 1);
		$params->def('num_links', 0);
		$params->def('num_links_columns', 1);
		//
		$model = $this->getModel('itemlist');
		$limitstart = $jinput->getInt('limitstart') ? $jinput->getInt('limitstart') : 0;
		$view = $jinput->getWord('view');
		$task = $jinput->getWord('task');
		$db = JFactory::getDBO();

		// Add link
		if (K2HelperPermissions::canAddItem())
			$addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
		$this->assignRef('addLink', $addLink);

		// Get data depending on task
		switch ($task)
		{
			case 'search' :
				// Set layout
				$this->setLayout('category');

				// Set limit
				//$limit = $params->get('genericItemCount');
				//JA K2 FILTER - like category view
				$limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

				// Set title
				$title = JText::_('JAK2FILTER_SEARCH_RESULTS').' '.$jinput->get('searchword', '', 'STRING');

				$addHeadFeedLink = $params->get('genericFeedLink', 1);
				//JA K2 FILTER - Set ordering
				$ordering = $jinput->get('ordering', $params->get('catOrdering'));

				break;

			default :
				// Set layout
				$this->setLayout('category');
				$user = JFactory::getUser();
				$this->assignRef('user', $user);

				// Set limit
				$limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');
				// Set featured flag
				$jinput->set('featured', $params->get('catFeaturedItems'));

				// Set title
				$title = $params->get('page_title');

				// Set ordering
				$ordering = $params->get('catOrdering');

				$addHeadFeedLink = $params->get('catFeedLink', 1);

				break;
		}

		// Set limit for model
		if (!$limit)
		$limit = 10;
		$jinput->set('limit', $limit);

		// Get items
		if (!isset($ordering))
		{
			$items = $model->getData();
		}
		else
		{
			$items = $model->getData($ordering);
		}
		
		// check for min max keyword. we return 0 item if not in range.
        $minium_keyword = $params->get('minium_keyword',3);
        $maximum_keyword = $params->get('maximum_keyword',20);
        $badchars = array('#', '>', '<', '\\');
        $search = JString::trim(JString::str_ireplace($badchars, '', $jinput->getString('searchword', '')));
        if ($search !== '' && (strlen($search) < $minium_keyword || strlen($search) > $maximum_keyword)) {
            $items = [];
            $this->setLayout('error');
            JFactory::getApplication()->enqueueMessage(JText::sprintf('JAK2FILTER_SEARCH_TERM', $minium_keyword, $maximum_keyword), 'error');
            parent::display($tpl);
            return;
        }
		if(count($items)==0){
			$this->setLayout('error');
			$blank_page = $params->get('blank_page', 0);
			// if choose to use blank page and not yet search.
			$this->blank_txt = $params->get('blank_txt', '');
			$this->blank_page = $blank_page;
			$this->blanktxt_after_search = $params->get('blanktxt_after_search', 0);
			parent::display($tpl);
			return;
		}
		// Pagination
		jimport('joomla.html.pagination');
		$total = count($items) ? $model->getTotal() : 0;
		if ($params->get('show_items_result', 0))
		    $title .= JText::sprintf( 'JAK2_FILTER_TOTAL_NUMBER_ITEMS', $total );
		$pagination = new JPagination($total, $limitstart, $limit);
		$vars = $jinput->get->get->getArray();
		$this->issearch = $vars['issearch'];
		
		//Fix bug: page navigation does not work properly if SEF is enabled
		if(count($vars)) {
			foreach ($vars as $k => $v) {
				if (preg_match('/(xf|tag|iss|category|range|searchword|rating|order|date|Itemid)/',$k)) {
					if(is_array($v)) {
						foreach ($v as $sk => $sv) {
							$pagination->setAdditionalUrlParam($k.'['.$sk.']', $sv);
						}
					} else {
						$pagination->setAdditionalUrlParam($k, $v);
					}
				}
			}
		}

		//Prepare items
		$user = JFactory::getUser();
		$cache = JFactory::getCache('com_k2_extended');
		$model = JModelLegacy::getInstance('item', 'K2Model');

		for ($i = 0; $i < sizeof($items); $i++)
		{

			//Item group
			// JA K2 FILTER - using category view for displaying search result
			if ($task == "category" || $task == "search" || $task == "")
			{
				if ($i < ($params->get('num_links') + $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items')))
					$items[$i]->itemGroup = 'links';
				if ($i < ($params->get('num_secondary_items') + $params->get('num_leading_items') + $params->get('num_primary_items')))
					$items[$i]->itemGroup = 'secondary';
				if ($i < ($params->get('num_primary_items') + $params->get('num_leading_items')))
					$items[$i]->itemGroup = 'primary';
				if ($i < $params->get('num_leading_items'))
					$items[$i]->itemGroup = 'leading';
			}

			// Check if the model should use the cache for preparing the item even if the user is logged in
			/*
			// JA K2 Filter: Remove cache method
			if ($user->guest || $task == 'tag' || $task == 'search' || $task == 'date')
			{
				$cacheFlag = true;
			}
			else
			{
				$cacheFlag = true;
				if (K2HelperPermissions::canEditItem($items[$i]->created_by, $items[$i]->catid))
				{
					$cacheFlag = false;
				}
			}

			// Prepare item
			if ($cacheFlag)
			{
				$hits = $items[$i]->hits;
				$items[$i]->hits = 0;
				JTable::getInstance('K2Category', 'Table');
				$items[$i] = $cache->call(array($model, 'prepareItem'), $items[$i], $view, $task);
				$items[$i]->hits = $hits;
			}
			else
			{
				$items[$i] = $model->prepareItem($items[$i], $view, $task);
			}
			*/
			// JA K2 Filter: PrepareItem
			$items[$i] = $model->prepareItem($items[$i], $view, '');

			// Plugins
			$items[$i]->params->set('genericItemIntroText', $params->get('catItemIntroText'));
			$items[$i]->params->set('catItemK2Plugins', $params->get('catItemK2Plugins'));
			$items[$i] = $model->execPlugins($items[$i], 'itemlist', '');

			// Trigger comments counter event
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('k2');
			$results = $dispatcher->trigger('onK2CommentsCounter', array(&$items[$i], &$params, $limitstart));
			$items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));

			//JA K2 FILTER - CUSTOM VIEW OPTIONS
			$items[$i]->params->merge($params);
			if(!is_array($items[$i]->extra_fields)) {
				$items[$i]->extra_fields = $model->getItemExtraFields($items[$i]->extra_fields, $items[$i]);
			}
			if ($params->get('catItemIntroTextWordLimit'))
			{
				$items[$i]->introtext = K2HelperUtilities::wordLimit($items[$i]->introtext, $params->get('catItemIntroTextWordLimit'));
			}
			
			//JA K2 FILTER - CUSTOM VIEW OPTIONS - AUTHOR
			if (!empty($items[$i]->created_by_alias))
			{
				$items[$i]->author = new stdClass;
				$items[$i]->author->name = $items[$i]->created_by_alias;
				$items[$i]->author->avatar = K2HelperUtilities::getAvatar('alias');
				$items[$i]->author->link = JURI::root();
			}
			else
			{
				$author = JFactory::getUser($items[$i]->created_by);
				$items[$i]->author = $author;
				$items[$i]->author->link = JRoute::_(K2HelperRoute::getUserRoute($items[$i]->created_by));
				$items[$i]->author->profile = $model->getUserProfile($items[$i]->created_by);
				$items[$i]->author->avatar = K2HelperUtilities::getAvatar($author->id, $author->email, $params->get('userImageWidth'));
			}

			if (!isset($items[$i]->author->profile) || is_null($items[$i]->author->profile))
			{

				$items[$i]->author->profile = new JObject;
				$items[$i]->author->profile->gender = NULL;

			}
			//JA K2 FILTER - CUSTOM VIEW OPTIONS - RATING
			$items[$i]->votingPercentage = $model->getVotesPercentage($items[$i]->id);
			$items[$i]->numOfvotes = $model->getVotesNum($items[$i]->id);

		}

		// Set title
		$document = JFactory::getDocument();
		$application = JFactory::getApplication();
		$menus = $application->getMenu();
		$menu = $menus->getActive();
		if (is_object($menu))
		{
			if (is_string($menu->params))
			{
				$menu_params = K2_JVERSION == '15' ? new JParameter($menu->params) : new JRegistry($menu->params);
			}
			else
			{
				$menu_params = $menu->params;
			}
			$params->set('page_title', $menu_params->get('page_title', $title));
			$params->set('page_heading', $menu_params->get('page_heading', $title));

			// override theming params
			$params_query = new JRegistry($menu->query);
			$params->set('theme', $params_query->get('theme', $params->get('theme')));
		}
		else
		{
			$params->set('page_title', $title);
		}

		// We're adding a new variable here which won't get the appended/prepended site title,
		// when enabled via Joomla!'s SEO/SEF settings
		$params->set('page_title_clean', $title);

		if (K2_JVERSION != '15')
		{
			if ($mainframe->getCfg('sitename_pagetitles', 0) == 1)
			{
				$tmpTitle = JText::sprintf('JPAGETITLE', $mainframe->getCfg('sitename'), $params->get('page_title'));
				$params->set('page_title', $tmpTitle);
			}
			elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 2)
			{
				$tmpTitle = JText::sprintf('JPAGETITLE', $params->get('page_title'), $mainframe->getCfg('sitename'));
				$params->set('page_title', $tmpTitle);
			}
		}
		$document->setTitle($params->get('page_title'));

		// Search - Update the Google Search results container (K2 v2.6.6+)
		/*
		if ($task == 'search')
		{
			$googleSearchContainerID = trim($params->get('googleSearchContainer', 'k2GoogleSearchContainer'));
			if ($googleSearchContainerID == 'k2Container')
			{
				$googleSearchContainerID = 'k2GoogleSearchContainer';
			}
			$params->set('googleSearchContainer', $googleSearchContainerID);
		}
		*/
		// Set metadata for category
		if ($task == 'category')
		{
			if ($category->metaDescription)
			{
				$document->setDescription($category->metaDescription);
			}
			else
			{
				$metaDescItem = preg_replace("#{(.*?)}(.*?){/(.*?)}#s", '', $this->category->description);
				$metaDescItem = strip_tags($metaDescItem);
				$metaDescItem = K2HelperUtilities::characterLimit($metaDescItem, $params->get('metaDescLimit', 150));
				$metaDescItem = htmlspecialchars($metaDescItem, ENT_QUOTES, 'UTF-8');
				$document->setDescription($metaDescItem);
			}
			if ($category->metaKeywords)
			{
				$document->setMetadata('keywords', $category->metaKeywords);
			}
			if ($category->metaRobots)
			{
				$document->setMetadata('robots', $category->metaRobots);
			}
			if ($category->metaAuthor)
			{
				$document->setMetadata('author', $category->metaAuthor);
			}
		}

		if (K2_JVERSION != '15')
		{

			// Menu metadata options
			if ($params->get('menu-meta_description'))
			{
				$document->setDescription($params->get('menu-meta_description'));
			}

			if ($params->get('menu-meta_keywords'))
			{
				$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
			}

			if ($params->get('robots'))
			{
				$document->setMetadata('robots', $params->get('robots'));
			}

			// Menu page display options
			if ($params->get('page_heading'))
			{
				$params->set('page_title', $params->get('page_heading'));
			}
			$params->set('show_page_title', $params->get('show_page_heading'));

		}

		// Pathway
		$pathway = $mainframe->getPathWay();


		if ($menu)
		{
			if (!isset($menu->query['task'])) {
				$menu->query['task'] = '';
			}
		
			switch ($task)
			{
				case 'category' :
					if ($menu->query['task'] != 'category' || $menu->query['id'] != $jinput->getInt('id'))
						$pathway->addItem($title, '');
					break;
				case 'user' :
					if ($menu->query['task'] != 'user' || $menu->query['id'] != $jinput->getInt('id'))
						$pathway->addItem($title, '');
					break;

				case 'tag' :
					if ($menu->query['task'] != 'tag' || $menu->query['tag'] != $jinput->get('tag'))
						$pathway->addItem($title, '');
					break;

				case 'search' :
				case 'date' :
					$pathway->addItem($title, '');
					break;
			}
		}

		// Feed link
		$config = JFactory::getConfig();
		$menu = $application->getMenu();
		$default = $menu->getDefault();
		$active = $menu->getActive();
		if ($task == 'tag')
		{
			$link = K2HelperRoute::getTagRoute($jinput->get('tag'));
		}
		else
		{
			$link = '';
		}
		$sef = K2_JVERSION == '30' ? $config->get('sef') : $config->getValue('config.sef');
		if (!is_null($active) && $active->id == $default->id && $sef)
		{
			$link .= '&Itemid='.$active->id.'&format=feed&limitstart=';
		}
		else
		{
			$link .= '&format=feed&limitstart=';
		}

		$feed = JRoute::_($link);
		$this->assignRef('feed', $feed);

		// Add head feed link
		if ($addHeadFeedLink)
		{
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}

		//JA K2 FILTER - custom item view option
		// Assign data
		if ($task == "category" || $task == "search" || $task == "")
		{
			$leading = @array_slice($items, 0, $params->get('num_leading_items'));
			$primary = @array_slice($items, $params->get('num_leading_items'), $params->get('num_primary_items'));
			$secondary = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items'), $params->get('num_secondary_items'));
			$links = @array_slice($items, $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items'), $params->get('num_links'));
			$this->assignRef('leading', $leading);
			$this->assignRef('primary', $primary);
			$this->assignRef('secondary', $secondary);
			$this->assignRef('links', $links);
		}
		else
		{
			$this->assignRef('items', $items);
		}

		// Set default values to avoid division by zero
		if ($params->get('num_leading_columns') == 0)
			$params->set('num_leading_columns', 1);
		if ($params->get('num_primary_columns') == 0)
			$params->set('num_primary_columns', 1);
		if ($params->get('num_secondary_columns') == 0)
			$params->set('num_secondary_columns', 1);
		if ($params->get('num_links_columns') == 0)
			$params->set('num_links_columns', 1);

		$this->assignRef('params', $params);
		$this->assignRef('pagination', $pagination);

		// Set Facebook meta data
		$document = JFactory::getDocument();
		$uri = JURI::getInstance();
		$document->setMetaData('og:url', $uri->toString());
		$document->setMetaData('og:title', htmlspecialchars($document->getTitle(), ENT_QUOTES, 'UTF-8'));
		$document->setMetaData('og:type', 'website');
		if ($task == 'category' && $this->category->image && strpos($this->category->image, 'placeholder/category.png') === false)
		{
			$image = substr(JURI::root(), 0, -1).str_replace(JURI::root(true), '', $this->category->image);
			$document->setMetaData('og:image', $image);
			$document->setMetaData('image', $image);
		}
		$document->setMetaData('og:description', htmlspecialchars(strip_tags($document->getDescription()??''), ENT_QUOTES, 'UTF-8'));
		
		// Look for template files in component folders
		$this->_addPath('template', JPATH_BASE.'/components/com_k2/templates');
		$this->_addPath('template', JPATH_BASE.'/components/com_k2/templates/default');

		// Look for overrides in template folder (K2 template structure)
		$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/templates');
		$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/templates/default');

		// Look for overrides in template folder (Joomla! template structure)
		$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/default');
		$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2');

		// Look for specific K2 theme files
		if ($params->get('theme'))
		{
			$this->_addPath('template', JPATH_BASE.'/components/com_k2/templates/'.$params->get('theme'));
			$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/templates/'.$params->get('theme'));
			$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/'.$params->get('theme'));
		}

		$nullDate = $db->getNullDate();
		$this->assignRef('nullDate', $nullDate);
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('k2');
		$dispatcher->trigger('onK2BeforeViewDisplay');
		// Prevent spammers from using the tag view
		if ($task == 'tag' && !count($this->items))
		{
			$tag = $jinput->getString('tag');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT id FROM #__k2_tags WHERE name = '.$db->quote($tag));
			$tagID = $db->loadResult();
			if (!$tagID)
			{
				$mainframe::enqueueMessage(JText::_('K2_NOT_FOUND'), 'error');
				return false;
			}
		}

		$badchars = array('#', '>', '<', '\\');
		$searchword = JString::trim(JString::str_ireplace($badchars, '', $jinput->getString('searchword', '')));

		if($params->get('enableHighlightSearchTerm', 0)) {
			$document->addScript(JURI::root(true).'/modules/mod_jak2filter/assets/jquery/jquery.highlight-4.js');
			$document->addStyleDeclaration('.highlight { background-color: #FFFFCC }');
			if(!empty($searchword) && strpos($searchword, '-') !== 0) {
				$document->addScriptDeclaration("
				(function($) {
					$(document).ready(function(){
						if($('#k2Container').length) {
        					jak2Highlight($('#k2Container'), \"".addslashes($searchword)."\");
						}
					});
				})(jQuery);
				");
			}
		}

		$blank_page = $params->get('blank_page', 0);
		if (!empty($blank_page)) {
			// if choose to use blank page and not yet search.
			$this->blank_txt = $params->get('blank_txt', '');
			$this->blank_page = $blank_page;
			$this->issearch = $vars['issearch'];
			$this->blanktxt_after_search = $params->get('blanktxt_after_search', 0);
			// Look for template files in jak2filter component folders
			$this->_addPath('template', JPATH_BASE.'/components/com_jak2filter/views/itemlist/tmpl');
			// Look for overrides in template folder (Joomla! template structure)
			$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/default');
			$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2');

			// Look for specific K2 theme files
			if ($params->get('theme') != 'default')
			{
				$this->_addPath('template', JPATH_BASE.'/components/com_k2/templates/'.$params->get('theme'));
				$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/templates/'.$params->get('theme'));
				$this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html/com_k2/'.$params->get('theme'));
			}
		}

		// add canonical, prev, next rel
		$url = $uri->toString(array('scheme', 'host', 'path'));
		$canonical = '<link rel="canonical" href="'.$url.'" />';
		$document->addCustomTag($canonical);


		$pdata = $pagination->getData();
		if ($pdata->previous->link) {
			$pre = '<link rel="prev" href="'.$uri->toString(array('scheme', 'host')) . $pdata->previous->link.'" />';
			$document->addCustomTag($pre);
		}

		if ($pdata->next->link) {
			$next = '<link rel="next" href="'.$uri->toString(array('scheme', 'host')) . $pdata->next->link.'" />';
			$document->addCustomTag($next);
		}

		$moduleid = $jinput->getInt('jamoduleid');
		$isUpdateCounterAjax = $jinput->get('tmpl') === 'component' && $moduleid;
		if ($isUpdateCounterAjax) {
			$module = array(
				'content' => $this->renderModule($moduleid)
			);

			$customTag = '<script type="text/template" id="ja-module-content">'.json_encode($module).'</script>';
			$document->addCustomTag($customTag);
		}

		parent::display($tpl);
	}

	function renderModule($moduleid) {
		$db = JFactory::getDbo();
		$query = "SELECT * FROM `#__modules` WHERE id = $moduleid";
		$module = $db->setQuery($query)->loadObject();
		
		if (!$module) {
			return '';
		}

		return JModuleHelper::renderModule($module);
	}
}
