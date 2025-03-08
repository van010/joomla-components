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

defined('_JEXEC') or die;


JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if(JalangHelper::isJoomla3x()) {
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
}

JHtml::_('behavior.modal', 'a.modal', array('fullScreen'=>true));

$app		= JFactory::getApplication();
$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jalang&task=articles.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'article-list', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

$numLang = 0;
foreach ($this->languages as $language) {
	if(isset($language->title_native) && ($language->lang_code != $this->mainlanguage)) {
		$numLang ++;
	}
}
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jalang&view=items'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search fltlft">
				<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
				<button type="submit" class="btn"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" onclick="jQuery('#filter_search').val('');this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="filter-select fltrt">
				<select name="itemtype" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('SELECT_ITEM_TYPE'); ?></option>
					<?php echo $this->filterByItemtype; ?>
				</select>
				<select name="mainlanguage" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE'); ?></option>
					<?php echo $this->filterByLanguage; ?>
				</select>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</fieldset>
		<div class="clr"> </div>

		<table class="adminlist" id="article-list">
			<thead>
				<tr>
					<?php foreach ($this->fields as $field => $label): ?>
					<th class="nowrap hidden-phone">
						<?php echo JHtml::_('grid.sort', $label, $field, $listDirn, $listOrder); ?>
					</th>
					<?php endforeach; ?>
					<?php if ($numLang) : ?>
					<?php foreach ($this->languages as $language): ?>
					<?php if(isset($language->title_native) && ($language->lang_code != $this->mainlanguage)): ?>
					<th class="hidden-phone separator">
						<?php echo JHtml::_('image', 'mod_languages/' . $language->image . '.gif',
							$language->title_native,
							array('title' => $language->title),
							true
						); ?>
						<?php echo $language->title_native; ?>
					</th>
					<?php endif; ?>
					<?php endforeach; ?>
					<?php endif;?>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$item->max_ordering = 0; //??
				$ordering   = ($listOrder == 'a.ordering');

				$association = urlencode(base64_encode(json_encode($item->associations)));
				?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="1">
					<?php foreach ($this->fields as $field => $label): ?>
					<?php $field = preg_replace('/^.*?\./', '', $field); ?>
					<td class="hidden-phone">
						<?php
						if(isset($item->{$this->adapter->primarykey}) && $field == $this->adapter->title_field) {
							$linkEdit = JRoute::_('index.php?option=com_jalang&task=item.edit&itemtype='.$this->adapter->table.'&id='.$item->{$this->adapter->primarykey});
							echo sprintf('<a class="modal" rel="{handler: \'iframe\'}" title="" href="%s">'.($item->{$field}).'</a>', $linkEdit);
						} else {
							echo $item->{$field};
						}
						?>
					</td>
					<?php endforeach; ?>
					<?php foreach ($this->languages as $language): ?>
					<?php if(isset($language->title_native) && ($language->lang_code != $this->mainlanguage)): ?>
					<td class="hidden-phone separator">
						<?php
						if(isset($item->associations[$language->lang_code])) {
							$id = $item->associations[$language->lang_code];
							$linkEdit = JRoute::_('index.php?option=com_jalang&task=item.edit&itemtype='.$this->adapter->table.'&id='.$id.'&refid='.$association);

							echo sprintf('<a class="modal" rel="{handler: \'iframe\'}" title="" href="%s">Edit</a>', $linkEdit). ' <span>(ID: '.$id.')</span>';
							//echo 'Item ID: '.$item->associations[$language->lang_code];
						}
						?>
					</td>
					<?php endif; ?>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<table class="adminlist">
			<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
		</table>
		<?php //Load the batch processing form. ?>
		<?php //echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
</form>
