<?php
/**
 * @version		$Id: view.feed.php 1930 2013-02-13 14:11:07Z joomlaworks $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
require_once JPATH_ROOT . '/components/com_k2/models/item.php';

class JAK2FilterViewItemlist extends JAK2FilterView
{

    function display($tpl = null)
    {

        $mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;
        $params = K2HelperUtilities::getParams('com_k2');
        $document = JFactory::getDocument();
        $model = $this->getModel('itemlist');
        $limitstart = $jinput->getInt('limitstart');
        $moduleID = $jinput->getInt('moduleID');

        if ($moduleID)
        {

            $result = $model->getModuleItems($moduleID);
            $items = $result->items;
            $title = $result->title;

        }
        else
        {

            //Get data depending on task
            $task = $jinput->getCmd('task');
            switch ($task)
            {

                case 'category' :
                    //Get category
                    $id = $jinput->getInt('id');
                    JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');
                    $category = JTable::getInstance('K2Category', 'Table');
                    $category->load($id);

                    // State check
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
                                $url = 'index.php?option=com_users&view=login&return='.$uri->toString();
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

                    //Set featured flag
                    $jinput::set('featured', $params->get('catFeaturedItems'));

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

                    //Set title
                    $title = $userObject->name;

                    // Set ordering
                    $ordering = $params->get('userOrdering');

                    break;

                case 'tag' :
                    //set title
                    $title = JText::_('K2_DISPLAYING_ITEMS_BY_TAG').' '.$jinput->getString('tag');

                    // Set ordering
                    $ordering = $params->get('tagOrdering');
                    break;

                case 'search' :
                    //Set title
                    $title = JText::_('K2_SEARCH_RESULTS_FOR').' '.$jinput->getString('searchword');
                    break;

                case 'date' :
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

                    //Set featured flag
                    $jinput->set('featured', $params->get('catFeaturedItems'));

                    //Set title
                    $title = $params->get('page_title');

                    // Set ordering
                    $ordering = $params->get('catOrdering');

                    break;
            }

            // Various Feed Validations
            $title = JFilterOutput::ampReplace($title);

            // Get items
            if (!isset($ordering))
            {
                $items = $model->getData();
            }
            else
            {
                $items = $model->getData($ordering);
            }

        }

        // Prepare feed items
        //$model = &$this->getModel('itemlist');
        foreach ($items as $item)
        {

            $item = $model->prepareFeedItem($item);
            $item->title = $this->escape($item->title);
            $item->title = html_entity_decode($item->title);
            $feedItem = new JFeedItem();
            $feedItem->title = $item->title;
            $feedItem->link = $item->link;
            $feedItem->description = $item->description;
            $feedItem->date = $item->created;
            $feedItem->category = $item->category->name;
            $feedItem->author = $item->author->name;
            if ($params->get('feedBogusEmail'))
            {
                $feedItem->authorEmail = $params->get('feedBogusEmail');
            }
            else
            {
                if ($mainframe->getCfg('feed_email') == 'author')
                {
                    $feedItem->authorEmail = $item->author->email;
                }
                else
                {
                    $feedItem->authorEmail = $mainframe->getCfg('mailfrom');
                }
            }

            // Add item
            $document->addItem($feedItem);
        }

        // Set title
        $document = JFactory::getDocument();
        $menus = $mainframe->getMenu();
        $menu = $menus->getActive();
        if (is_object($menu))
        {
            $menu_params = class_exists('JParameter') ? new JParameter($menu->params) : new JRegistry($menu->params);
            if (!$menu_params->get('page_title'))
                $params->set('page_title', $title);
        }
        else
        {
            $params->set('page_title', $title);
        }
        if (K2_JVERSION != '15')
        {
            if ($mainframe->getCfg('sitename_pagetitles', 0) == 1)
            {
                $title = JText::sprintf('JPAGETITLE', $mainframe->getCfg('sitename'), $params->get('page_title'));
                $params->set('page_title', $title);
            }
            elseif ($mainframe->getCfg('sitename_pagetitles', 0) == 2)
            {
                $title = JText::sprintf('JPAGETITLE', $params->get('page_title'), $mainframe->getCfg('sitename'));
                $params->set('page_title', $title);
            }
        }
        $document->setTitle($params->get('page_title'));

        // Prevent spammers from using the tag view
        if ($task == 'tag' && !count($items))
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

    }

}
