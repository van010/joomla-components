<?php
/**
 * ------------------------------------------------------------------------
 * JA Megafilter Component
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2016 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('formbehavior.chosen', 'select');

require_once JPATH_COMPONENT.'/assets/asset.php';
Factory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'default.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};
");
$app = Factory::getApplication();
$typeLists = $this->typeLists;
$item = $this->item;
if (!version_compare(JVERSION, '4', 'ge')){
  $jVer = 'j3';
}else{
  $jVer = 'j4';
}
?>
<form action="<?php echo Route::_('index.php?option=com_jamegafilter&view=default&layout=edit&id=' . (int) $this->item->id); ?>"
		method="post" name="adminForm" id="item-form" class="form-validate <?php echo $jVer ?>">
	<div class="form-horizontal">
		<fieldset class="adminform">
			<div class="row-fluid row-details">
				
				<div class="span12">
					<div class="control-group">
						<div class="control-label"></div>
						<div class="control-legend"><legend><?php echo Text::_('COM_JAMEGAFILTER_DETAILS'); ?></legend></div>
						<div class="controls"></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo Text::_('COM_JAMEGAFILTER_COMPONENT'); ?></div>
						<div class="controls">
              <select class="form-select form-select-color-state form-select-success valid form-control-success" id="jform_jatype" name="jform[jatype]"
                      onChange="window.location.href='<?php echo Route::_('index.php?option=com_jamegafilter&layout=edit&id=' . (int) $this->item->id); ?>&view=default&type='+this.value;">
								<option value="blank"><?php echo Text::_('JSELECT'); ?></option>
								<?php
								if (!empty($typeLists)):
									foreach ($typeLists AS $l):
										echo '<option '.($item->type == $l ? ' selected="selected" ' : '').' value="'.$l.'" >'.ucfirst($l).'</option>';
									endforeach;
								endif;
								?>
							</select>
						</div>
					</div>
					<?php if ($item->type != 'blank'): ?>
            <!--published-->
						<div class="control-group">
							<div class="control-label"><?php echo Text::_('COM_JAMEGAFILTER_PUBLISHED'); ?></div>
							<div class="controls">
								<fieldset id="jform_params_menu_text" class="btn-group btn-group-yesno radio">
                  <input class="btn-check" type="radio" id="jform_params_menu_text0"
                         name="jform[published]" value="1" <?php echo ($item->published==1 ? ' checked="checked"' : '') ?>>
                  <label for="jform_params_menu_text0" class="btn btn-outline-success"><?php echo Text::_('JYES'); ?></label>
                  
                  <input class="btn-check" type="radio" id="jform_params_menu_text1"
                         name="jform[published]" value="0" <?php echo ($item->published==0 ? ' checked="checked"' : '') ?>>
                  <label for="jform_params_menu_text1" class="btn btn-outline-danger"><?php echo Text::_('JNO'); ?></label>
								</fieldset>
							</div>
						</div>
            <!--title *-->
						<div class="control-group">
							<div class="control-label">
								<label for="jform_title">
									<?php echo Text::_('COM_JAMEGAFILTER_TITLE') ?>
									<span class="star">&nbsp;*</span>
								</label>
							</div>
							<div class="controls">
								<input id="jform_title" type="text" class="inputbox form-control" value="<?php echo $item->title; ?>" name="jform[title]" required />
							</div>
						</div>
            <!--root category -> generate thumbnail-->
						<?php if ($this->form && $this->checkComponent ): ?>
							<?php echo $this->form->renderFieldSet('base'); ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			
			<?php if ($this->form && $this->checkComponent && $item->type != 'blank'): ?>
			<div class="row-fluid row-filter-config">
				<div class="control-group">
					<div class="control-label"></div>
					<div class="control-legend"><legend><?php echo Text::_('COM_JAMEGAFILTER_FILTER_CONFIG'); ?></legend></div>
					<div class="controls"></div>
				</div>
        <!--base field, filter config and Page option-->
				<?php foreach ($this->form->getFieldset('filterfields') as $field):?>
					<div class="control-group">
						<div class="config-wrap">
							<?php echo $this->form->getInput($field->fieldname,$field->group,(!empty($item->params[$field->fieldname]) ? $item->params[$field->fieldname] : false)); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" value="<?php echo $item->id; ?>" name="jform[id]" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">
(function(root, $) {
	var drag_i, drop_i, helper, moved;
	$( "#tabs" ).tabs().addClass( "ui-tabs-vertical ui-helper-clearfix" );
	$( "#tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
	$( "#tabs" ).tabs().find( ".ui-tabs-nav" ).sortable({
		handle: ".icon-menu",
		axis: "y",
		start: function (e, ui) {
			drag_i= $(ui.item[0]).index();
		},
		stop: function(e, ui) {
			drop_i = $(ui.item[0]).index();
			if (drop_i !== drag_i) {
				helper = $('<div>', {
					class:'ui-tabs-panel'
				});
				helper.insertAfter($('.ui-tabs-panel:last'));
				moved = $($('.ui-tabs-panel')[drag_i]).detach();
				moved.insertBefore($($('.ui-tabs-panel')[drop_i]));
				helper.remove();
			}
			$( "#tabs" ).tabs( "refresh" );
		}
	});

	$( "tbody" ).sortable({
		axis: "y",
		handle: ".icon-menu"
	});
	
	var updateLayoutInfo = function() {
		var $info = $('#jform_params_layout_addition');
		var layout_addition = [];

		$info.val('');

		$('#sortable2').find('li').each(function() {
			var $li = $(this);
			var field = $li.attr('data-jfield');

			$('input.layout-addition[data-jfield="'+ field +'"]').val(1);
			layout_addition.push(($(this).attr('data-jfield')));
		});

		$info.val(layout_addition.join(','));

		$('#sortable1').find('li').each(function() {
			var $li = $(this);
			var field = $li.attr('data-jfield');

			$('input.layout-addition[data-jfield="'+ field +'"]').val(0);
		});
	}

	var updateFilterConfig = function() {
		var $groups = $('.filter-field');
		var groups = [];
		$groups.each(function() {
			groups.push( $(this).attr('href') );
		})

		var $fields = $( groups.join(','));

		var $filterConfig = $('#filterconfig');

		$fields.find('.icon-publish[data-jfield]').each( function() {
			var field = $(this).attr('data-jfield');
			var $filter = $filterConfig.find('tr[data-jfield="'+field+'"]');

			$filter.show();
		});
		
		$fields.find('.icon-unpublish[data-jfield]').each( function() {
			var field = $(this).attr('data-jfield');
			var $filter = $filterConfig.find('tr[data-jfield="'+field+'"]');

			$filter.hide();
		});

	}

	$(document).ready(function(){
		// this is code for field set radio sort by.
		$('input.sort_by_input').change(function(){
			$('.btn-asc, .btn-desc').removeClass('active btn-success');
			$('.btn-'+$(this).val()).addClass('active btn-success');
		});
		$( "#sortable1, #sortable2" ).sortable({
			items: "li:not(.ui-state-disabled)",
			connectWith: ".connectedSortable",
			placeholder: "ui-sortable-placeholder",
			update: function( event, ui ) {
				updateLayoutInfo();
			}
		}).disableSelection();

		setTimeout( function () {
			updateLayoutInfo();
			updateFilterConfig();
		}, 500);
    var jVer = "<?php echo $jVer ?>";
    if (jVer === 'j4'){
      $('div.chosen-container').css('display', 'none');
      $('select.form-select').css('display', '');
    }
	});


	root.publish_item = function(e) {
		var $input = $(e).next();
		var $ele = $(e);
		var $span;
		
		switch ( $input.val()) {
			case '0':
				$input.val(1);
				$span = $ele.find('span');
				$span.removeClass('icon-unpublish');
				$span.addClass('icon-publish');
				break
			case '1':
				$input.val(0);
				$span = $ele.find('span');
				$span.removeClass('icon-publish');
				$span.addClass('icon-unpublish');
				break;
		}

		updateFilterConfig();
	}

	root.default_sort = function(e) {
		var obj = $(e);
		
		if (obj.children('span').hasClass('icon-publish')) {
			obj.children('span').removeClass('icon-publish').addClass('icon-delete');
			obj.children('input').val(0);
		} else {
			$('.btn-sort_default span').removeClass('icon-publish').addClass('icon-delete');
			$('.sort_default-input').val(0);
			obj.children('span').removeClass('icon-delete').addClass('icon-publish');
			obj.children('input').val(1);
		}
	}
})( window, jQuery);
</script>
