<?php
/**
 * @version		$Id: view.raw.php 1827 2013-01-25 12:01:41Z lefteris.kavadas $
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
        $params = K2HelperUtilities::getParams('com_k2');
        $model = $this->getModel('itemlist');
        $limitstart = $jinput->getInt('limitstart');
        $view = $jinput->getWord('view');
        $task = $jinput->getWord('task');

        //Add link
        if (K2HelperPermissions::canAddItem())
            $addLink = JRoute::_('index.php?option=com_k2&view=item&task=add&tmpl=component');
        $this->assignRef('addLink', $addLink);

        //Get data depending on task
        switch ($task)
        {

            case 'category' :
                //Get category
                $id = $jinput->getInt('id');
                JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'//');
                $category = JTable::getInstance('K2Category', 'Table');
                $category->load($id);

                // State Check
                if (!$category->published || $category->trash)
                {
                    $mainframe::enqueueMessage(JText::_('K2_CATEGORY_NOT_FOUND'), 'error');
                }

                //Access check
                $user = JFactory::getUser();
                if (K2_JVERSION != '15')
                {
                    if (!in_array($category->access, $user->getAuthorisedViewLevels()))
                    {
                        if ($user->guest)
                        {
                            $uri = JFactory::getURI();
                            $url = 'index.php?option=com_user&view=login&return='.$uri->toString();
                            $mainframe->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
                        }
                        else
                        {
                            $mainframe::enqueueMessage(JText::_('K2_ALERTNOTAUTH'), 'error');
                            return;
                        }
                    }
                    $languageFilter = $mainframe->getLanguageFilter();
                    $languageTag = JFactory::getLanguage()->getTag();
                    if ($languageFilter && $category->language != $languageTag && $category->language != '*')
                    {
                        return;
                    }
                }
                else
                {
                    if ($category->access > $user->get('aid', 0))
                    {
                        if ($user->guest)
                        {
                            $uri = JFactory::getURI();
                            $url = 'index.php?option=com_user&view=login&return='.$uri->toString();
                            $mainframe->redirect(JRoute::_($url, false), JText::_('K2_YOU_NEED_TO_LOGIN_FIRST'));
                        }
                        else
                        {
                            $mainframe::enqueueMessage(JText::_('K2_ALERTNOTAUTH'), 'error');
                            return;
                        }
                    }
                }

                // Hide the add new item link if user cannot post in the specific category
                if (!K2HelperPermissions::canAddItem($id))
                {
                    unset($this->addLink);
                }

                //Merge params
                $cparams = class_exists('JParameter') ? new JParameter($category->params) : new JRegistry($category->params);
                if ($cparams->get('inheritFrom'))
                {
                    $masterCategory = JTable::getInstance('K2Category', 'Table');
                    $masterCategory->load($cparams->get('inheritFrom'));
                    $cparams = class_exists('JParameter') ? new JParameter($masterCategory->params) : new JRegistry($masterCategory->params);
                }
                $params->merge($cparams);

                //Category link
                $category->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($category->id.':'.urlencode($category->alias))));

                //Category image
                $category->image = K2HelperUtilities::getCategoryImage($category->image, $params);

                //Category plugins
                $dispatcher = JDispatcher::getInstance();
                JPluginHelper::importPlugin('content');
                $category->text = $category->description;

                if (K2_JVERSION != '15')
                {
                    $dispatcher->trigger('onContentPrepare', array('com_k2.category', &$category, &$params, $limitstart));
                }
                else
                {
                    $dispatcher->trigger('onPrepareContent', array(&$category, &$params, $limitstart));
                }

                $category->description = $category->text;

                //Category K2 plugins
                $category->event->K2CategoryDisplay = '';
                JPluginHelper::importPlugin('k2');
                $results = $dispatcher->trigger('onK2CategoryDisplay', array(&$category, &$params, $limitstart));
                $category->event->K2CategoryDisplay = trim(implode("\n", $results));
                $category->text = $category->description;
                $dispatcher->trigger('onK2PrepareContent', array(&$category, &$params, $limitstart));
                $category->description = $category->text;

                $this->assignRef('category', $category);
                $this->assignRef('user', $user);

                //Category children
                $ordering = $params->get('subCatOrdering');
                $children = $model->getCategoryFirstChildren($id, $ordering);
                if (count($children))
                {
                    foreach ($children as $child)
                    {
                        if ($params->get('subCatTitleItemCounter'))
                        {
                            $child->numOfItems = $model->countCategoryItems($child->id);
                        }
                        $child->image = K2HelperUtilities::getCategoryImage($child->image, $params);
                        $child->link = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($child->id.':'.urlencode($child->alias))));
                        $subCategories[] = $child;
                    }
                    $this->assignRef('subCategories', $subCategories);
                }

                //Set limit
                $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');

                //Set featured flag
                $jinput->set('featured', $params->get('catFeaturedItems'));

                //Set layout
                $this->setLayout('category');

                //Set title
                $title = $category->name;

                // Set ordering
                if ($params->get('singleCatOrdering'))
                {
                    $ordering = $params->get('singleCatOrdering');
                }
                else
                {
                    $ordering = $params->get('catOrdering');
                }

                break;

            case 'user' :
                //Get user
                $id = $jinput->getInt('id');
                $userObject = JFactory::getUser($id);

                //Check user status
                if ($userObject->block)
                {
                    $mainframe::enqueueMessage(JText::_('K2_USER_NOT_FOUND'), 'error');
                }

                //Get K2 user profile
                $userObject->profile = $model->getUserProfile();

                //User image
                $userObject->avatar = K2HelperUtilities::getAvatar($userObject->id, $userObject->email, $params->get('userImageWidth'));

                //User K2 plugins
                $userObject->event->K2UserDisplay = '';
                if (is_object($userObject->profile) && $userObject->profile->id > 0)
                {

                    $dispatcher = JDispatcher::getInstance();
                    JPluginHelper::importPlugin('k2');
                    $results = $dispatcher->trigger('onK2UserDisplay', array(&$userObject->profile, &$params, $limitstart));
                    $userObject->event->K2UserDisplay = trim(implode("\n", $results));
                    $userObject->profile->url = htmlspecialchars($userObject->profile->url, ENT_QUOTES, 'UTF-8');

                }

                $this->assignRef('user', $userObject);

                //Set layout
                $this->setLayout('user');

                //Set limit
                $limit = $params->get('userItemCount');

                //Set title
                $title = $userObject->name;

                // Set ordering
                $ordering = $params->get('userOrdering');

                break;

            case 'tag' :
                //Set layout
                $this->setLayout('tag');

                //Set limit
                $limit = $params->get('tagItemCount');

                //set title
                $title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.$jinput->get('tag');

                // Set ordering
                $ordering = $params->get('tagOrdering');
                break;

            case 'search' :
                //Set layout
                $this->setLayout('generic');
                $tpl = $jinput->getCmd('tpl', '');

                //Set limit
                $limit = $params->get('genericItemCount');

                //Set title
                $title = JText::_('K2_SEARCH_RESULTS_FOR').' '.$jinput->getString('searchword');
                break;

            case 'date' :
                //Set layout
                $this->setLayout('generic');

                //Set limit
                $limit = $params->get('genericItemCount');

                // Set title
                if ($jinput->getInt('day'))
                {
                    $date = strtotime($jinput->getInt('year').'-'.$jinput->getInt('month').'-'.$jinput->getInt('day'));
                    $dateFormat = (K2_JVERSION == '15') ? '%A, %d %B %Y' : 'l, d F Y';
                    $title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
                }
                else
                {
                    $date = strtotime($jinput->getInt('year').'-'.$jinput->getInt('month'));
                    $dateFormat = (K2_JVERSION == '15') ? '%B %Y' : 'F Y';
                    $title = JText::_('K2_ITEMS_FILTERED_BY_DATE').' '.JHTML::_('date', $date, $dateFormat);
                }
                // Set ordering
                $ordering = 'rdate';
                break;

            default :
                //Set layout
                $this->setLayout('category');
                $user = JFactory::getUser();
                $this->assignRef('user', $user);

                //Set limit
                $limit = $params->get('num_leading_items') + $params->get('num_primary_items') + $params->get('num_secondary_items') + $params->get('num_links');
                //Set featured flag
                $jinput->set('featured', $params->get('catFeaturedItems'));

                //Set title
                $title = $params->get('page_title');

                // Set ordering
                $ordering = $params->get('catOrdering');

                break;
        }

        //Set limit for model
        $jinput->set('limit', $limit);

        if (!isset($ordering))
        {
            $items = $model->getData();
        }
        else
        {
            $items = $model->getData($ordering);
        }

        //Pagination
        jimport('joomla.html.pagination');
        $total = count($items) ? $model->getTotal() : 0;
        $pagination = new JPagination($total, $limitstart, $limit);

        //Prepare items
        $user = JFactory::getUser();
        $cache = JFactory::getCache('com_k2_extended');
		$model = JModelLegacy::getInstance('item', 'K2Model');
        for ($i = 0; $i < sizeof($items); $i++)
        {

            //Item group
            if ($task == "category" || $task == "")
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

            //Check if model should use cache for preparing item even if user is logged in
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

            //Prepare item
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

            //Plugins
			$items[$i]->params->set('genericItemIntroText', $params->get('catItemIntroText'));
            $items[$i] = $model->execPlugins($items[$i], $view, $task);

            //Trigger comments counter event
            $dispatcher = JDispatcher::getInstance();
            JPluginHelper::importPlugin('k2');
            $results = $dispatcher->trigger('onK2CommentsCounter', array(&$items[$i], &$params, $limitstart));
            $items[$i]->event->K2CommentsCounter = trim(implode("\n", $results));

        }

        //Pathway
        $pathway = $mainframe->getPathWay();
        $pathway->addItem($title);

        //Feed link
        $config = JFactory::getConfig();
        $menu = $mainframe->getMenu();
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

        //Assign data
        if ($task == "category" || $task == "")
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

        //Set default values to avoid division by zero
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

        //Look for template files in component folders
        $this->_addPath('template', JPATH_COMPONENT.'/templates');
        $this->_addPath('template', JPATH_COMPONENT.'/templates'.'/default');

        //Look for overrides in template folder (K2 template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2'.'/templates');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2'.'/templates'.'/default');

        //Look for overrides in template folder (Joomla! template structure)
        $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2'.'/default');
        $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2');

        //Look for specific K2 theme files
        if ($params->get('theme'))
        {
            $this->_addPath('template', JPATH_COMPONENT.'/templates/'.$params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2'.'/templates/'.$params->get('theme'));
            $this->_addPath('template', JPATH_SITE.'/templates/'.$mainframe->getTemplate().'/html'.'/com_k2/'.$params->get('theme'));
        }

        $db = JFactory::getDBO();
        $nullDate = $db->getNullDate();
        $this->assignRef('nullDate', $nullDate);
        
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

        parent::display($tpl);
    }

    function module()
    {
        jimport('joomla.application.module.helper');
        $mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
        $moduleID = $jinput->getInt('moduleID');
        $model = K2Model::getInstance('Itemlist', 'K2Model');
        if ($moduleID)
        {
            $result = $model->getModuleItems($moduleID);
            $items = $result->items;
            $componentParams = JComponentHelper::getParams('com_k2');
            if (is_string($result->params))
            {
                $params = class_exists('JParameter') ? new JParameter($result->params) : new JRegistry($result->params);
            }
            else
            {
                $params = $result->params;
            }
           
            if ($params->get('getTemplate'))
                require (JModuleHelper::getLayoutPath('mod_k2_content', $params->get('getTemplate').'/default'));
            else
                require (JModuleHelper::getLayoutPath($result->module, 'default'));
        }
        $mainframe->close();
    }

}
