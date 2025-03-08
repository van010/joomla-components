<?php
/**
 * @version		$Id: k2category.php 1812 2013-01-14 18:45:06Z lefteris.kavadas $
 * @package		K2
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');

class K2ElementJAK2FilterCategory extends K2Element
{

    function fetchElement($name, $value, &$node, $control_name)
    {
        $db = JFactory::getDBO();
        $query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                if (K2_JVERSION != '15')
                {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        
        foreach ($list as $item)
        {
            $item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
            @$mitems[] = JHTML::_('select.option', $item->id, $item->treename);
        }

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
        }
        else
        {
            $fieldName = $control_name.'['.$name.']';
        }

        return JHTML::_('select.genericlist', $mitems, $fieldName, ' class="inputbox" size="15"', 'value', 'text', $value);

    }

}

class JFormFieldJAK2FilterCategory extends K2ElementJAK2FilterCategory
{
    var $type = 'jak2filtercategory';
}

class JElementJAK2FilterCategory extends K2ElementJAK2FilterCategory
{
    var $_name = 'jak2filtercategory';
}
