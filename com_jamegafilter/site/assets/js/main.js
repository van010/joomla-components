// Minified version of isMobile included in the HTML since it's small
!function (a) { var b = /iPhone/i, c = /iPod/i, d = /iPad/i, e = /(?=.*\bAndroid\b)(?=.*\bMobile\b)/i, f = /Android/i, g = /(?=.*\bAndroid\b)(?=.*\bSD4930UR\b)/i, h = /(?=.*\bAndroid\b)(?=.*\b(?:KFOT|KFTT|KFJWI|KFJWA|KFSOWI|KFTHWI|KFTHWA|KFAPWI|KFAPWA|KFARWI|KFASWI|KFSAWI|KFSAWA)\b)/i, i = /IEMobile/i, j = /(?=.*\bWindows\b)(?=.*\bARM\b)/i, k = /BlackBerry/i, l = /BB10/i, m = /Opera Mini/i, n = /(CriOS|Chrome)(?=.*\bMobile\b)/i, o = /(?=.*\bFirefox\b)(?=.*\bMobile\b)/i, p = new RegExp("(?:Nexus 7|BNTV250|Kindle Fire|Silk|GT-P1000)", "i"), q = function (a, b) { return a.test(b) }, r = function (a) { var r = a || navigator.userAgent, s = r.split("[FBAN"); return "undefined" != typeof s[1] && (r = s[0]), s = r.split("Twitter"), "undefined" != typeof s[1] && (r = s[0]), this.apple = { phone: q(b, r), ipod: q(c, r), tablet: !q(b, r) && q(d, r), device: q(b, r) || q(c, r) || q(d, r) }, this.amazon = { phone: q(g, r), tablet: !q(g, r) && q(h, r), device: q(g, r) || q(h, r) }, this.android = { phone: q(g, r) || q(e, r), tablet: !q(g, r) && !q(e, r) && (q(h, r) || q(f, r)), device: q(g, r) || q(h, r) || q(e, r) || q(f, r) }, this.windows = { phone: q(i, r), tablet: q(j, r), device: q(i, r) || q(j, r) }, this.other = { blackberry: q(k, r), blackberry10: q(l, r), opera: q(m, r), firefox: q(o, r), chrome: q(n, r), device: q(k, r) || q(l, r) || q(m, r) || q(o, r) || q(n, r) }, this.seven_inch = q(p, r), this.any = this.apple.device || this.android.device || this.windows.device || this.other.device || this.seven_inch, this.phone = this.apple.phone || this.android.phone || this.windows.phone, this.tablet = this.apple.tablet || this.android.tablet || this.windows.tablet, "undefined" == typeof window ? this : void 0 }, s = function () { var a = new r; return a.Class = r, a }; "undefined" != typeof module && module.exports && "undefined" == typeof window ? module.exports = r : "undefined" != typeof module && module.exports && "undefined" != typeof window ? module.exports = s() : "function" == typeof define && define.amd ? define("isMobile", [], a.isMobile = s()) : a.isMobile = s() }(this);
function utf8_to_b64(str) {
	return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
		return String.fromCharCode('0x' + p1);
	}));
}
function exeUTF8(str) {
	$str = '';
	for (var i = 0; i < str.length; i++) {
		if (str.charCodeAt(i) > 127) $str += utf8_to_b64(str[i]);
		else $str += (str[i]);
	}
	return $str.replace(/\=/g, "");
}

var JAnameColor = { "aliceblue": "#f0f8ff", "antiquewhite": "#faebd7", "aqua": "#00ffff", "aquamarine": "#7fffd4", "azure": "#f0ffff", "beige": "#f5f5dc", "bisque": "#ffe4c4", "black": "#000000", "blanchedalmond": "#ffebcd", "blue": "#0000ff", "blueviolet": "#8a2be2", "brown": "#a52a2a", "burlywood": "#deb887", "cadetblue": "#5f9ea0", "chartreuse": "#7fff00", "chocolate": "#d2691e", "coral": "#ff7f50", "cornflowerblue": "#6495ed", "cornsilk": "#fff8dc", "crimson": "#dc143c", "cyan": "#00ffff", "darkblue": "#00008b", "darkcyan": "#008b8b", "darkgoldenrod": "#b8860b", "darkgray": "#a9a9a9", "darkgrey": "#a9a9a9", "darkgreen": "#006400", "darkkhaki": "#bdb76b", "darkmagenta": "#8b008b", "darkolivegreen": "#556b2f", "darkorange": "#ff8c00", "darkorchid": "#9932cc", "darkred": "#8b0000", "darksalmon": "#e9967a", "darkseagreen": "#8fbc8f", "darkslateblue": "#483d8b", "darkslategray": "#2f4f4f", "darkslategrey": "#2f4f4f", "darkturquoise": "#00ced1", "darkviolet": "#9400d3", "deeppink": "#ff1493", "deepskyblue": "#00bfff", "dimgray": "#696969", "dimgrey": "#696969", "dodgerblue": "#1e90ff", "firebrick": "#b22222", "floralwhite": "#fffaf0", "forestgreen": "#228b22", "fuchsia": "#ff00ff", "gainsboro": "#dcdcdc", "ghostwhite": "#f8f8ff", "gold": "#ffd700", "goldenrod": "#daa520", "gray": "#808080", "grey": "#808080", "green": "#008000", "greenyellow": "#adff2f", "honeydew": "#f0fff0", "hotpink": "#ff69b4", "indianred ": "#cd5c5c", "indigo  ": "#4b0082", "ivory": "#fffff0", "khaki": "#f0e68c", "lavender": "#e6e6fa", "lavenderblush": "#fff0f5", "lawngreen": "#7cfc00", "lemonchiffon": "#fffacd", "lightblue": "#add8e6", "lightcoral": "#f08080", "lightcyan": "#e0ffff", "lightgoldenrodyellow": "#fafad2", "lightgray": "#d3d3d3", "lightgrey": "#d3d3d3", "lightgreen": "#90ee90", "lightpink": "#ffb6c1", "lightsalmon": "#ffa07a", "lightseagreen": "#20b2aa", "lightskyblue": "#87cefa", "lightslategray": "#778899", "lightslategrey": "#778899", "lightsteelblue": "#b0c4de", "lightyellow": "#ffffe0", "lime": "#00ff00", "limegreen": "#32cd32", "linen": "#faf0e6", "magenta": "#ff00ff", "maroon": "#800000", "mediumaquamarine": "#66cdaa", "mediumblue": "#0000cd", "mediumorchid": "#ba55d3", "mediumpurple": "#9370db", "mediumseagreen": "#3cb371", "mediumslateblue": "#7b68ee", "mediumspringgreen": "#00fa9a", "mediumturquoise": "#48d1cc", "mediumvioletred": "#c71585", "midnightblue": "#191970", "mintcream": "#f5fffa", "mistyrose": "#ffe4e1", "moccasin": "#ffe4b5", "navajowhite": "#ffdead", "navy": "#000080", "oldlace": "#fdf5e6", "olive": "#808000", "olivedrab": "#6b8e23", "orange": "#ffa500", "orangered": "#ff4500", "orchid": "#da70d6", "palegoldenrod": "#eee8aa", "palegreen": "#98fb98", "paleturquoise": "#afeeee", "palevioletred": "#db7093", "papayawhip": "#ffefd5", "peachpuff": "#ffdab9", "peru": "#cd853f", "pink": "#ffc0cb", "plum": "#dda0dd", "powderblue": "#b0e0e6", "purple": "#800080", "rebeccapurple": "#663399", "red": "#ff0000", "rosybrown": "#bc8f8f", "royalblue": "#4169e1", "saddlebrown": "#8b4513", "salmon": "#fa8072", "sandybrown": "#f4a460", "seagreen": "#2e8b57", "seashell": "#fff5ee", "sienna": "#a0522d", "silver": "#c0c0c0", "skyblue": "#87ceeb", "slateblue": "#6a5acd", "slategray": "#708090", "slategrey": "#708090", "snow": "#fffafa", "springgreen": "#00ff7f", "steelblue": "#4682b4", "tan": "#d2b48c", "teal": "#008080", "thistle": "#d8bfd8", "tomato": "#ff6347", "turquoise": "#40e0d0", "violet": "#ee82ee", "wheat": "#f5deb3", "white": "#ffffff", "whitesmoke": "#f5f5f5", "yellow": "#ffff00", "yellowgreen": "#9acd32" };

(function ($) {

	// Upper first letter
	String.prototype.ucfirst = function () {
		return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase();
	}    // Upper first letter
	String.prototype.slugify = function () {
		return this.toString().toLowerCase()
			.replace(/\s+/g, '-')           // Replace spaces with -
			.replace(/[^\w\-]+/g, '')       // Remove all non-word chars
			.replace(/\-\-+/g, '-')         // Replace multiple - with single -
			.replace(/^-+/, '')             // Trim - from start of text
			.replace(/-+$/, '');            // Trim - from end of text
	}

	// Register iter helper for dustjs
	// Make a loop for object properties
	dust.helpers.iter = function (chunk, context, bodies) {
		var items = context.current(), //this gets the current context hash from the Context object (which has a bunch of other attributes defined in it)
			ctx;

		for (var key in items) {
			ctx = { "key": key, "value": items[key] };
			chunk = chunk.render(bodies.block, context.push(ctx));
		}

		return chunk;
	}

  dust.helpers.showClearAll = function(chunk, context){
    if (Object.keys(context.stack.head).length !== 0){
      return true;
    }
    return false;
  }

	dust.helpers.info = function (chunk, context, bodies) {
		var item = context.current();
		if (item.attr.custom_fields !== undefined){
			var ja_customfield_class_render = item.attr.custom_fields;
		}
		var obj = item.attr;
		if (typeof ja_layout_addition === 'string') {
			$ja_layout_add = ja_layout_addition.split(',');
			for (var $i = 0; $i < $ja_layout_add.length; $i++) {
				// for the key "key" in ctx variable. include if you want 2 columns in layout.
				var $jakey = $ja_layout_add[$i].replace('attr.', '').replace('.value', '');
				var render_class = "";
				var label_render_class = "";

				// this line support for custom field class
				// must override then render in template first to use.
				if (typeof ja_customfield_class_render !== "undefined") {
					if (typeof ja_customfield_class_render[$jakey] !== "undefined") {
						if (typeof ja_customfield_class_render[$jakey]['params'] !== "undefined") {
							if (typeof ja_customfield_class_render[$jakey]['params']['render_class'] !== "undefined"){
								render_class = ja_customfield_class_render[$jakey]['params']['render_class'];
							}
							if (typeof ja_customfield_class_render[$jakey]['params']['label_render_class'] !== "undefined"){
								label_render_class = ja_customfield_class_render[$jakey]['params']['label_render_class'];
							}
							if (typeof ja_customfield_class_render[$jakey]['params']['value_render_class'] !== "undefined"){
								label_render_class = ja_customfield_class_render[$jakey]['params']['value_render_class'];
							}
						}
					}
				}

				// custom layout input for thumb|desc. put something to "value" if all layout template same markup html.
				if ($ja_layout_add[$i] == 'thumb') {
					var ctx = { "_class": $jakey, "value": item.desc == undefined ? '' : item.desc, "render_class": render_class, "label_render_class": label_render_class };
					if (typeof ja_layout_columns[$ja_layout_add[$i]] != 'undefined' && ja_layout_columns[$ja_layout_add[$i]] == 1)
						ctx.key = jamegafilter_thumb;
					chunk = chunk.render(bodies.block, context.push(ctx));
					continue;
				}
				if ($ja_layout_add[$i] == 'desc') {
					var ctx = { "_class": $jakey, "value": item.desc == undefined ? '' : item.desc, "render_class": render_class, "label_render_class": label_render_class };
					if (typeof ja_layout_columns[$ja_layout_add[$i]] != 'undefined' && ja_layout_columns[$ja_layout_add[$i]] == 1)
						ctx.key = jamegafilter_desc;
					chunk = chunk.render(bodies.block, context.push(ctx));
					continue;
				}
				if ($ja_layout_add[$i] == 'baseprice') {
					var ctx = { "_class": $jakey, "value": item.baseprice == undefined ? '' : item.baseprice, "render_class": render_class, "label_render_class": label_render_class };
					if (typeof ja_layout_columns[$ja_layout_add[$i]] != 'undefined' && ja_layout_columns[$ja_layout_add[$i]] == 1)
						ctx.key = jamegafilter_baseprice;
					chunk = chunk.render(bodies.block, context.push(ctx));
					continue;
				}

				for (var key in obj) {
					var value = obj[key];
					if (key !== $jakey) continue;
					if (typeof value.type === 'undefined') continue;
					var _value;
					var _val_class = "";

					// custom layout input for name. put something to "value" if all layout template same markup html.
					if ($ja_layout_add[$i] == 'name') {
						var ctx = { "_class": $jakey, "value": value.frontend_value, "render_class": render_class, "label_render_class": label_render_class };
						if (typeof ja_layout_columns[$ja_layout_add[$i]] != 'undefined' && ja_layout_columns[$ja_layout_add[$i]] == 1) ctx.key = value.title;
						chunk = chunk.render(bodies.block, context.push(ctx));
						continue;
					}

					if (dust.isArray(value.frontend_value)) {

						if (value.type == 'date') {
							_date = new Date(value.frontend_value[0] * 1000);
							_value = _date.getFullYear() + '-' + (_date.getMonth() + 1) + '-' + _date.getDate();
						} else if (value.type == 'color') {
							_value = [];
							for (var i = 0; i < value.frontend_value.length; i++) {
								var color = value.frontend_value[i].toLowerCase();
								if (JAnameColor.hasOwnProperty(color)) {
									color = JAnameColor[color];
								}
								var span = '<span class="color-item-bg" style="background-color: ' + color + '; width: 24px; height: 20px; display: inline-block; box-shadow: 0 0 5px rgba(0, 0, 0, 0.65)"></span></span>';
								_value.push(span);
							}
							_value = _value.join(' ');
						} else if (value.type == 'media') {
							_value = [];
							for (var i = 0; i < value.frontend_value.length; i++) {
								if (/^(http|https):\/\//.test(value.frontend_value[i])) {
									_value.push('<img src="'+ value.frontend_value[i] + '" />');
								} else {
									_value.push('<img src="' + JABaseUrl + '/' + value.frontend_value[i] + '" />');
								}
							}
							_value = _value.join(' ');
						} else {
							// make sure the replace do not replace other value.
							// only apply for category. we need to clear the category tree.
							_value = [];
							for (var i = 0; i < value.frontend_value.length; i++) {
								_value.push(value.frontend_value[i].replace(/(.*?)\&raquo\; /g, '').replace(/(.*?)» /g, ''));
							}
							_value = _value.join(', ');
							if (value['value'] && value['value'].length === 1) {
								_val_class = value['value'].join(" "); // support for something like badge icon.
							}

							_val_class = _val_class.replace(/[0-9 ]+/, "");
						}
					} else {
						if (key == 'rating') {
							_value = [
								'<div class="rating-summary">',
								'<div title="{rating} out of 5" class="rating-result">',
								'<span style="width:' + value.frontend_value + '%"></span>',
								'</div>',
								'</div>'
							].join('');
						} else {
							_value = value.frontend_value;
						}

					}
					// include "key" if 2 columns.
					var ctx = {
						"_class": $jakey,
						"value": _value,
						"val_class": _val_class,
						"render_class": render_class,
						"label_render_class": label_render_class
					};

					if (typeof ja_layout_columns[$ja_layout_add[$i]] !== 'undefined' && ja_layout_columns[$ja_layout_add[$i]] == 1) {
						ctx.key = value.title;
					}

					chunk = chunk.render(bodies.block, context.push(ctx));
				}
			}
		} else {

		}

		return chunk;
	};
	// if helper
	dust.helpers.if = function (chunk, context, bodies, params) {
		if (!params.value || params.value == params.is) {
			chunk = chunk.render(bodies.block, context);
		} else if (bodies.else) {
			chunk = chunk.render(bodies.else, context);
		}
		return chunk
	}
	
	// truncate text
	dust.helpers.Truncate = function(chunk, context, bodies, params){
		var data = dust.helpers.tap(params.data, chunk, context);
		var length = dust.helpers.tap(params.length, chunk, context);
		return chunk.write(data.substr(0, length));
	}
	
	// translate helper
	dust.helpers.__ = function (chunk, context, bodies, params) {
		chunk.write($.mage.__(params.t));
		return chunk
	}
	dust.filters.__ = function (value) {
		return $.mage.__(value);
	};

	var UBLN = {}, lnmain, lnfilter, lntoolbar, lnselected;

	UBLN.main = function (config) {
		// create main item
		lnmain = new LNBase({
			template: 'product-list',
			class: 'lnfilter-wrapper',
			container: '.main-content',
			itemWrapper: '.product-items',
			itemClass: 'ln-item',
			autopage: config.autopage,
			afterRender: function () {
				lntoolbar.render();
			},
			afterUpdateRender: function () {
				// update toolbar
				lntoolbar.updateRender();
				if (lnfilter.options.sticky) {
					lnfilter.sticky()
				}

			}
		});
		lnfilter = new LNFilter({
			container: '.sidebar-content',
			sticky: config.sticky,
			afterRender: function () {
				lnselected.render();
				if (lntoolbar.rendered) UBLN.load();
			}
		});
		
		lnselected = new LNSelected({
			container: '.filter-values'
		});
		lntoolbar = new LNToolbar({
			container: '.toolbar-wrapper',
			config: config,
			afterRender: function () {
				if (lnfilter.rendered) UBLN.load();
			}
		});
		// default items per page
		lnmain.itemPerPage = lntoolbar.itemPerPage;
		lnfilter.addItems(lnmain);
		lnfilter.addFilterResult(lnselected);

		// add field value data
		lnfilter.addFieldValues(config.customFieldVal);

		lntoolbar.addItems(lnmain);

		lnmain.render();

		// global for debug
		$.lnmain = lnmain;
		$.lnfilter = lnfilter;
		$.lnselected = lnselected;
		var progessBar = [
			'<div id="mg-myProgress">',
			'<div id="mg-loader"></div>',
			'<div id="mg-percentBar">',
			Joomla.JText._('COM_JAMEGAFILTER_LOADING') + ' <span id="mg-perComplete"></span>',
			'</div>',
			'<div id="mg-myBar"></div>',
			'</div>'
		].join('');
		lnmain.$wrapper.append(progessBar);
		$('.ja-megafilter-wrap').addClass('mg-loading_layer');

		if (config.dataUrl) {
			$.ajax({
				xhr: function () {
					var xhr = new window.XMLHttpRequest();

					// Download progress
					xhr.addEventListener("progress", function (evt) {
						if (evt.lengthComputable) {
							var percentComplete = Math.round(evt.loaded / evt.total * 100);
							// Do something with download progress
							lnmain.$wrapper.find('#mg-myBar').css('width', percentComplete + '%');
							lnmain.$wrapper.find('#mg-perComplete').text(percentComplete + '%');
						}
					}, false);

					return xhr;
				},
				type: 'GET',
				url: config.dataUrl,
				dataType: 'json',
				data: {},
				success: function (data) {
					lnmain.$wrapper.find('#mg-myProgress').remove();
					$('.ja-megafilter-wrap').removeClass('mg-loading_layer');
					var ids = Object.keys(data);
					if (!ids.length) return;
					for (var id in data) {
						if (afterGetData(data[id])) {
							continue;
						}

						var itemdata = data[id],
							item = new LNItem({
								template: 'product-item'
							});

						if (itemdata.url) {
							itemdata.url = Joomla.getOptions('system.paths').root + itemdata.url;
						}

						if (itemdata.url_download) {
							itemdata.url_download = Joomla.getOptions('system.paths').root + itemdata.url_download;
						}

						item.setData(itemdata);
						lnmain.addItem(item, 'k' + itemdata.id);
					}

					// Build filter list
					if ($.isArray(config.fields)) {
						config.fields.forEach(function (field) {
							var type = field.type.ucfirst();
							if (field.field == 'title') type = 'Value';
							var func = 'addFilter' + type;

							if (lnfilter[func]) {
								lnfilter[func](field);
							}
						})
					}

					lnselected.setFilter(lnfilter);

					// update render
					lnfilter.updateFilter().render();

					// add effect toggle filter on touch device.
					let jabackdrop = jQuery('.sidebar-toggle-backdrop');
					// if the layout contain the div with class. the backdrop will show up.
					// the style follow up in template. this is sample style. edit if need.
					jabackdrop.css({
						'width': '100%',
						'position': 'fixed',
						'height': '100%',
						'top': 0,
						'left': 0,
						'z-index': 101,
						'background': 'black',
						'opacity': 0.5,
						'display': 'none'
					});
					$('.sidebar-toggle').off().on('click', function () {
						$('.ja-mg-sidebar').toggleClass('open');
						if ($('.ja-mg-sidebar').hasClass('open')) {
							jabackdrop.off().on('click', function () {
								$('.ja-mg-sidebar').toggleClass('open');
								$(this).fadeOut();
							});
							jabackdrop.fadeIn();
						} else {
							jabackdrop.fadeOut();
						}
					});

					MegaFilterCallback();
				}
			});
		}

		// free object
		$(window).on('unload', function () {
			lnfilter = null;
			lnmain = null;
			lntoolbar = null;
		})
	};


	UBLN.load = function () {
		// handle first visit, reselect
		var qs = lnmain.getQS().qs,
			page = qs.page ? qs.page : 1;

		lnfilter.page = page;
		lnfilter.load();
		if (!lnmain.matchedIds.length) {
			lnmain.setMatchedItems(Object.keys(lnmain.items));
		}
		lntoolbar.load();
		lnmain.updateRender();
	}


	// reinit quickview
	$(document).on('afterUpdateRender', function (e) {
		if (e.lntarget == lnmain) {
			// scroll to top
			if (typeof MegaFilterCallback === 'function') {
				MegaFilterCallback();
			}
		}
	})

	$(document).on('afterAutoPage', function (e) {
		if (typeof afterAutoPage === 'function') {
			afterAutoPage();
		}

	})

	window.UBLN = UBLN;
})(jQuery);