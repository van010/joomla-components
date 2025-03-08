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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar;

defined('_JEXEC') or die;


HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');
if(JalangHelper::isJoomla4x()) {
	HTMLHelper::_('dropdown.init');
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$app		= Factory::getApplication();
$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$archived	= $this->state->get('filter.published') == 2 ? true : false;
$trashed	= $this->state->get('filter.published') == -2 ? true : false;
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jalang&task=articles.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'article-list', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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
  #j-main-container.jaleft {float:left;}
</style>
<script src="<?php echo Uri::root(true); ?>/media/system/js/fields/modal-fields.js"></script>
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
<form action="<?php echo Route::_('index.php?option=com_jalang&view=items'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
<!--
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
 -->
	<div id="j-main-container" class="col-md-12 jaleft span12">
<?php else : ?>
	<div id="j-main-container" class="col-md-12 jaleft span12">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
				<input type="text" name="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo Text::_('JSEARCH_FILTER'); ?>" />
			</div>
			<div class="btn-group pull-left hidden-phone">
				<button class="btn tip hasTooltip" type="submit" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
				<button class="btn tip hasTooltip" type="button" onclick="jQuery('#filter_search').val('');this.form.submit();" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
			</div>
			<div class="filter-search btn-group pull-left">
				<?php echo HTMLHelper::_('select.genericlist', $this->jaoptions, 'itemtype', 'class="inputbox jaselected"', 'value', 'text', $this->jaitemtype); ?>
			</div>
			<div class="filter-search btn-group pull-left">
				<?php echo HTMLHelper::_('select.genericlist', $this->jacontentlang, 'mainlanguage', 'class="inputbox jaselected"', 'value', 'text', $this->jamainlanguage); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo Text::_('JGLOBAL_ORDER_DESCENDING');  ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo Text::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo Text::_('JGLOBAL_SORT_BY');?></option>
					<?php echo HTMLHelper::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>

		<table class="table table-striped" id="article-list">
			<thead>
				<tr>
					<?php foreach ($this->fields as $field => $label): ?>
					<th class="nowrap hidden-phone">
						<?php echo HTMLHelper::_('grid.sort', $label, $field, $listDirn, $listOrder); ?>
					</th>
					<?php endforeach; ?>
					<?php if ($numLang) : ?>
					<?php foreach ($this->languages as $language): ?>
					<?php if(isset($language->title_native) && ($language->lang_code != $this->mainlanguage)): ?>
					<th class="hidden-phone separator">
						<?php echo HTMLHelper::_('image', 'mod_languages/' . $language->image . '.gif',
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
							$linkEdit = Route::_('index.php?option=com_jalang&task=item.edit&itemtype='
                .$this->adapter->table.'&id='.$item->{$this->adapter->primarykey});
              
              $modalParams = array(
                'height' => 480,
                'width' => 640,
                'bodyHeight' => 80,
                'modalWidth' => 90,
              );
              $modalParams['url'] = $linkEdit;
              $modalParams['title'] = $item->{$field};
              echo
                '<input class="form-control" id="jform_associations_'.$language->lang_code.'_name" type="hidden" value="" disabled="disabled" size="35">
                <a href="#ModalEditArticle_jform_associations_'.$language->lang_code.'_'.$item->id.'"
                  class="hasTooltip" data-bs-toggle="modal"
                  data-bs-target="#myModal-'. $item->id .'">' . $item->{$field} . '</a>'
                . HTMLHelper::_(
                  'bootstrap.renderModal',
                  'myModal-'. $item->id,
                  $modalParams
                ) .'
              <div id="ModalEditArticle_jform_associations_'.$language->lang_code.'_'.$item->id.'" role="dialog" tabindex="-1"
                  class="joomla-modal modal fade" data-backdrop="static" data-keyboard="false"
                  data-url="'.$linkEdit.'"
                  data-iframe="<iframe class=&quot;iframe&quot; src=&quot;'.$linkEdit.'&quot; name=&quot;'.Text::_('COM_JALANG_EDIT_ARTICLE').'&quot; height=&quot;400px&quot; width=&quot;800px&quot;></iframe>">
                <div class="modal-dialog modal-lg jviewport-width80" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                    <h3 class="modal-title">'.Text::_('COM_JALANG_EDIT_ARTICLE').'</h3>
                  </div>
                <div class="modal-body jviewport-height70">
                  </div>
                      <div class="modal-footer">
                        <a role="button" class="btn btn-secondary" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'cancel\', \'item-form\'); return false;">'.Text::_('JCANCEL').'</a>
                        <a role="button" class="btn btn-primary" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'save\', \'item-form\'); return false;">'.Text::_('JSAVE').'</a>
                        <a role="button" class="btn btn-success" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'apply\', \'item-form\'); return false;">'.Text::_('JAPPLY').'</a>
                      </div>
                    </div>
                  </div>
                </div>
              <input type="hidden" id="jform_associations_'.$language->lang_code.'_id" data-required="0" name="jform[associations]['.$language->lang_code.']" data-text="Select an Article" value="'.$item->id.'">
              ';
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
							$linkEdit = Route::_('index.php?option=com_jalang&task=item.edit&itemtype='.$this->adapter->table.'&id='.$id.'&refid='.$association);
              
              $modalEdit = array(
                'height' => 480,
                'width' => 640,
                'bodyHeight' => 80,
                'modalWidth' => 90,
              );
              $modalEdit['url'] = $linkEdit;

              echo '
              <input class="form-control" id="jform_associations_'.$language->lang_code.'_name" type="hidden" value="" disabled="disabled" size="35">
              <a href="#ModalEditArticle_jform_associations_'.$language->lang_code.'_'.$id.'"
              class="btn btn-secondary hasTooltip" data-bs-toggle="modal"
              data-bs-target="#myEditModal-'. $id .'"
              id="jform_associations_'.$language->lang_code.'_edit"
              role="button"
              title="'.Text::_('COM_JALANG_EDIT_ARTICLE').'">
              <span class="icon-edit" aria-hidden="true"></span>'. Text::_("COM_JALANG_EDIT") .'</a>'
                . HTMLHelper::_(
                  'bootstrap.renderModal',
                  'myEditModal-'. $id,
                  $modalEdit
                ) . '
              
              <div id="ModalEditArticle_jform_associations_'.$language->lang_code.'_'.$id.'" role="dialog" tabindex="-1"
              class="joomla-modal modal fade" data-backdrop="static" data-keyboard="false"
              data-url="'.$linkEdit.'"
              data-iframe="<iframe class=&quot;iframe&quot; src=&quot;'.$linkEdit.'&quot; name=&quot;'.Text::_('COM_JALANG_EDIT_ARTICLE').'&quot; height=&quot;400px&quot; width=&quot;800px&quot;></iframe>">
                <div class="modal-dialog modal-lg jviewport-width80" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                    <h3 class="modal-title">'.Text::_('COM_JALANG_EDIT_ARTICLE').'</h3>
                  </div>
                <div class="modal-body jviewport-height70">
                  </div>
                      <div class="modal-footer">
                        <a role="button" class="btn btn-secondary" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'cancel\', \'item-form\'); return false;">'.Text::_('JCANCEL').'</a>
                        <a role="button" class="btn btn-primary" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'save\', \'item-form\'); return false;">'.Text::_('JSAVE').'</a>
                        <a role="button" class="btn btn-success" aria-hidden="true" onclick="window.processModalEdit(this, \'jform_associations_'.$language->lang_code.'\', \'edit\', \'article\', \'apply\', \'item-form\'); return false;">'.Text::_('JAPPLY').'</a>
                      </div>
                    </div>
                  </div>
                </div>
              <input type="hidden" id="jform_associations_'.$language->lang_code.'_id" data-required="0" name="jform[associations]['.$language->lang_code.']" data-text="Select an Article" value="'.$id.'">
              ';
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
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>

<script>
$(document).ready(function(){
	$('.editItem').on('click', function(e){
		e.preventDefault();
		$('#EditItemModal').attr('data-iframe', $(this).attr('data-linkedit'));
		$('#EditItemModal').modal('show');
		return false;
	});
});
</script>