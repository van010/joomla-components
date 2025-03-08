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
 
var LNBase = Class.extend(function(){
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNBase';

	self.options = null;
	self.defaultOptions = {
		rerenderWhenUpdate: false
	};

	self.state = {
	};

	self.items = {};
	self.$item = null;

	self.data = {};

	self.ids = [];
	self.matchedIds = [];
	self.shownIds = [];
	self.showingIds = [];

	self.startIdx = 1;
	self.endIdx = 0;
	self.page = 1;
	self.itemPerPage = 12;

	self.rendered = false;

	self.sortField = 'position'; 
	self.sortDir = 'desc';

	self.qs = {};

	self.timeout = [];

	self.constructor = function (options) {
		self.options = $.extend({}, self.defaultOptions, options);
	}


	self.render = function (callback) {
		self.beforeRender();

		var e = $.Event('beforeRender');
		e.lntype = self.type;
		e.lntarget = self;
		$(document).trigger(e);

		dust.render(self.options.template, self, function(err, out) {
			var $el = $(out);
			if ($el.length != 1) $el = $('<div>').html(out);
			$el.addClass('ln-element').addClass(self.options.class).data('lnid', self.getId());

			if (self.$item) {
				self.$item.remove();
				self.$item = null;
			}

			if (callback) {
				self.$item = callback($el);
			} else {
				self.$item = $el.appendTo($(self.options.container).empty());
			}
			if (self.options.itemWrapper) {
				self.$wrapper = self.$item.is(self.options.itemWrapper) ? self.$item : self.$item.find(self.options.itemWrapper);
			}

			// find state fields
			self.$stateFields = self.$item.find('[data-lnstate]');

			self.renderItems();

			// Render state value
			self.renderStateFields ();

			self.afterRender();
			self.rendered = true;

			var e = $.Event('afterRender');
			e.lntype = self.type;
			e.lntarget = self;
			self.$item.trigger(e);
		});
		var e = $.Event('completeRender');
		e.lntype = self.type;
		e.lntarget = self;
		self.$item.trigger(e);
	}

	self.renderStateFields = function () {
		if (self.$stateFields.length) {
			self.$stateFields.each(function () {
				var $field = $(this),
					field = $field.data('lnstate'),
					value = self.getField (field);
				$field.html(value);
			})
		}
	}

	self.beforeRender = function () {
		if (self.options.beforeRender) self.options.beforeRender.apply(self);
	}

	self.afterRender = function () {
		if (self.options.afterRender) self.options.afterRender.apply(self);	
	}

	self.beforeUpdateRender = function () {
		if (self.options.beforeUpdateRender) self.options.beforeUpdateRender.apply(self);
	}

	self.afterUpdateRender = function () {
		if (self.options.afterUpdateRender) self.options.afterUpdateRender.apply(self);	
	}

	self.beforeRenderItems = function () {
		if (self.options.beforeRenderItems) self.options.beforeRenderItems.apply(self);
		if (!Object.keys(self.items).length) return;
		if (self.options.showAllItems) {
			self.matchedIds = Object.keys(self.items);
			self.endIdx = self.matchedIds.length;
			self.startIdx = 1;
		} else {
			self.endIdx = self.startIdx + self.itemPerPage - 1;
			if (self.endIdx > self.matchedIds.length) self.endIdx = self.matchedIds.length;
		}
		if (!self.matchedIds || !self.matchedIds.length) return false;
		self.showingIds = self.matchedIds.slice(self.startIdx - 1, self.endIdx);
		return true;
	}

	self.afterRenderItems = function () {
		if (self.options.afterRenderItems) self.options.afterRenderItems.apply(self);
	}

	self.renderItems = function () {
		// render children item 
		if (self.beforeRenderItems()) {
			var oldIds = [];
			for(var i=0; i<self.shownIds.length; i++) {
				// remove old item on page
				var id = self.shownIds[i];
				if ($.inArray(id, self.showingIds) == -1) {
					// remove html item
					self.getItem(id).$item.remove();
				} else {
					oldIds.push(id);
				}
			}
			// Render for new item
			self.$currentItem = null;
			for(var i=0; i<self.showingIds.length; i++) {
				var id = self.showingIds[i];
				if ($.inArray(id, oldIds) == -1) {
					// render new one
					self.renderItem(id);
				} else {
					var item = self.getItem (id);
					item.updateRender();
					if (self.$currentItem) item.$item.insertAfter(self.$currentItem);
					self.$currentItem = item.$item;
				}
			}
			// update shownIds
			self.shownIds = self.showingIds.slice();
		} else {
			if (self.$wrapper) self.$wrapper.empty();
			self.shownIds = []
		}

		self.afterRenderItems();
	}

	self.renderItem = function (id) {
		var item = self.getItem(id);

		item.render(function($item){
			$item.addClass(self.options.itemClass);
			if (self.$currentItem) $item.insertAfter(self.$currentItem);
			else $item.appendTo(self.$wrapper);
			self.$currentItem = $item;
			return $item;
		});
	}

	self.updateRender = function () {
		self.delayCall('_updateRender', 1);
	}

	self._updateRender = function () {
		var e = $.Event('beforeUpdateRender');
		e.lntype = self.type;
		e.lntarget = self;
		self.$item.trigger(e);

		if (self.options.rerenderWhenUpdate) {
			self.render();
		} else {
			self.beforeUpdateRender();
			self.renderItems();
			self.renderStateFields ();
			self.afterUpdateRender();
		}

		if (self.type === 'LNBase' && self.options.autopage) {
			self.autoPage();
		}

		var e = $.Event('afterUpdateRender');
		e.lntype = self.type;
		e.lntarget = self;
		self.$item.trigger(e);
	}

	self.addItem = function (child, id) {
		if (!id) id = child.data[id];
		child.lnid = id.slugify();
		child.parent = self;
		self.items[child.lnid] = child;
	}

	self.getData = function () {
		return self.data;
	}

	self.setData = function (data) {
		self.data = data;
	}

	self.getField = function (field) {
		if (self[field] !== undefined) return self[field];
		if (self.data[field] !== undefined) return self.data[field];
		// check if path field, eg: stats.HP
		var fields = field.split('.'),
			v = self.data, 
			i=0;
		while(v && i<fields.length) {
			v = v[fields[i++]];
		}
		return v === undefined ? '' : v;
	}

	self.getTemplate = function () {
		return self.options.template;
	}

	self.getItem = function (id) {
		return self.items[id.slugify()];
	}

	self.getId = function () {
		return self.lnid;
	}

	self.is = function (check) {
		if (typeof check == 'string') {
			return check === self.type;
		}

		if (check instanceof LNBase) {
			return (check.is(self.type) && check.getId() == self.getId());
		}

		return false;
	}

	// pages
	self.setPage = function (p) {
		if (!p) return;
		self.setQS('page', p);
		self.page = p;
		self.startIdx = (p-1) * self.itemPerPage + 1;
		return self;
	}

	// sticky
	self.sticky = function () {
		if(!isMobile.any ) {
			$(self.options.container).parent().stick_in_parent().trigger('sticky_kit:recalc');
		}
	}

	// auto page
	self.autoPage = function () {
		var	itemWrapper = $(self.options.itemWrapper),
		div = $('<div>', {id:'autopage','page-data': 1, 'style':'clear:both'}),
		reachEnd = 0;

		if ($('#autopage').length) {
			$('#autopage').remove();
			itemWrapper.append(div);
		}
		else {
			itemWrapper.append(div);
		}

		self.startIdx = 1;
		self.endIdx = self.matchedIds.length;

		$(window).on('scroll.page',function () {
			var wheight = $(window).outerHeight(),
			target = $('#autopage').offset().top,
			numpage = parseInt($('#autopage').attr('page-data')),
			start = numpage * self.itemPerPage + 1,
			end = start + self.itemPerPage - 1;

			if (end > self.endIdx ) 
				end = self.endIdx;  

			if( ($(window).scrollTop() + wheight + 400) >= target ) {
				self.showingIds = self.matchedIds.slice(start - 1, end)

				for(var i=0; i<self.showingIds.length; i++) {
					var id = self.showingIds[i];
					self.renderItem(id);
					self.shownIds.push(id);
				}
				
				if (end === self.endIdx) {
					$(window).off('scroll.page')
				} else {
					numpage = numpage + 1;
					$('#autopage').attr('page-data', numpage );
				}

				// for afterAutoPage() callback
				var e = $.Event('afterAutoPage');
				e.lntype = self.type;
				e.lntarget = self;
				self.$item.trigger(e);
			}
		});
	}

	// pages
	self.setLimiter = function (limiter) {
		self.setQS('limiter', limiter);
		self.itemPerPage = parseInt(limiter);
		self.startIdx = (self.page-1) * self.itemPerPage + 1;
		if (self.startIdx >= self.matchedIds.length) {
			// reset to first page
			self.startIdx = 1;
			self.page = 1;
		}
		
		return self;
	}

	// sort
	self.sort = function (field, dir) {
		if (field !== undefined) {
			self.setQS('sort', field);
			self.sortField = field;
		}
		if (dir == 'asc' || dir == 'desc') {
			self.setQS('sortdir', dir);
			self.sortDir = dir;
		}
		var d = self.sortDir == 'desc' ? -1 : 1;

		if (!self.ids.length || (self.sortField == 'position' && d == -1)) 
			self.ids = Object.keys(self.items);
		// do sort
		if (self.sortField != 'position') {
		    let dump_custom_ordering = [];
            if (ja_custom_ordering[self.sortField.replace('frontend_value', 'value')] !== undefined)
                dump_custom_ordering = ja_custom_ordering[self.sortField.replace('frontend_value', 'value')];
			self.ids.sort(function (a, b) {
				var v1 = self.getItem(a).getField(self.sortField),
					v2 = self.getItem(b).getField(self.sortField);

				// transfer array to string
				if (Array.isArray(v1)) {
					v1.sort();

					// sort items depend on ordering feature in administrator
					if (ja_custom_ordering[self.sortField.replace('frontend_value', 'value')] !== undefined) {
					    let v11 = [];
                        for (let x in dump_custom_ordering) {
                            if (v1.indexOf(dump_custom_ordering[x]) !== -1)
                                v11.push(parseInt(x));
                        }
                        if (v11.length)
                            v1 = v11;
                    }

					v1 = v1.join(' ');
				}
				if (Array.isArray(v2)) {
					v2.sort();

					// sort items depend on ordering feature in administrator
                    if (ja_custom_ordering[self.sortField.replace('frontend_value', 'value')] !== undefined) {
					    let v11 = [];
                        for (let x in dump_custom_ordering) {
                            if (v2.indexOf(dump_custom_ordering[x]) !== -1)
                                v11.push(parseInt(x));
                        }
                        if (v11.length)
                            v2 = v11;
                    }

					v2 = v2.join(' ');
				}
					
				if (v1 == v2) {
					var t1 = +self.getItem(a).getField('id'),
						t2 = +self.getItem(b).getField('id');
					return t1 > t2 ? d : -d;
				}
				
				if ( !isNaN(v1) && !isNaN(v2) && v1 !== '' && v2 !== '') {
					v1 = +v1;
					v2 = +v2;
					return v1 > v2 ? d : -d;
				} else if( v1 && v2 ) {
					v1 = v1.split('');
					v2 = v2.split('');
					var min = Math.min(v1.length, v2.length);
					for (var i = 0; i < min; i++) {
						if (v1[i] != v2[i]) {
							return v1[i].localeCompare(v2[i]) > 0 ? d : -d;
						}
					}

					return v1 > v2 ? d : -d;
				}
				
				// let empty value always goes to bottom
				if (!v1) {
					return d > 0 ? d : -d;
				}
				
				if (!v2) {
					return d < 0 ? d : -d;
				}
			});
		} else if (d == 1) {
			self.ids.reverse();
		}
		return self;
	}

	self.setMatchedItems = function (ids) {
		self.matchedIds = [];
		if (!self.ids.length) self.ids = Object.keys(self.items);
		self.ids.forEach(function(id) {
			if (ids.indexOf(id) != -1) self.matchedIds.push(id);
		})
		return self;
	}

	self.setQS = function (name, value) {
		self.getQS().set(name, value);
	}

	self.getQS = function () {
		if ($.lnqs == undefined) {
			$.lnqs = new LNQueryString();
			$.lnqs.load();
		}
		return $.lnqs;
	}

	self.delayCall = function (func, timeOut) {
		if (self.timeout[func]) clearTimeout(self.timeout[func]);
		if (self[func]) {
			self.timeout[func] = setTimeout(function () {self[func]()}, timeOut);
		}
	}

	self.cleanRangeValue = function (value) {
		if(isNaN(value)) {
			var arr = [], pieces = value.split('');
			for (var i = 0; i < pieces.length; i++) {
				pieces[i] = pieces[i].replace(',', '.');
				if (pieces[i] == ' ') {
					continue;
				}

				if (pieces[i] === '.' || !isNaN(pieces[i])) {
					arr.push(pieces[i]);
				}
			}
			value = arr.join('');
			return isNaN(value) ? 0 : value;
		} 

		return value;
	}
});
;
var LNFilter = LNBase.extend(function(){
	// private variable
	var self = this,
		$ = jQuery,
		so = setOps;

	self.type = 'LNFilter';

	self.defaultOptions = {
		template: 'filter-list',
		container: '.sidebar',
		itemWrapper: '.filter-list',
		showAllItems: true
	};

	self.lnItems = null;
	self.filterResult = null;

	// is first load

	self.isFirstLoad = true;

	// store selected value
	self.selectedFilters = {};

	// page
	self.page = 1;

	// add field value data
	self.lnFieldValues = null;

	// Filter base on list data items
	self.addItems = function (lnItems) {
		self.lnItems = lnItems;
	}

	// add field value data
	self.addFieldValues = function (lnFieldVals){
		self.lnFieldValues = lnFieldVals;
	}

	// Filter result
	self.addFilterResult = function (filterResult) {
		self.filterResult = filterResult;
		self.filterResult.setSelectedFilters (self.selectedFilters);
	}


	self.addFilterSingle = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.radio',
			class: 'filter-field filter-radio',
			type: 'single'
		})), 'filter-' + options.field);
	}

	self.addFilterDropdown = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.dropdown',
			class: 'filter-field filter-dropdown',
			type: 'dropdown'
		})), 'filter-' + options.field);
	}

	self.addFilterList = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.list',
			class: 'filter-field filter-list',
			type: 'list'
		})), 'filter-' + options.field);
	}


	self.addFilterMultiple = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.multiple',
			class: 'filter-field filter-multiple',
			type: 'multiple'
		})), 'filter-' + options.field);
	}

	self.addFilterValue = function (options) {
		// template
		self.addItem (new LNFilterValue(options), 'filter-' + options.field);
	}

	self.addFilterDate = function (options) {
		// template
		self.addItem (new LNFilterDate(options), 'filter-' + options.field);
	}
	self.addFilterNumberrange = function (options) {
		// template
		self.addItem (new LNFilterNumberrange(options), 'filter-' + options.field);
	}

	self.addFilterColor = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.color',
			class: 'filter-field filter-color',
			type: 'color'
		})), 'filter-' + options.field);
	}

	self.addFilterSize = function (options) {
		// template
		self.addItem (new LNFilterGroup($.extend(options, {
			template: 'filter.size',
			class: 'filter-field filter-size',
			type: 'size'
		})), 'filter-' + options.field);
	}

	self.addFilterMedia = function (options) {
	    // template
        self.addItem (new LNFilterGroup($.extend(options, {
            template: 'filter.media',
            class: 'filter-field filter-media',
            type: 'media'
        })), 'filter-' + options.field);
	}

	self.addFilterRange = function (options) {
		// find max value 
		if (!options.max) {
			var field = options.field,
				vals = [];
			for (var id in self.lnItems.items) {
				var v = self.lnItems.getItem(id).getField(field);
				if (Array.isArray(v)) {
					for (var i = 0; i < v.length; i++) {
						vals.push(+self.cleanRangeValue(v[i]));
					}
				} else {
					vals.push (+self.cleanRangeValue(v));
				}
			}
			options.min = Math.min.apply(Math, vals);
			options.max = Math.max.apply(Math, vals);
			// change max min value to float to int.
			if (!!(options.max % 1)) options.max = Math.ceil(options.max);
			if (!!(options.min % 1)) options.min = Math.floor(options.min);
		}

		// template
		self.addItem (new LNFilterRange(options), 'filter-' + options.field);
	}

  self.addFilterRating = function(options){
    self.addItem (new LNFilterGroup($.extend(options, {
      template: 'filter.rating',
      class: 'filter-field filter-rating',
      type: 'rating'
    })), 'filter-' + options.field);
    // self.addItem ( new LNFilterRating(options), 'filter-' + options.field);
  }

	

	// update value for filter type Single & Multiple
	self.updateFilter = function () {
		for (var gid in self.items) {
			var fgroup = self.getItem(gid),
				field = fgroup.options.field,
				type = fgroup.options.type,
				frontend_value;
			if (fgroup.options.frontend_field)
				frontend_value=fgroup.options.frontend_field;
			else
				frontend_value=fgroup.options.field;
			// show all child
			fgroup.options.showAllItems = true;
			fgroup.options.itemWrapper = '.filter-items';
			// fetch child value from filter data
			if (fgroup.is('LNFilterGroup')) {
				if (fgroup.options.type === "rating") {
					var items = self.lnItems.items;
					var rates = [5,4,3,2,1,0];
					rates.forEach(rate => {
						const fitem = new LNFilterItem({
							template: fgroup.getTemplate() + '-item',
							name: field,
							frontend_value: rate,
							value: rate,
							width_rating: rate*20,
						});
						fitem.mids = [];
						fgroup.addItem(fitem, field +'-' + rate);

						for (var id in self.lnItems.items) {
							var item = self.lnItems.getItem(id);
							var itemRating = item.getField(field);

							itemRating = itemRating ? parseFloat(itemRating) : 0;
							if (itemRating === 5){
								fitem.mids.push(id);
							} else if (itemRating >= rate && itemRating < 5) {
								fitem.mids.push(id);
							}
						}
					})
				} else {
					// fetch field values into array
					for (var id in self.lnItems.items) {
						var item = self.lnItems.getItem(id),
						val = item.getField(field),
						frontend_val = item.getField(frontend_value);

						if (val === undefined) continue;

						if ($.isArray(val)) {
							for (var i=0; i<val.length; i++) {
								var v = val[i],
								key = field + '-' + v,
								fitem = fgroup.getItem(key);

								if (!v || v == 'N/A') continue;
								if (!fitem) {
								fitem = new LNFilterItem({
									template: fgroup.getTemplate() + '-item',
									name: field,
									frontend_value: frontend_val[i],
									value: v
								});
								fitem.mids = [];
								fgroup.addItem(fitem, key);
								}

								$.inArray(id, fitem.mids) === -1 && fitem.mids.push(id);
							}
						} else {
							var v = val,
								key = field + '-' + v,
								fitem = fgroup.getItem(key);
							if (!v || v == 'N/A') continue;
							if (!fitem) {
								fitem = new LNFilterItem({
								template: fgroup.getTemplate() + '-item',
								name: field,
								value: v
								});
								fitem.mids = [];
								fgroup.addItem(fitem, key);
							}

							$.inArray(id, fitem.mids) === -1 && fitem.mids.push(id);
						}
					}
				}

				// sort child items
				var keys = Object.keys(fgroup.items);

				// unset this fgroup if less than 1 values
				if (keys.length < 1) {
					delete self.items[gid];
				} else {
					var ordering = ja_custom_ordering[field];
					if (ordering) {
						keys.sort(function(a, b) {
							var valA = fgroup.getItem(a).options.value;
							var valB = fgroup.getItem(b).options.value;
							var keyA = ordering.indexOf(decodeURIComponent(valA));
							var keyB = ordering.indexOf(decodeURIComponent(valB));
							return keyA < keyB ? -1 : 1;
						});
						
					} else {
						var order = (fgroup.options.order.toUpperCase() == 'DESC') ? -1 : 1;
						keys.sort(function (a, b) {
							// control the order direction. it's do not relate to default options.
							var orderType = 0;
							var $a = fgroup.getItem(a);
							var $b = fgroup.getItem(b);

							if (ja_fileter_field_order[$a.options.name] !== undefined && ja_fileter_field_order[$b.options.name] !== undefined) {
								if (ja_fileter_field_order[$a.options.name] === 'name_asc') // name ASC
									order = 1;
								if (ja_fileter_field_order[$a.options.name] == 'name_desc') // name DESC
									order = -1;
								if (ja_fileter_field_order[$a.options.name] == 'number_asc') // number ASC
									orderType = 1;
								if (ja_fileter_field_order[$a.options.name] == 'number_desc') // number DESC
									orderType = -1;
							}

							if (orderType) {
								var countA = typeof $a.mcount === 'number' ? $a.mcount : $a.mids.length;
								var countB = typeof $a.mcount === 'number' ? $b.mcount : $b.mids.length;

								if (orderType === 1) {
									return countA < countB ? -1 : 1; // ASC
								} else {
									return countA > countB ? -1 : 1; // DESC
								}
							}

							if ($a.options.name === 'rating'){
								return $a.options.name.localeCompare($b.options.name) * order;
							}
							// end control.
							return $a.options.frontend_value.localeCompare($b.options.frontend_value) * order;
						});
						
						// re-sort custom field type radio as ordering in backend configuration
						if (fgroup.options.template === 'filter.radio' && self.lnFieldValues != null 
								&& fgroup.options.raw_name != null){
									
							var lnidVal = fgroup.lnid.split('filter-')[1];
							var newKeys = [];
							for(const [key, radioVal] of Object.entries(self.lnFieldValues)){
								if (key == null) return true;
								var fieldName  = key.split('-').join(' ');
								if (fgroup.options.raw_name.toLowerCase() === fieldName){
									for(const [key1, radioVal1] of Object.entries(radioVal)){
										newKeys.push(lnidVal + '-' + radioVal1.value);
									}
								}
							}

							if (newKeys.length !== 0){
								keys = newKeys;
							}
						}
					}

					var _items = {};
					keys.forEach(function (id) {
						_items[id] = fgroup.getItem(id);
					});

					fgroup.items = _items;
				}
			}
		}
		return self;
	}

	self.afterRender = function () {
		// tracking change on filter
		self.$item.on('change', function (e) {
			if (self.options.sticky) {
				self.sticky();
			}
			// update filter selected
			var $value = $(e.target),
				$fgroup = $value.closest('.filter-field'),
				fgroup = self.getItem($fgroup.data('lnid')),
				field = fgroup.options.field,
				type = fgroup.options.type;
			if ($value.is('select')) {
				// find element item
				var val = $value.val(),
					key = field + '-' + val,
					fitem = fgroup.getItem(key);
				// update to selected list
				if ($value.attr('multiple') === undefined) {
					// single select
					if (val) {
						self.selectedFilters[field] = fitem;
					} else {
						delete self.selectedFilters[field];
					}
				} else {
					// multiple select
					$value.find('option').each(function() {
						var key = field + '-' + $(this).attr('value');
						var fitem = fgroup.getItem(key);
						if ($.inArray(jQuery(this).attr('value'), val) !== -1) {
							self.selectedFilters[key] = fitem;
						} else {
							delete self.selectedFilters[key];
						}
					});
				}
			} else {
				if (fgroup.is('LNFilterValue')) {
					fgroup.value = $value.val();
					if (fgroup.value) {
						self.selectedFilters[field] = fgroup;
					} else {
						delete self.selectedFilters[field];
					}
				}
				if (fgroup.is('LNFilterGroup') && (type == 'multiple' || type == 'color' || type == 'size' || type == 'media')) {
					var $fitem = $value.closest('.ln-element'),
						fitem = fgroup.getItem($fitem.data('lnid')),
						key = $fitem.data('lnid');//field + '-' + fitem.options.value;
					// toggle. with checkbox type we toggle the active.
					if ($value.prop('checked')) {
					    jQuery($fitem).addClass('jamg-active');
						self.selectedFilters[key] = fitem;
					} else {
					    jQuery($fitem).removeClass('jamg-active');
						delete self.selectedFilters[key];
					}
				}
				if (fgroup.is('LNFilterGroup') && (type === 'single') || type === 'rating') {
					var $fitem = $value.closest('.ln-element'),
						fitem = fgroup.getItem($fitem.data('lnid'));
					// with radio we uncheck all radio first.
					$fgroup.find('.jamg-active').removeClass('jamg-active');
					if (fitem) {
            jQuery($fitem).addClass('jamg-active');
						self.selectedFilters[field] = fitem;
					} else {
						delete self.selectedFilters[field];
					}
				}
				if (fgroup.is('LNFilterRange')) {
					fgroup.value = $value.val();
					fgroup.value[0] = parseInt(fgroup.value[0]); // to Int
					fgroup.value[1] = parseInt(fgroup.value[1]); // to Int
					if (fgroup.value) {
						self.selectedFilters[field] = fgroup;
					} else {
						delete self.selectedFilters[field];
					}
				}				

				if (fgroup.is('LNFilterDate') || fgroup.is('LNFilterNumberrange')) {
					fgroup.value = $value.val();
					if (fgroup.value) {
						self.selectedFilters[field] = fgroup;
					} else {
						delete self.selectedFilters[field];
					}
				}
			}

			// using timeout to prevent multiple change in sort time
			clearTimeout(self.filterChangeTimeout);
			self.filterChangeTimeout = setTimeout(function() {
				self.filterChangeHandle()
			}, 100);
		});

		self.super.afterRender();

	}

	self.filterChangeHandle = function () {
		// fetch matched item
		var matchedIds = [];
		jQuery('span.color-item-bg').removeClass('color-active');
		jQuery('img.img-item').removeClass('media-active');
		if (Object.keys(self.selectedFilters).length) {
			var fields = {},
				ids = [],
				vfields = [];
			for(var prop in self.selectedFilters) {
				var fitem = self.selectedFilters[prop],
					field;

				if (!fitem.is('LNFilterItem')) {
					field = fitem.options.field;
					// process for value & range filter
					// vfields.push(prop);
					var _ids = [];
					for (var id in self.lnItems.items) {
						var item = self.lnItems.getItem(id),
							matched = true;
						if (!fitem.options.match || !fitem.options.match(item, field, fitem.value))
							matched = false;
						if (matched) _ids.push(id);
					}
					fields[field] = _ids;
				} else {
					field = fitem.parent.options.field;
					if (fitem.options.template === 'filter.color-item') {
						fitem.$item.find('.color-item-bg').addClass('color-active');
					} else if (fitem.options.template === 'filter.media-item') {
						fitem.$item.find('img').addClass('media-active');
					}
					fields[field] = fields[field] ? setOps.union(fields[field], fitem.mids) : fitem.mids;
				}
				ids = setOps.union(ids, fields[field]);
			}

			var t = 0;
			// find matched items
			for(var field in fields) {
				matchedIds = t ? setOps.intersection(matchedIds, fields[field]) : fields[field];
				t++;
			}

			// update counter for this field
			for (var gid in self.items) {
				var fgroup = self.getItem(gid),
					field = fgroup.options.field,
					fmatchedIds = [];

				// reset before count
				for (var fid in fgroup.items) {
					fgroup.getItem(fid).mcount = 0;
				}

				if (field in fields) {
					if (Object.keys(fields).length == 1) {
						// use global count
						// update counter for each item
						for (var fid in fgroup.items) {
							fgroup.getItem(fid).mcount = fgroup.getItem(fid).mids.length;
						}
					} else {
						// find matched items except this condition
						var t = 0
						for(var f in fields) {
							if (f == field) continue;
							fmatchedIds = t ? setOps.intersection(fmatchedIds, fields[f]):fields[f];
							t++
						}
					}
				} else {
					fmatchedIds = matchedIds;
				}

				if (fmatchedIds.length) {
					// reset before count
					fmatchedIds.forEach(function(id) {	
						var item = self.lnItems.getItem(id),
							val = item.getField(field);

						if ($.isArray(val)) {							
							val.forEach(function(v){
								var fitem = fgroup.getItem(field + '-' + v);
								if (fitem) fitem.mcount += 1;
							});
						} else {
							var v = val,
								key = field + '-' + v,
								fitem = fgroup.getItem(key);
							if (fitem) fitem.mcount += 1;
						}
					})
				}
			}
		} else {
			matchedIds = Object.keys(self.lnItems.items);
			// reset counter
			for (var gid in self.items) {
				var fgroup = self.getItem(gid);
				for (var fid in fgroup.items) {
					fgroup.getItem(fid).mcount = fgroup.getItem(fid).mids.length;
				}
			}
		}
		self.lnItems.setMatchedItems(matchedIds);

		if (self.isFirstLoad) {
			self.isFirstLoad = false;
		} else {
			self.page = 1;
		}

		self.lnItems.setPage(self.page);
		self.lnItems.updateRender();

		self.updateFilter();
		self.updateRender();
		self.updateFilterResult();
		setTimeout(function () {
			self.$item.find('select').trigger('liszt:updated');
			self.$item.find('select').trigger('chosen:updated');
		}, 300)
	}

	self.updateFilterResult = function () {
		if (self.filterResult) {
			self.filterResult.updateRender();
		}
	}

	self.load = function () {
		var qs = self.getQS().qs,
			filtered = false;

		for (var gid in self.items) {
			var fgroup = self.getItem(gid),
				field = fgroup.options.field;
			if (qs[field]) {
				fgroup.setValue(qs[field]);
				filtered = true;
			}
		}

		return self;
	}
});
var LNFilterGroup = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterGroup';

	self.options = null;
	self.defaultOptions = {
		class: 'filter-field',
		order: 'ASC'
	};
	
	self.afterRender = function () {
		// easy way to change the category tree name.
		// only use field name that we can detect it's category tree.
		if (self.options.field == 'attr.cat.value' || self.options.field == 'attr.category.value' || self.options.field == 'attr.tag.value') {
			var $lv;
			switch (self.options.type) {
				case 'multiple': 
					self.$item.find('input').each(function() {
						$lv = ($(this).next().text().match(/ » /g) || []).length+1;
						$(this).parents('li').addClass('lv-'+$lv);
						$(this).next().text($(this).next().text().replace(/^(.*)?» /, ''));
					});
					break;
				case 'single':
        case 'rating':
					self.$item.find('input').each(function() {
						$lv = ($(this).next().text().match(/ » /g) || []).length+1;
						$(this).parents('li').addClass('lv-'+$lv);
						$(this).next().text($(this).next().text().replace(/^(.*)?» /, ''));
					});
					break;

				case 'dropdown':
					self.$item.find('option').each(function() {
						$lv = ($(this).text().match(/ » /g) || []).length+1;
						var $space = '';
						for (i=1; i< $lv; i++) {
							if (i > 1) {
								$space += '. ';
							} else {
								$space += ': . ';
							}
						}
						$(this).addClass('lv-'+$lv);
						$(this).text($space+''+$(this).text().replace(/^(.*)?» /, ''));
					});
					break;
				case 'color':
				case 'size':
				case 'list':
				case 'media':
					break;
			}
		}
		if (self.options.type == 'color') {
			self.$item.find('span.color-item-bg').each(function() {
				if ((JAnameColor[$(this).data('bgcolor').toLowerCase().trim()]) != undefined) {
					$(this).css('background-color', JAnameColor[$(this).data('bgcolor').toLowerCase().trim()]);
				} else {
					$(this).css('background-color', ($(this).data('bgcolor').toLowerCase().trim().indexOf('#') === -1 ? '#' : '')+jQuery(this).data('bgcolor').toLowerCase().trim());
				}
			});
		}

		if (self.options.type === 'dropdown') {
			self.$item.find('select').chosen({
				disable_search_threshold: 10,
				placeholder_text_multiple: Joomla.JText._('COM_JAMEGAFILTER_MULTIPLE_SELECT_PLACEHOLDER')
			});

			setTimeout(() => {
				self.$item.find('select').trigger('chosen:updated');
			});
		}
    if (self.options.type !== 'rating') {
      if ((ja_show_more > 0 && self.$item.find('li:not(.first)').length > ja_show_more)) {
        self.$item.find('li:not(.first):gt(' + (ja_show_more - 1) + ')').hide();
        self.$item.find('li').last().after('<li class="show-more" style="float: none; clear: both;"><a href="javascript:;" class="" onclick="return openShift(this);">' + jamegafilter_show_more + '</a></li>');
      }
    }
	}

	self.reset = function () {
		self.setValue('');
	}

	self.setValue = function (value) {
		switch (self.options.type) {
			case 'color':
			case 'size':
			case 'multiple': 
			case 'media':
				var vals = value.split(',');
				self.$item.find('input').prop('checked', false).filter(function(){
					return vals.indexOf (this.value) != -1
				}).prop('checked', true).trigger('change');
				break;
      case 'single':
      case 'rating':
        self.$item.find('input[value="' + value + '"]').prop('checked', true).trigger('change');
        break;

			case 'dropdown':
				if (value.search(/,/) !== -1) {
					var _v = value.split(',');
					for (var x in _v) {
						self.$item.find('select option[value="'+(_v[x])+'"]').prop('selected', true);
						self.$item.find('select option[value="'+(_v[x])+'"]').attr('selected', true);
					}
					self.$item.find('select').trigger('change');
				} else
					self.$item.find('select').val(value).trigger('change');
				break;

			case 'list':
				break;
		}		
	}
});
var LNFilterItem = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterItem';

	self.options = null;
	self.defaultOptions = {
		class: 'filter-item'
	};


	self.updateRender = function () {
		// update textnode to replce counter
    if(self.$item != null) {
      self.$item.find(":not(iframe)").addBack().contents().filter(function () {
        return this.nodeType == 3;
      }).each(function () {
        var $this = $(this);
        $this.replaceWith($this.text().replace(/\(.*\)$/, '(' + self.mcount + ')'));
      });

      // add empty class into empty group
      if (!self.mcount) self.$item.addClass('empty');
      else self.$item.removeClass('empty');

      // call super
      self.super.updateRender();
    }
	}

	self.reset = function () {
		if (self.parent.options.type == 'dropdown' && self.$item.parent().attr('multiple') !== undefined) {
			self.$item.prop('selected', false);
			self.$item.parent().trigger('change');
			self.$item.parent().trigger("chosen:updated");
		} else {
			if (self.parent.options.type == 'multiple' || self.parent.options.type == 'size' || self.parent.options.type == 'color' || self.parent.options.type == 'media'){
				self.$item.find('input').prop('checked', false).trigger('change');
			} else {
				self.parent.reset();
			}
		}
	}
});

var LNFilterColor = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterColor';

	self.options = null;
	self.defaultOptions = {
		template: 'filter.color',
		class: 'filter-input filter-field filter-item',
		match: function (item, field, val) {
			return null;
		}
	};

	self.afterRender = function () {
		
	}

	self.reset = function () {
		
	}

	self.setValue = function (value) {
		
	}
});

var LNFilterSize = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterSize';

	self.options = null;
	self.defaultOptions = {
		template: 'filter.size',
		class: 'filter-input filter-field filter-item',
		match: function (item, field, val) {
			return null;
		}
	};

	self.afterRender = function () {
		
	}

	self.reset = function () {
		
	}

	self.setValue = function (value) {
		
	}
});

var LNFilterMedia = LNBase.extend(function() {
  var self = this,
  $ = jQuery;

  self.type = 'LNFilterMedia';

  self.options = null;

  self.defaultOptions = {
	template: 'filter.media',
	class: 'filter-input filter-field filter-item',
	match: function (item, field, val) {
	  return null;
	}
  };
});


var LNFilterDate = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterDate';

	self.options = null;

	self.defaultOptions = {
		template: 'filter.date',
		class: 'filter-input filter-field',
		match: function (item, field, val) {
			var from, to;
			// prevent error when the item do not had value on this field.
			if (item.getField(field) == undefined == true || item.getField(field) == false || item.getField(field) == undefined || item.getField(field) == 'undefined') return null; 

			from = self.$item.find('.jafrom').val(),
			to = self.$item.find('.jato').val();
			from = new Date(from).getTime() / 1000;
			to = new Date(to).getTime() / 1000 + 24 * 60 * 60;
			var itemval = item.getField(field)[0];
			var timeoffset = (new Date()).getTimezoneOffset();
			itemval = itemval - timeoffset * 60;
			return (itemval >= from && itemval < to);
		}
	};
	
	self.afterRender = function () {
		self.$item.find('input').each(function(i, e){
			$(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: "yy-mm-dd",
				onSelect : function(s, o){
					var from = self.$item.find('.jafrom').val(),
					to = self.$item.find('.jato').val();
					if (from !== '' && to !== '') {
						$(this).trigger('change');
					} 
				}
			});
		});
	}

	self.reset = function () {
		self.$item.find('input').each(function(i, e){
			$(e).val('').trigger('change');
		});
	}	

	self.setValue = function (value) {
		var vals = value.split(',');
		self.$item.find('input').each(function(i, e){
			$(e).val(vals[i]);
			$(e).trigger('change');
		});

	}
});

var LNFilterNumberrange = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterNumberrange';

	self.options = null;

	self.defaultOptions = {
		template: 'filter.txtrange',
		class: 'filter-input filter-field',
		match: function (item, field, val) {
			var from, to;
			// prevent error when the item do not had value on this field.
			if (item.getField(field) == undefined == true 
			    || item.getField(field) == false 
			    || item.getField(field) == undefined 
			    || item.getField(field) == 'undefined'
			) return null; 

			from = (self.$item.find('.jafrom').val()),
			to = (self.$item.find('.jato').val());
			if (from === "") from = 0;
			else from = parseInt(from);
			if (to === "") to = 9007199254740993; // MAX Integer in js.
			else to = parseInt(to);
			var itemval = parseInt(item.getField(field));
			return (itemval >= from && itemval <= to);
		}
	};
	
	self.afterRender = function () {
		self.$item.find('input').each(function(i, e){
// 			$(this).datepicker({
// 				changeMonth: true,
// 				changeYear: true,
// 				dateFormat: "yy-mm-dd",
// 				onSelect : function(s, o){
// 					var from = self.$item.find('.jafrom').val(),
// 					to = self.$item.find('.jato').val();
// 					if (from !== '' && to !== '') {
// 						$(this).trigger('change');
// 					} 
// 				}
// 			});
		});
	}

	self.reset = function () {
		self.$item.find('input').each(function(i, e){
			$(e).val('').trigger('change');
		});
	}	

	self.setValue = function (value) {
		var vals = value.split(',');
		self.$item.find('input').each(function(i, e){
			$(e).val(vals[i]);
			$(e).trigger('change');
		});

	}
});

var LNFilterRating = LNBase.extend(function(){
  var $ = jQuery;
  var self = this;
  this.type = 'LNFilterRating';
  this.options = null;

  this.defaultOptions = {
    template: 'filter.rating',
    class: 'filter-field filter-rating',
    match: function(item, field, val) {
      // self.handleMatchedItems(item, val)
    }
  }

  self.afterRender = function () {

  }

  self.reset = function () {
  }

  self.setValue = function (value) {}

  self.triggerChange = function () {

  }
})

var LNFilterRange = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterRange';

	self.options = null;
	self.defaultOptions = {
		template: 'filter.range',
		class: 'filter-range filter-field',
		min: 0,
		max: 0,
		match: function (item, field, val) {
			var itemVal = item.getField(field);
			var v;
			if (Array.isArray(itemVal)) {
				for (var i = 0; i < itemVal.length; i++) {
					v = self.cleanRangeValue(itemVal[i]);
					if (v >= val[0] && (val[1] == self.options.max || v <= val[1])) {
						return true;
					}
				}
			} else {
				v = self.cleanRangeValue(itemVal);
				return (v >= val[0] && (val[1] == self.options.max || v <= val[1]));
			}
			return false;
		}
	};

	self.afterRender = function () {
		self.$slider = self.$item.find('.range-item');
		if (self.$slider.length) {
			self.$slider[0].slide = null;
			self.$slider.slider({
				range: true,
				min: self.options.min,
				max: self.options.max,
				isRTL: true, // RTL
				values: [ self.options.min, self.options.max ],
				slide: function( event, ui ) {
					self.$item.find('.range-value0').html(ui.values[0]);
					self.$item.find('.range-value1').html(ui.values[1]);
				},
				change: function( event, ui ) {
					clearTimeout(self.changeTimeout);
					self.changeTimeout = setTimeout(function(){self.triggerChange();}, 100);
					self.$item.find('.range-value0').html(ui.values[0]);
					self.$item.find('.range-value1').html(ui.values[1]);
				}
			});
			// first value
			self.$item.find('.range-value0').html(self.options.min);
			self.$item.find('.range-value1').html(self.options.max);
		}
	}

	self.reset = function () {
		self.$slider.slider( "values", [self.options.min, self.options.max]);
	}

	self.setValue = function (value) {
		self.$slider.slider( "values", value.split(','));
	}

	self.triggerChange = function () {
		var values = self.$slider.slider("values");
		self.$slider.val((values[0] == self.options.min && values[1] == self.options.max) ? null : values);
		var e = $.Event( "change", { target: self.$slider[0] } );
		self.$slider.trigger('change', e);
	}

	self.toString = function () {
		if (self.options.toString) self.options.toString.apply(self);
		return self.$slider.slider("values").join (' - ');
	}
});
var LNFilterValue = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNFilterValue';

	self.options = null;
 
	self.defaultOptions = {
		template: 'filter.value',
		class: 'filter-input filter-field',
		match: function (item, field, val) {
			if (item.getField(field) == undefined || item.getField(field) == 'undefined') return null; // prevent error when the item do not had value on this field.
			val = val.replace(/([^a-zA-Z0-9 ])/g, '\\$1').toLowerCase(); // addslashes to all special characters.
			var specialChar = {
				'a':'(a|á|à|ả|ã|ạ|ă|ắ|ằ|ẵ|ặ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Ä|Å|Æ)'.toLowerCase(),
				'o':'(o|ó|ò|ỏ|õ|ọ|ơ|ớ|ờ|ở|ợ|ỡ|ô|ố|ồ|ổ|ỗ|ộ|Ö|Ø)'.toLowerCase(),
				'u':'(u|ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ự|ữ|Û|Ü)'.toLowerCase(),
				'y':'(y|ý|ỳ|ỷ|ỹ|ỵ|ÿ)'.toLowerCase(),
				'i':'(i|í|ì|ỉ|ĩ|ị|Î|Ï|ı)'.toLowerCase(),
				'e':'(e|é|è|ẻ|ẽ|ẹ|ê|ế|ề|ễ|ệ|ể|Ë)'.toLowerCase(),
				'c':'(c|Ç)'.toLowerCase(),
				'd':'(d|đ)'.toLowerCase(),
				'n':'(n|Ñ)'.toLowerCase(),
				'g':'(g|ğ)'.toLowerCase(),
				's':'(s|ş)'.toLowerCase()
			};
			for (var x in specialChar) {
				val = val.replace(new RegExp(x, 'g'), specialChar[x]);
			}
			var searchSTR = item.getField(field).toLowerCase();
			return searchSTR.match(new RegExp(val, 'i'));
		}
	};

	self.reset = function () {
		self.$item.find('input').val('').trigger('change');
	}	

	self.setValue = function (value) {
		self.$item.find('input').val(value).trigger('change');
	}

	self.afterRender = function () {
		if (navigator.userAgent.match(/Trident/)) {
			self.$item.find('input').on('keyup', function(e) {
				if (e.keyCode === 13) {
					jQuery(this).trigger('change');
				}
			});
		}
	}	
});

var LNItem = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNItem';

	self.options = null;
	self.defaultOptions = {
		class: 'ln-item'
	};
});
var LNQueryString = Class.extend(function(){
	// private variable
	var self = this,
		$ = jQuery;
	self.qs = {};

	self.curHash = location.hash;

	self.cache = [];

	self.defaultValues = {};

	self.load = function () {
		// handle first visit, reselect
		var qs = {};
		location.hash.substr(1).split('&').forEach(function(nvp){
			var nv = nvp.split('=');
			if (nv.length == 2) qs[nv[0]] = decodeURIComponent(nv[1]);
		});
		self.qs = qs;

		// register event to update update url hash
		$(document).on('afterRender', function (e) {
			var hash = [], page = 0;
			for (var name in self.qs) {
				if (name === 'page') {
					page = self.qs[name];
					continue;
				}
				hash.push(name + '=' + encodeURIComponent(self.qs[name]));
			}
			var curHash = hash.slice(0);
			if (page) curHash.push('page=' + page);
			curHash = curHash.length ? '#' + curHash.join('&') : '';
			// first load handling for empty values selected
			var btnSearch = $('#jamegafilter-search-btn');

			if (btnSearch.length && btnSearch.attr('href').includes('javascript:void')){
				btnSearch.attr('href', filter_url + curHash);
			}
			// handle when values selected
			if (e.lntype === 'LNToolbar' || e.lntype === 'LNSelected') {
				if (self.curHash !== curHash) { // only update url once
					self.curHash = curHash;
					if (btnSearch.length > 0) {
						var surl = filter_url + curHash;
						btnSearch.attr('href', surl );
					} else {
						history.replaceState(null, null, location.origin + location.pathname + curHash);	
						// update href for page links
						var baseLink = '#' + (hash.length ? hash.join('&') + '&' : '');
						$('a.page').each(function(){
							var $a = $(this),
								page = $a.data('value');
							$a.attr('href', baseLink + 'page=' + page);
						})
					}
				}
			}
		})
	}

	self.set = function (name, value) {
		if (value) {
			self.qs[name] = value;
		} else {
			delete self.qs[name];
		}
	}

	self.setDefault = function (defaultValues) {
		self.defaultValues = defaultValues;
	}
});
var LNSelected = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNSelected';

	self.options = null;
	self.defaultOptions = {
		template: 'filter-selected',
		class: 'selected-filters'
	};

	self.selectedFilters = null;
	self.values = {};

	self.filterFields = [];

	self.setSelectedFilters = function (selectedFilters) {
		self.selectedFilters = selectedFilters;
	}

	self.setFilter = function (lnFilter) {
		self.lnFilter = lnFilter;
		self.filterFields = [];
		for (var gid in lnFilter.items) {
			var fgroup = lnFilter.getItem(gid),
				field = fgroup.options.field;
			self.filterFields.push(field);
		}
	}

	self.updateRender = function () {
		self.render();
	}

	self.beforeRender = function () {
		// parse selected filters to display
		self.values = {};
		for(var prop in self.selectedFilters) {
			var fitem = self.selectedFilters[prop], field, sval, rval, fval;
			if (fitem.is('LNFilterItem')) {
				fval = fitem.parent.options.field;
				field = fitem.parent.options.title;
				rval = fitem.options.value;
				sval = fitem.options.frontend_value;
				if (fitem.options.name == 'attr.cat.value' || fitem.options.name == 'attr.category.value' || fitem.options.name == 'attr.tag.value')
					sval = sval.replace(/^(.*)?\&raquo\; /, '').replace(/^(.*)?» /, '');

				if (fitem.options.template === 'filter.color-item') {
					if (JAnameColor[sval.toLowerCase().trim()] != undefined) {
						sval = '<i class="fa fa-circle-o" style="color:'+JAnameColor[sval.toLowerCase().trim()]+';" aria-hidden="true"></i>';
					} else {
						sval = '<i class="fa fa-circle-o" style="color:'+(sval.toLowerCase().trim().indexOf('#') === -1 ? '#' : '')+sval.toLowerCase().trim()+';" aria-hidden="true"></i>';
					}
				} else if (fitem.options.template === 'filter.media-item') {
					sval= '<img class="img-selected" src="'+JABaseUrl + '/' +sval+'" />';
				}

			} else if (fitem.is('LNFilterRange')) {
				fval = fitem.options.field;
				field = fitem.options.title;
				sval = fitem.toString();
				rval = fitem.value;
			} else if (fitem.is('LNFilterDate') || fitem.is('LNFilterNumberrange')) {
				fval = fitem.options.field;
				field = fitem.options.title;
				var from = fitem.$item.find('.jafrom').val(),
				to = fitem.$item.find('.jato').val();
				sval = from+' '+jamegafilter_to+' '+ to;
				rval = from+','+to;
			} else {
				fval = fitem.options.field;
				field = fitem.options.title;
				rval = fitem.value;
				sval = rval;
			}
			if (!self.values[fval]) self.values[fval] = [];
			if (fitem.options.template === 'filter.color-item' 
			    || fitem.options.template === 'filter.media-item'
			    || (fitem.options.frontend_value !== undefined && String(fitem.options.frontend_value).search(/k-icon-document/) !== -1)
			)
				self.values[fval].push({prop: prop, name: field, value: sval, raw_value: rval});
			else
				self.values[fval].push({prop: prop, name: field, value: dust.escapeHtml(sval), raw_value: rval});
		}

		// update url hash
		if (self.registeredClearFilterEvent) {
			var hash = [];
			self.filterFields.forEach(function(field) {
				var val = null;
				if (self.values[field]) {
					var vals = [];
					self.values[field].forEach(function(val){
						vals.push(val.raw_value);
					});
					val = vals.join(',');
				}

				self.setQS(field, val);
			})
		}

		//self.super.beforeRender();
	}

	self.afterRender = function () {
		if (Object.keys(self.values).length) 
			self.$item.parent().removeClass ('empty');
		else 
			self.$item.parent().addClass ('empty');
		self.super.afterRender();
		self.registerClearFilter();
	}

	self.registerClearFilter = function () {
		if (self.registeredClearFilterEvent) return;
		self.registeredClearFilterEvent = true;
		// listening click on filter-clear
		$(self.options.container).on('click', function (e) {
			var $item = $(e.target);
			if ($item.is ('.clear-filter')) {
				var lnprop = $item.data('lnprop'),
					item = self.selectedFilters[lnprop];
				item.reset();
			}
			if ($item.is ('.clear-all-filter')) {
				self.resetAll();
			}
		})
	}

	self.resetAll = function () {
		for(var prop in self.selectedFilters) {
			var fitem = self.selectedFilters[prop];
			fitem.reset();
		}
	}

}) ;
var LNToolbar = LNBase.extend(function() {
	// private variable
	var self = this,
		$ = jQuery;

	self.type = 'LNToolbar';

	self.options = null;
	self.defaultOptions = {
		template: 'product-toolbar',
		class: 'products-toolbar',
		rerenderWhenUpdate: true
	};

	self.lnItems = null;

	self.startIdx = 0;
	self.endIdx = 0;
	self.totalItems = 0;
	self.page = 0;
	self.itemPerPage = 0;
	self.nextPage = null;
	self.sortDir = '';

	self.constructor = function (options) {
		self.super.constructor(options);
		// get from config
		self.sortByOptions = self.options.config.sortByOptions;
		self.productsPerPageAllowed = self.options.config.productsPerPageAllowed;
		self.itemPerPage = self.productsPerPageAllowed[0];
		self.autopage = self.options.config.autopage;
	}

	self.beforeRender = function () {
		self.startIdx = self.lnItems.matchedIds.length ? self.lnItems.startIdx:0;
		self.endIdx = self.lnItems.endIdx;
		self.totalItems = self.lnItems.matchedIds.length;
		self.curPage = parseInt(self.lnItems.page);
		self.itemPerPage = self.lnItems.itemPerPage;
		// calculate pages, prevPage, nextPage
		var pages = Math.ceil(self.totalItems / self.itemPerPage);
		self.startPage = 1;
		self.endPage = pages;
		self.prevPage = self.curPage > 1 ? self.curPage - 1 : 1;
		self.nextPage = self.curPage < pages ? self.curPage + 1 : pages;

		self.pages = null;
		if (pages > 1) {
			self.pages = [];
			var start, end;
			start = self.curPage - 2;
			if (start < 1) start = 1;
			end = 5 + start;
			if (end > pages) {
				end = pages;
				start = end - 5;
				if (start < 1) start = 1;
			}

			for (var i=start; i<=end; i++) self.pages.push(i);
		}
		// if (self.lnItems.sortField.search('.value') !== -1)
		// 	self.lnItems.sortField = self.lnItems.sortField.replace('.value', '.frontend_value');
		self.sortField = self.lnItems.sortField;
		self.sortDir = self.lnItems.sortDir == 'desc' ? 'asc' : 'desc';
	}

	// Filter base on list data items
	self.addItems = function (lnItems) {
		self.lnItems = lnItems;
	}

	self.setPage = function (p) {
		self.page = p;
	}

	self.afterRender = function () {
		if (!self.actionHandled) {
			self.actionHandled = true;
			self.$item.parent().on('click', function (e) {
				var $btn = $(e.target).closest('[data-action]'),
					action = 'do' + $btn.data('action');
				if (!self[action]) return false;
				var value = $btn.data('value');
				self[action](value);
				return false;
			}).on('change', function (e) {
				var $elem = $(e.target);
				if ($elem.prop('tagName') == 'SELECT') {
					if (jQuery($elem).hasClass('sorter-options'))
						addFilterWarperClass(jQuery($elem));
					var $btn = $elem.find(":selected"),
						action = 'do' + $btn.data('action');
					if (!self[action]) return false;
					var value = $btn.data('value');
					self[action](value);
					return false;
				}
			});
		}
	}

	self.dopage = function (value) {
		if (value != self.lnItems.page){
			self.lnItems.setPage(value).updateRender();
		}
	}

	self.dosort = function (value) {
		if (value != self.lnItems.sortField) {
			self.lnItems.sort(value).setMatchedItems(self.lnItems.matchedIds).updateRender();
		}
	}

	self.dosortdir = function (value) {
		var sorDir = value == 'desc' ? 'desc' : 'asc';
		self.lnItems.sort(undefined, sorDir).setMatchedItems(self.lnItems.matchedIds).updateRender();
	}

	self.dolimiter = function (value) {
		if (value != self.lnItems.itemPerPage) {
			self.lnItems.setLimiter(value).updateRender();
		}
	}

	self.load = function () {
		var qs = self.getQS();
		// set default toolbar value
		qs.setDefault({
			page: 1,
			limiter: self.productsPerPageAllowed[0],
			sort: self.sortByOptions[0].field,
			sortdir: 'desc'
		})
		
		//update query string for default sorting
		if ( !qs.qs.sort ) qs.qs.sort = ja_default_sort;
		if ( !qs.qs.sortdir ) qs.qs.sortdir = ja_sort_by;
		
		for (var name in qs.qs) {
			var value = qs.qs[name],
				action = 'do' + name;
			if (self[action]) {
				self[action](value);
			}
		}
	}

});