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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

defined('_JEXEC') or die;

HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers/html');

if(JalangHelper::isJoomla3x()) {
	HTMLHelper::_('bootstrap.tooltip');
	HTMLHelper::_('behavior.multiselect');
	HTMLHelper::_('dropdown.init');
	HTMLHelper::_('formbehavior.chosen', 'select');
}

if (!version_compare(JVERSION, '4', 'ge')){
  HTMLHelper::_('behavior.modal', 'a.modal', array('fullScreen'=>true, 'onClose'=>'\\function(){ window.location.reload(); }'));
}

$app		= Factory::getApplication();
$user		= Factory::getUser();
$userId		= $user->get('id');

$languages = JalangHelper::getListInstalledLanguages();

$defaultLanguage = JalangHelper::getLanguage();

$params = ComponentHelper::getParams('com_jalang');

$input = Factory::getApplication()->input;
if($input->get('debug', 0)) {
	$lang = $input->get('lang', '');
	if($lang) {
		$db = Factory::getDbo();
		$query = "SELECT language FROM #__content WHERE ".$db->quoteName('alias')." LIKE '%-{$lang}'";
		$db->setQuery($query);
		$langtag = $db->loadResult();
	}
}

?>
<style>
  #jaright {float:right;}
  #j-main-container.jaleft {float:left;}
</style>

<script type="text/javascript">
	function removelang(lang) {
		if(!confirm('<?php echo Text::_('ALERT_CONFIRM_REMOVE_LANGUAGE', true) ?>')) {
			return false;
		}
		var form = document.getElementById('adminForm');
		form.lang_remove.value = lang;
		Joomla.submitbutton('tool.remove_language');
	}
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
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
</script>

<?php echo $this->tabbar; ?>

<form action="<?php echo Route::_('index.php?option=com_jalang&view=tool&layout=movelang&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm" target="ja-translation">
	<?php if (!empty( $this->sidebar)) : ?>
<!--
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
 -->
	<div id="j-main-container" class="col-md-9 jaleft span9" style="width: 50%">
		<?php else : ?>
		<div id="j-main-container" class="col-md-9 jaleft span9" style="width: 50%">
			<?php endif;?>
			<div class="clearfix"> </div>

			<h3><?php echo Text::_('DELETE_LANGUAGE_CONTENT') ?></h3>
			<div class="warning">
				<?php echo Text::_('DELETE_LANGUAGE_DESCRIPTION') ?>
				<br/>
				<strong><?php echo Text::_('COMPONENT').':'; ?></strong><br/>
				<?php echo Text::_('WHICH_COMPONENT_WILL_BEING_REMOVED_ITEMS') ?>
				 <a href="#" onclick="jQuery('#component-list').toggle(300);this.innerHTML=='<?php echo Text::_('JSHOW'); ?>' ? this.innerHTML='<?php echo Text::_('JHIDE'); ?>' : this.innerHTML='<?php echo Text::_('JSHOW'); ?>';" title="<?php echo Text::_('JSHOW'); ?>"><?php echo Text::_('JSHOW'); ?></a>
				<div id="component-list" style="display: none">
					<ol>
						<?php foreach($this->adapters as $itemtype => $props): ?>
							<li><?php echo $props['title']; ?></li>
						<?php endforeach; ?>
					</ol>
				</div>
			</div>

			<table class="table table-striped adminlist vertical" id="ja-table-form">
				<thead>
				<tr>
					<th class="hidden-phone"><?php echo Text::_('LANGUAGE'); ?></th>
					<th class="hidden-phone">&nbsp;</th>
				</tr>
				</thead>
				<tbody>

				<?php foreach($languages as $lang): ?>
					<?php
					$manifest = json_decode($lang->manifest_cache);
					if($lang->element == $defaultLanguage->element) continue;
					?>
					<tr>
						<td class="nowrap hidden-phone">
							<?php echo (is_object($manifest) ? $manifest->name : $lang->name).' ('.$lang->element.')'; ?>
						</td>
						<td class="hidden-phone">
							<button class="btn btn-sm btn-small" onclick="return removelang('<?php echo $lang->element; ?>');"><?php echo Text::_('JALANG_ACTION_REMOVE'); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>

				</tbody>
			</table>

			<input type="hidden" name="lang_remove" value="" />
			<input type="hidden" name="task" value="tool.move_all" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
        <?php if (JalangHelper::smallerThanJ4x()): ?>
	    <div class="col-md-3 span3" style="width: 40%">
        <?php else: ?>
        <div class="col-md-6 span6">
        <?php endif; ?>
			<fieldset class="adminform">
				<legend><?php echo Text::_('MOVE_LANGUAGE_RESULT'); ?></legend>
        <iframe name="ja-translation" class="tran-result-iframe"
                src="about:blank" height="650px"
                frameborder="0"></iframe>
			</fieldset>
		</div>
		
		
</form>