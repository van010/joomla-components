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
$assoc		= isset($app->item_associations) ? $app->item_associations : 0;

$numLang = 0;
foreach ($this->languages as $language) {
	if(isset($language->title_native) && ($language->lang_code != $this->mainlanguage)) {
		$numLang ++;
	}
}
?>
<style>
  #jaright {float:right;}
  #j-main-container2.jaleft {float:left;}
</style>
<script type="text/javascript">
	jQuery(document).ready(function () {
		jQuery('.jaselected').change(function(){
			document.adminForm.submit();
		});
	});
	Joomla.submitbutton = function(task)
	{
		if (task == 'tool.transview') {
			window.location.href="index.php?option=com_jalang&view=tool";
		}
		if (task == 'tool.assview') {
			window.location.href="index.php?option=com_jalang&view=items";
		}
		if (task == 'tool.delview') {
			window.location.href="index.php?option=com_jalang&view=tool&layout=removelang";
		}
	}
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
<?php echo $this->tabbar; ?>
<form action="<?php echo JRoute::_('index.php?option=com_jalang&view=items'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
<!-- 
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
 -->
	<div id="j-main-container2" class="col-md-12 span12 jaleft">
<?php else : ?>
	<div id="j-main-container2" class="col-md-12 span12 jaleft">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
				<input type="text" name="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="jQuery('#filter_search').val('');this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="filter-search btn-group pull-left">
				<?php echo JHTML::_('select.genericlist', $this->jaoptions, 'itemtype', 'class="inputbox jaselected"', 'value', 'text', $this->jaitemtype); ?>
			</div>
			<div class="filter-search btn-group pull-left">
				<?php echo JHTML::_('select.genericlist', $this->jacontentlang, 'mainlanguage', 'class="inputbox jaselected"', 'value', 'text', $this->jamainlanguage); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="article-list">
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
		<?php echo $this->pagination->getListFooter(); ?>
		<?php //Load the batch processing form. ?>
		<?php //echo $this->loadTemplate('batch'); ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
