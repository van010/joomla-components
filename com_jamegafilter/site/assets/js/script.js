function initScript() {
	// accordion menu filter critirea
	jQuery('dt.filter-options-title').off().unbind().click(function() {
		// do not use collapse with horizontal.
		if (jQuery('.ja-mg-sidebar').hasClass('sb-horizontal')) return false;
		//collapsed
		if (jQuery(this).hasClass('collapsed')) {
			jQuery(this).removeClass('collapsed');
			jQuery(this).next().slideDown( function() {
				recalc_sticky(jQuery('.sidebar-main'));
			});
		} else {
			jQuery(this).addClass('collapsed');
			jQuery(this).next().slideUp( function() {
				recalc_sticky(jQuery('.sidebar-main'));
			});
		}
		// save to cookie
		var arrTab = new Array();
		jQuery('dt.filter-options-title').each(function(i){
			arrTab[i] = jQuery(this).hasClass('collapsed');
		});
		jQuery.cookie(jQuery('.ja-mg-sidebar').data('mgfilter'), arrTab);
		
	});
	
	// change layout product list.
	jQuery('.jamg-layout-chooser>span').off().unbind().click(function() {
		jQuery('.jamg-layout-chooser>span').removeClass('active');
		jQuery('.jamg-layout-chooser>span[data-layout="'+jQuery(this).attr('data-layout')+'"]').addClass('active');
		jQuery('.ja-products-wrapper.products.wrapper')
			.removeClass('grid products-grid list products-list')
			.addClass(jQuery(this).attr('data-layout')+' products-'+jQuery(this).attr('data-layout'));
		recalc_sticky(jQuery('.sidebar-main'));
		jQuery.event.trigger('jamg-layout-change');
		jamegafilter_default_result_view = jQuery(this).attr('data-layout');
	});

	// default trigger change layout
	jQuery('.jamg-layout-chooser>span').removeClass('active');
	jQuery('.jamg-layout-chooser>span[data-layout="'+jamegafilter_default_result_view+'"]').addClass('active');
	jQuery('.ja-products-wrapper.products.wrapper')
		.removeClass('grid products-grid list products-list')
		.addClass(jamegafilter_default_result_view+' products-'+jamegafilter_default_result_view);

	// trigger collapse critirie
	if (!jQuery.cookie(jQuery('.ja-mg-sidebar').data('mgfilter'))) {
		jQuery.cookie(jQuery('.ja-mg-sidebar').data('mgfilter'), '');
	}

	// do not use collapse with horizontal.
	if (!jQuery('.ja-mg-sidebar').hasClass('sb-horizontal') && jQuery('.ja-mg-sidebar').data('mgfilter')) {
		var data = jQuery.cookie(jQuery('.ja-mg-sidebar').data('mgfilter'));
		arrTab = data.split(',');

		jQuery('dt.filter-options-title').each(function(i){
			if (arrTab[i] == "true") {
				jQuery(this).addClass('collapsed');
				jQuery(this).next().slideUp( function() {
					recalc_sticky(jQuery('.sidebar-main'));
				});
			}
		});
	}
	
	// default order Class
	addFilterWarperClass(undefined);
    jQuery('.ja-megafilter-wrap .ja-toolbar-wrapper select').chosen({
        disable_search_threshold: 10,
        placeholder_text_multiple: Joomla.JText._('COM_JAMEGAFILTER_MULTIPLE_SELECT_PLACEHOLDER')
    });
}

function addFilterWarperClass(ele) {
	if (ele === undefined) {
		ele = jQuery('.sorter-options');
		// in case the site custom remove the sorting.
		if (!ele.length)
			return;
	}

	ele.find('option').each(function(){
		var _class = jQuery(this).attr('value').replace('attr.', '').replace('.frontend_value', '');
		jQuery('.ja-megafilter-wrapper, .ja-megafilter-wrap').removeClass('ja-'+_class);
	});
	var _class = ele.val().replace('attr.', '').replace('.frontend_value', '');
	jQuery('.ja-megafilter-wrapper, .ja-megafilter-wrap').addClass('ja-'+_class);
}

function openShift(obj) {
	jQuery(obj).parent().parent().find('li:hidden').show();
	jQuery(obj).hide();
}

function recalc_sticky(elem) {
	if (typeof elem.stick_in_parent === 'function') {
		elem.trigger('sticky_kit:recalc');
	}
};

/* function ajaxLoad(){
	var urlRoot = Joomla.getOptions('system.paths');
	const $ = jQuery;
	// find filter group radio
	const getRadio = setInterval(() => {
		var radioWraper = $('div.filter-radio');
		var title = radioWraper.find('dt.filter-options-title').first().find('span').first().text();
		if (title.length > 0){
			const urlParams = {
				option: 'com_ajax',
				module: 'jamegafilter',
				format: 'json',
				method: 'loadField',
				title_radio: title.toLowerCase(),
			};
			const paramsString = new URLSearchParams(urlParams).toString();
			const queryString = urlRoot.baseFull + 'index.php?' + paramsString.replace(/%2C/g, ',');
			$.ajax({
				url: queryString,
				type: 'json',
				success: (data) => {
					const allOptions = data.data[0];
					if (typeof allOptions === 'string'){
						var options = JSON.parse(allOptions).options;
						const radioContent = radioWraper.find('dd.filter-options-content').first().find('li');
						const firstRadio = $('li.item.first');
						// object length Object.keys(options).length
						var i = 0;
						for(const [key, radio] of Object.entries(options)){
							radioVal = radio.value;
							const radioInput = $(`input[value="${radioVal}"]`);
							if (radioInput.length === 0) return true;
							var radioLi = radioInput.parent().parent();
							if (i === 0){
								radioLi.insertAfter(firstRadio);
								radioLi.addClass(`item_${i}`);
							}else{
								radioLi.insertAfter($(`li.item_${i-1}`));
								radioLi.addClass(`item_${i}`);
							}
							i++;
						}
					}
				}
			});
		}
	}, 100);

	setTimeout(() => {
		clearInterval(getRadio);
	}, 500);
} */

/* ajaxLoad();
var getRadio = setInterval(() => {
	var radioWraper = jQuery('div.filter-radio');
	const radioContent = radioWraper.find('dd.filter-options-content').first().find('li');
	radioContent.each(function (idx, el){
		var radio = jQuery(el);
		radio.on('click', function (){
			ajaxLoad();
		})
	})
}, 200);

setTimeout(() => {
	clearInterval(getRadio);
}, 600); */