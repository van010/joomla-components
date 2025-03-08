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

HTMLHelper::_('behavior.modal', 'a.modal', array('fullScreen'=>true, 'onClose'=>'\\function(){ window.location.reload(); }'));

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

<script type="text/javascript">
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
		if(task == 'tool.move_all') {
			if(!confirm('<?php echo Text::_('ALERT_CONFIRM_MOVE_ITEM', true) ?>')) {
				return false;
			}
		}
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
</script>

<form action="<?php echo Route::_('index.php?option=com_jalang&view=tool&layout=movelang&tmpl=component'); ?>" method="post" name="adminForm" id="adminForm" target="ja-translation">
	<?php if (!empty( $this->sidebar)) : ?>
<!-- 	<div id="j-sidebar-container" class="span3">
		<?php echo $this->sidebar; ?>
	</div> -->
	<div id="j-main-container" class="col-md-9 jaleft span9" style="width: 50%">
		<?php else : ?>
		<div id="j-main-container" class="col-md-9 jaleft span9" style="width: 50%">
			<?php endif;?>
			<div class="clearfix"> </div>

			<?php if(($params->get('translator_api_active', 'bing') == 'google' && $params->get('google_browser_api_key', '') == '') || ($params->get('translator_api_active', 'bing') == 'bing' && ($params->get('bing_client_id', '') == ''))): ?>
				<div class="alert alert-danger">
					<?php echo Text::_('ALERT_COMPONENT_SETTING'); ?>
				</div>
			<?php endif; ?>

			<table class="table table-striped adminlist vertical" id="ja-table-form">
				<thead>
				<tr>
					<td class="hidden-phone" colspan="2">
						<h3><?php echo Text::_('MOVE_LANGUAGE') ?></h3>
						<?php echo Text::_('MOVE_LANGUAGE_DESCRIPTION') ?>
					</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<th class="nowrap hidden-phone" style="width: 100px;">
						<?php echo Text::_('MOVE_FROM_LANGUAGE') ?>
						<!--<sup>[?]</sup>-->
					</th>
					<td class="hidden-phone">

						<select name="from_language" class="inputbox">
							<option value=""><?php echo Text::_('SELECT_A_SOURCE_LANGUAGE'); ?></option>
							<?php echo HTMLHelper::_('select.options', HTMLHelper::_('contentlanguage.existing', false, true), 'value', 'text', ''); ?>
						</select>
						<br />
						<?php echo Text::_('MOVE_FROM_LANGUAGE_TAG') ?>
						<br/>
						<input type="text" name="from_language_tag" class="inputbox" size="8" />
					</td>
				</tr>
				<tr>
					<th class="nowrap hidden-phone">
						<?php echo Text::_('MOVE_TO_LANGUAGE') ?>
						<!--<sup>[?]</sup>-->
					</th>
					<td class="hidden-phone">

						<select name="to_language" class="inputbox">
							<option value=""><?php echo Text::_('SELECT_A_DESTINATION_LANGUAGE'); ?></option>
							<?php foreach($languages as $lang): $manifest = json_decode($lang->manifest_cache); ?>
								<option value="<?php echo $lang->element; ?>">
									<?php echo (is_object($manifest) ? $manifest->name : $lang->name).' ('.$lang->element.')'; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th class="nowrap hidden-phone">
						<?php echo Text::_('COMPONENT') ?>
					</th>
					<td class="hidden-phone">
						<?php echo Text::_('WHICH_COMPONENT_WILL_BEING_MOVED_ITEMS') ?>
						<a href="#" onclick="jQuery('#component-list').toggle(300);this.innerHTML=='<?php echo Text::_('JHIDE'); ?>' ? this.innerHTML='<?php echo Text::_('JSHOW'); ?>' : this.innerHTML='<?php echo Text::_('JHIDE'); ?>';" title="<?php echo Text::_('JHIDE'); ?>"><?php echo Text::_('JHIDE'); ?></a>
						<div id="component-list" style="">
							<ol>
								<?php foreach($this->adapters as $itemtype => $props): ?>
									<?php
									$adapter = JalangHelperContent::getInstance($props['name']);
									if(!$adapter || $adapter->table_type != 'native') continue;
									?>
									<li><?php echo $props['title']; ?></li>
								<?php endforeach; ?>
							</ol>
						</div>
					</td>
				</tr>
				<tr class="last">
					<td class="hidden-phone" colspan="2" style="text-align: center;">
						<button class="btn btn-large" onclick="return Joomla.submitbutton('tool.move_all');"><?php echo Text::_('MOVE_ALL'); ?></button>
					</td>
				</tr>
				</tbody>
			</table>

			<input type="hidden" name="itemtype" value="" />
			<input type="hidden" name="task" value="tool.move_all" />
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		
		<div class="span3 col-md-3">
			<fieldset class="adminform">
				<legend><?php echo Text::_('MOVE_LANGUAGE_RESULT'); ?></legend>
				<iframe name="ja-translation" src="about:blank" style="width: 100%; height: 500px;" frameborder="0"></iframe>
			</fieldset>
		</div>
		
		
</form>