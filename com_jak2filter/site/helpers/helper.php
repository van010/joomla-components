<?php
/**
* ------------------------------------------------------------------------
* Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
* @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
* Author: J.O.O.M Solutions Co., Ltd
* Websites: http://www.joomlart.com - http://www.joomlancers.com
* This file may not be redistributed in whole or significant part.
* ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	com_jak2filter
 * @since		1.5
 */
class JAK2FilterHelper
{
	public function __construct() {

	}

	/**
	 * Update indexing data for all items
	 *
	 * @param string $context
	 */
	public function indexingData($context) {
		@ignore_user_abort(true);

		$db = JFactory::getDbo();
		$jnow = JFactory::getDate();
		$now = K2_JVERSION == '15' ? $jnow->toMySQL() : $jnow->toSql();
		$nullDate = $db->getNullDate();

		//RE-INDEXING ALL ITEMS
		$where = array();
		$where[] = 'published=1';
		$where[] = 'trash=0';
		$where[] = "( publish_up = ".$db->Quote($nullDate)." OR publish_up <= ".$db->Quote($now)." )";
		$where[] = "( publish_down = ".$db->Quote($nullDate)." OR publish_down >= ".$db->Quote($now)." )";

		$whereItem = ' WHERE '.implode(' AND ', $where);


		//UPDATE TAXONOMY
		$qUpdateTableCollation = 'ALTER TABLE #__jak2filter_taxonomy CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin';
		$db->setQuery($qUpdateTableCollation);
		$db->execute();

		$qTaxonomy = $db->getQuery(true);
		$qTaxonomy->insert('#__jak2filter_taxonomy');
		$qTaxonomy->columns(array($db->quoteName('type'), $db->quoteName('title'), $db->quoteName('asset_id'), $db->quoteName('option_id'), $db->quoteName('num_items'), $db->quoteName('labels')));
		$counter = 0;
		$batchProcess = 50;

		$this->truncateTable('#__jak2filter_taxonomy');
		//1. Category
		$query = "SELECT `id`, `name` FROM #__k2_categories";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if(count($items)) {
			foreach ($items as $item) {
				$qTaxonomy->values($db->quote('category') . ', ' . $db->quote($item->name). ', ' . $db->quote($item->id). ', ' . $db->quote(0). ', ' . $db->quote(0) . ', '.$db->quote($item->name));
				$counter++;
				if($counter >= $batchProcess) {
					$db->setQuery($qTaxonomy);
					$db->query();

					$qTaxonomy->clear('values');
					$counter = 0;
				}
			}
		}

		//2. Author
		$query = "
			SELECT DISTINCT u.id as userid, u.name
			FROM #__users AS u
			INNER JOIN #__k2_items AS i ON i.created_by = u.id
			";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if(count($items)) {
			foreach ($items as $item) {
				$qTaxonomy->values($db->quote('author') . ', ' . $db->quote($item->name). ', ' . $db->quote($item->userid). ', ' . $db->quote(0). ', ' . $db->quote(0) . ', '.$db->quote($item->name));
				$counter++;
				if($counter >= $batchProcess) {
					$db->setQuery($qTaxonomy);
					$db->query();

					$qTaxonomy->clear('values');
					$counter = 0;
				}
			}
		}

		//3. Tag
		$query = "SELECT `id`, `name` FROM #__k2_tags";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if(count($items)) {
			foreach ($items as $item) {
				$qTaxonomy->values($db->quote('tag') . ', ' . $db->quote($item->name). ', ' . $db->quote($item->id). ', ' . $db->quote(0). ', ' . $db->quote(0) . ', '.$db->quote($item->name));

				$counter++;
				if($counter >= $batchProcess) {
					$db->setQuery($qTaxonomy);
					$db->query();

					$qTaxonomy->clear('values');
					$counter = 0;
				}
			}
		}

		//4. Extra Field
		$query = "SELECT `id`, `name`, `group`, `value` FROM #__k2_extra_fields WHERE `type` IN (".$db->quote('select').",".$db->quote('multipleSelect').",".$db->quote('radio') . ")";
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if(count($items)) {
			foreach ($items as $item) {
				$xfields = json_decode($item->value);
				if(count($xfields)) {
					foreach ($xfields as $xfield) {
						$qTaxonomy->values($db->quote('xfield') . ', ' . $db->quote($item->name). ', ' . $db->quote($item->id). ', ' . $db->quote($xfield->value). ', ' . $db->quote(0) . ', '.$db->quote($xfield->name));

						$counter++;
						if($counter >= $batchProcess) {
							$db->setQuery($qTaxonomy);
							$db->query();

							$qTaxonomy->clear('values');
							$counter = 0;
						}
					}
				}
			}
		}

		//5. Labels + Text + Date
		$query = "SELECT `id` FROM #__k2_extra_fields WHERE `type` IN (".$db->quote('textfield').",".$db->quote('date').")";
		$db->setQuery($query);
		$aTextfields = $db->loadColumn();

		$query = 'SELECT id FROM #__k2_extra_fields WHERE `type` = '.$db->quote('labels');
		$db->setQuery($query);
		$aLabels = $db->loadColumn();
		$aListLabels = array();

		$query = "SELECT id, catid, created_by, extra_fields, language FROM #__k2_items ".$whereItem;
		$db->setQuery($query);
		$k2items = $db->loadObjectList();

		foreach ($k2items as $item)
		{
			$values = json_decode($item->extra_fields);
			if(is_array($values) && count($values))
			{
				foreach ($values as $val)
				{
					if(!is_array($val->value) && (in_array($val->id, $aLabels) || in_array($val->id, $aTextfields)))
					{
						if(in_array($val->id, $aLabels)) {
							$fieldTitle = "Searchable label";
							$labels = explode(',', $val->value);
						} else {
							$fieldTitle = "Text";
							$labels = array($val->value);
						}

						if(count($labels))
						{
							foreach($labels as $label)
							{
								$label = trim($label);
								if(empty($label)) continue;
								if(in_array(strtolower($val->id.'_'.$label), $aListLabels)) continue;
								$aListLabels[] = strtolower($val->id.'_'.$label);

								$qTaxonomy->values($db->quote('xfield') . ', ' . $db->quote($fieldTitle). ', ' . $db->quote($val->id). ', ' . $db->quote(0). ', ' . $db->quote(0) . ', ' . $db->quote($label));

								$counter++;
								if($counter >= $batchProcess)
								{
									$db->setQuery($qTaxonomy);
									$db->query();

									$qTaxonomy->clear('values');
									$counter = 0;
								}
							}
						}
					}
				}
			}
		}

		if($counter) {
			$db->setQuery($qTaxonomy);
			$db->query();

			$qTaxonomy->clear('values');
			$counter = 0;
		}

		//UPDATE TAXONOMY MAP
		$qTaxonomy = $db->getQuery(true);
		$qTaxonomy->insert('#__jak2filter_taxonomy_map');
		$qTaxonomy->columns(array($db->quoteName('node_id'), $db->quoteName('item_id'), $db->quoteName('language')));
		$counter = 0;

		$this->truncateTable('#__jak2filter_taxonomy_map');

		//check and get nodes
		$query = "
			SELECT `id`, CONCAT_WS('_', `type`, `asset_id`, `option_id`) AS tkey
			FROM #__jak2filter_taxonomy
			WHERE `type` IN ('category', 'author', 'tag')
			OR (`type` = 'xfield' AND `option_id` <> 0)";
		$db->setQuery($query);
		$taxonomyItems = $db->loadAssocList('tkey', 'id');

		//Searchable label and extra fields is not a select list type
		$query = "
			SELECT `id`, CONCAT_WS('_', `type`, `asset_id`, MD5(`labels`)) AS tkey
			FROM #__jak2filter_taxonomy
			WHERE `type` = 'xfield'
			AND `option_id` = 0";
		$db->setQuery($query);
		$taxonomyItemsLabel = $db->loadAssocList('tkey', 'id');

		if(!count($taxonomyItems) && !count($taxonomyItemsLabel)) {
			return $this->finishIndexingData($context, JText::_('JAK2FILTER_DONE_INDEXING_DATA'));
		}

		//1. Category + Author + Extra Field
		if(!count($k2items)) {
			return $this->finishIndexingData($context, JText::_('JAK2FILTER_DONE_INDEXING_DATA'));
		}

		foreach ($k2items as $item) {
			$_listed = array();

			$tkey = sprintf('%s_%d_%d', 'category', $item->catid, 0);
			if(isset($taxonomyItems[$tkey])) {
				$_value = $db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language);
				if (!in_array($_value, $_listed)) {
					$_listed[] = $_value;
					$qTaxonomy->values($_value);
					$counter++;
				}
			}

			$tkey = sprintf('%s_%d_%d', 'author', $item->created_by, 0);
			if(isset($taxonomyItems[$tkey])) {
				$_value = $db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language);
				if (!in_array($_value, $_listed)) {
					$_listed[] = $_value;
					$qTaxonomy->values($_value);
					$counter++;
				}
			}

			$values = json_decode($item->extra_fields);
			if(is_array($values) && count($values)) {
				foreach ($values as $val) {
					if(is_array($val->value)){
						foreach($val->value as $optid){
							$tkey = sprintf('%s_%d_%d', 'xfield', $val->id, $optid);
							if(isset($taxonomyItems[$tkey])) {
								$_value = $db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language);
								if (!in_array($_value, $_listed)) {
									$_listed[] = $_value;
									$qTaxonomy->values($_value);
									$counter++;
								}
							}
						}
					}else{
						if(in_array($val->id, $aLabels) || in_array($val->id, $aTextfields))
						{
							if(in_array($val->id, $aLabels)) {
								$labels = explode(',', $val->value);
							} else {
								$labels = array($val->value);
							}

							if(count($labels))
							{
								$nodes = array();
								foreach($labels as $label)
								{
									$label = trim($label);
									if(empty($label)) continue;
									$tkey = sprintf('%s_%d_%s', 'xfield', $val->id, md5($label));
									if(isset($taxonomyItemsLabel[$tkey]) && !in_array($taxonomyItemsLabel[$tkey], $nodes)) {
										$_value = $db->quote($taxonomyItemsLabel[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language);
										if (!in_array($_value, $_listed)) {
											$_listed[] = $_value;
											$nodes[] = $taxonomyItemsLabel[$tkey];
											$qTaxonomy->values($_value);
											$counter++;
										}
									}
								}
							}
						}
						else
						{
							$tkey = sprintf('%s_%d_%d', 'xfield', $val->id, $val->value);
							if(isset($taxonomyItems[$tkey])) {
								$_value = $db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language);
								if (!in_array($_value, $_listed)) {
									$_listed[] = $_value;
									$qTaxonomy->values($db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language));
									$counter++;
								}
							}
						}
					}
				}
			}

			if($counter >= $batchProcess) {
				$db->setQuery($qTaxonomy);
				$db->execute();

				$qTaxonomy->clear('values');
				$counter = 0;
			}
		}

		//4. Tag
		$query = "
			SELECT xref.tagID, #__k2_items.id, #__k2_items.language
			FROM #__k2_items
			JOIN #__k2_tags_xref AS xref ON #__k2_items.id = xref.itemID"
			.$whereItem;
		$db->setQuery($query);
		$items = $db->loadObjectList();
		if(count($items)) {
			foreach ($items as $item) {
				$tkey = sprintf('%s_%d_%d', 'tag', $item->tagID, 0);
				if(isset($taxonomyItems[$tkey])) {
					$qTaxonomy->values($db->quote($taxonomyItems[$tkey]) . ', ' . $db->quote($item->id). ', ' . $db->quote($item->language));
					$counter++;
				}

				if($counter >= $batchProcess) {
					$db->setQuery($qTaxonomy);
					$db->execute();

					$qTaxonomy->clear('values');
					$counter = 0;
				}
			}
		}

		if($counter) {
			$db->setQuery($qTaxonomy);
			$db->execute();

			$qTaxonomy->clear('values');
			$counter = 0;
		}

		//UPDATE COUNTER
		$query = "
				UPDATE #__jak2filter_taxonomy
				SET num_items = (
					SELECT COUNT(item_id) FROM #__jak2filter_taxonomy_map
					WHERE #__jak2filter_taxonomy_map.node_id = #__jak2filter_taxonomy.id
				)";
		$db->setQuery($query);
		$db->execute();

		return $this->finishIndexingData($context, JText::_('JAK2FILTER_DONE_INDEXING_DATA'));
	}

	private function finishIndexingData($context, $message) {
		if($context == 'cron') {
			jexit($message);
		} else {
			return $message;
		}
	}

	public function truncateTable($table) {
		$db = JFactory::getDbo();
		try {
			$db->truncateTable($table);
		} catch (Exception $e) {
			//echo 'Caught exception: ',  $e->getMessage(), "\n";
			$query = "DELETE FROM {$table} WHERE 1";
			$db->setQuery($query);
			$db->execute();
		}
	}
}