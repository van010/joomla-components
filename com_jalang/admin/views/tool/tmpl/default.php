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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

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

$imgLoading = Uri::root(true) . '/administrator/components/com_jalang/asset/loading.gif';

$params = ComponentHelper::getParams('com_jalang');
$Mclass = '';
if (!version_compare(JVERSION, '4.0', 'ge')) {
    $Mclass = 'modal';
}
echo "<script>const img_loading_path = '$imgLoading';</script>";
?>
<style>
#jaright {float:right;}
#j-main-container.jaleft {float:left;}
</style>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		var form = jQuery('#adminForm');
		if (task == 'tool.translate_all') {
			langto = document.getElementById('langto');
			langcheck = document.getElementsByName("lang_check");
			langtxt = '';
			for (i=0;i<langcheck.length;i++) {
				if (langcheck[i].checked) {
					langtxt += langcheck[i].value+',';
				}
			}
			langto.value = langtxt;
		}
		if(task == 'tool.find') {
			form.removeAttr('target');
		}
		Joomla.submitform(task, document.getElementById('adminForm'));
		const fieldset_adminform = form.find('fieldset.adminform');

		if (fieldset_adminform.length) {
			if (!jQuery('span#com-jalang-translating').length) {
				const spanLoading = jQuery('<span>', {
					id: 'com-jalang-translating',
				}).prependTo(fieldset_adminform);
				const imgLoading = jQuery('<img>', {
					src: img_loading_path,
				});
				spanLoading.text('Translating ');
				spanLoading.append(imgLoading);
			}
		}
	}

	function translate(itemtype) {
		var form = document.getElementById('adminForm');
		form.itemtype.value = itemtype;
		Joomla.submitbutton('tool.translate');
	}

	const wait_translation_done =  setInterval(function (){
	    const translationIfr = jQuery('iframe[name="ja-translation"]');
		const transContent = translationIfr.contents();
		if (transContent.find('p#translation-completed').length) {
			jQuery('#com-jalang-translating').css('display', 'none');
		    clearInterval(wait_translation_done);
		}
	}, 1000);

</script>

<?php echo $this->tabbar; ?>

<form action="<?php echo Route::_('index.php?option=com_jalang&view=tool&tmpl=component'); ?>"
      method="post" name="adminForm" id="adminForm" target="ja-translation">
	  <div id="j-main-container" class="col-md-9 jaleft span9" style="width: 50%">
		<div class="clearfix"> </div>

		<?php if(($params->get('translator_api_active', 'bing') == 'google' && $params->get('google_browser_api_key', '') == '') || ($params->get('translator_api_active', 'bing') == 'bing' && ($params->get('bing_client_id', '') == '' ))): ?>
			<div class="alert alert-danger">
				<?php echo Text::_('ALERT_COMPONENT_SETTING'); ?>
			</div>
		<?php endif; ?>

		<table class="table table-striped adminlist vertical" id="ja-table-form">
			<thead>
			<tr>
				<th class="nowrap hidden-phone" style="width: 100px;">
					<?php echo Text::_('FROM') ?>
					<!--<sup>[?]</sup>-->
				</th>
				<td class="hidden-phone">
					<strong><?php echo $defaultLanguage->name ; ?></strong>
					(<?php echo $defaultLanguage->element ; ?> -
					<a class="<?php echo $Mclass; ?>" rel="{handler: 'iframe'}" href="<?php echo Route::_('index.php?option=com_languages&view=installed&client=0'); ?>" target="_blank" title="<?php echo Text::_('CHANGE'); ?>"><?php echo Text::_('CHANGE'); ?></a>
					)
				</td>
			</tr>
			</thead>
			<tbody>
			<tr>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('TO') ?>
					<!--<sup>[?]</sup>-->
				</th>
				<td class="hidden-phone">
					<p><?php echo Text::_('JA_LANG_WARNING') ?></p>
					<ol>
						<?php foreach($languages as $lang): ?>
							<?php if($lang->element == $defaultLanguage->element) continue; ?>
							<li>
								<?php
								$manifest = json_decode($lang->manifest_cache);
								echo '<label> <input type="checkbox" value="'.$lang->element.'" name="lang_check" />  '.(is_object($manifest) ? $manifest->name : $lang->name).' ('.$lang->element.') </label>';
								?>
							</li>
						<?php endforeach; ?>
					</ol>
          <!--handle modal for J4-->
          <?php if (version_compare(JVERSION, '4.0', 'ge')): ?>
          <?php
          $modalParams = array(
            'height' => 480,
            'width' => 640,
            'bodyHeight' => 80,
            'modalWidth' => 90,
          );
          $modalParams['url'] = Route::_('index.php?option=com_installer&view=languages');
          $modalParams['title'] = Text::_('INSTALL_MORE');
          echo
            '
                <a href="#ModalEditArticle_jform_associations"
                  class="hasTooltip '.$Mclass.'" data-bs-toggle="modal"
                  data-bs-target="#modalInstallLang"> <span class="small">' . $modalParams['title'] . '</span></a>'
            . HTMLHelper::_(
              'bootstrap.renderModal',
              'modalInstallLang',
              $modalParams
            );
          ?>
          <?php else: ?>
					<a class="<?php echo $Mclass; ?>" href="<?php echo Route::_('index.php?option=com_installer&view=languages'); ?>" rel="{handler: 'iframe'}" title="<?php echo Text::_('INSTALL_MORE'); ?>">
						<span class="small"><?php echo Text::_('INSTALL_MORE'); ?></span>
					</a>
          <?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="nowrap hidden-phone">
					<?php echo Text::_('COMPONENT') ?>
				</th>
				<td class="hidden-phone">
					<?php echo Text::_('WHICH_COMPONENT_WILL_BEING_TRANSLATED') ?>
					<a href="#" onclick="jQuery('#component-list').toggle(300);this.innerHTML=='<?php echo Text::_('JSHOW'); ?>' ? this.innerHTML='<?php echo Text::_('JHIDE'); ?>' : this.innerHTML='<?php echo Text::_('JSHOW'); ?>';" title="<?php echo Text::_('JSHOW'); ?>"><?php echo Text::_('JSHOW'); ?></a>
					<div id="component-list" style="display: none;">
						<ol>
							<?php foreach($this->adapters as $itemtype => $props): ?>
							<li><?php echo $props['title']; ?></li>
							<?php endforeach; ?>
						</ol>
					</div>
				</td>
			</tr>
			<tr class="last">
				<td class="hidden-phone" colspan="2" style="text-align: center;">
					<button class="btn btn-large" onclick="Joomla.submitbutton('tool.translate_all');"><?php echo Text::_('TRANSLATE_ALL'); ?></button>
				</td>
			</tr>
			</tbody>
		</table>
		<input type="hidden" name="langto" id="langto" value="" />
		<input type="hidden" name="itemtype" value="" />
		<input type="hidden" name="task" value="tool.translate_all" />
		<input type="hidden" name="boxchecked" value="1" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
    <?php if (JalangHelper::smallerThanJ4x()): ?>
	<div class="col-md-3 span3" style="width: 40%">
    <?php else: ?>
    <div class="col-md-9 span9" style="width: 70%">
    <?php endif; ?>
		<fieldset class="adminform">
			<legend><?php echo Text::_('TRANSLATION_RESULT'); ?></legend>
      <iframe name="ja-translation" class="tran-result-iframe" style="height: 700px"
              src="about:blank" width="100%" frameborder="0"></iframe>
		</fieldset>
	</div>
		
</form>
