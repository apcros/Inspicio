function initDynamicVue(element,data_key, data_val, on_refresh = null) {
	var raw_data = {};
	raw_data[data_key] = data_val;
	var computed_data = {};
	computed_data['computed_'+data_key]=
		 {
			get: function() {
				return this[data_key];
			},
			set: function(v) {
				this[data_key] = v;
			}
		};
	return new Vue({
		el: element,
		data: raw_data,
		computed: computed_data,
		updated: function() {
			this.$nextTick(function() {
				if(on_refresh) {
					on_refresh();
				}
			})
		},
		methods: {
			refreshData: function(data){
				this['computed_'+data_key] = data;
				this.$forceUpdate();
				if(on_refresh) {
					on_refresh();
				}
			}
		}
	});
}

function updateOrCreateVue(name,element, data_key, data_val, on_refresh = null) {
	if(available_vues[name] != undefined) {
		available_vues[name].refreshData(data_val);
	}else {
		available_vues[name] = initDynamicVue(element, data_key, data_val, on_refresh);
	}
}

function startLoading(btn) {
	var current_html = $(btn).html();
	$(btn).attr('disabled',true);
	var current_onclick = $(btn).attr('onclick');
	$(btn).removeAttr('onclick');
	$(btn).addClass('disabled');
	$(btn).html('<i class="fa fa-refresh fa-spin aria-hidden="true"></i>');

	return {
		onclick: current_onclick,
		html: current_html
	};
}

function stopLoading(btn, state) {
	$(btn).removeAttr('disabled');
	$(btn).removeClass('disabled');
	$(btn).attr('onclick',state.onclick);
	$(btn).html(state.html);
}
$(document).ready(function() {
	available_vues = {};
});