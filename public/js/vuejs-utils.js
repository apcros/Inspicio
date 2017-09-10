function initDynamicVue(element,data_key, data_val) {
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
		methods: {
			refreshData: function(data){
				this['computed_'+data_key] = data;
				this.$forceUpdate();
			}
		}
	});
}

function updateOrCreateVue(name,element, data_key, data_val) {
	if(available_vues[name] != undefined) {
		available_vues[name].refreshData(data_val);
	}else {
		available_vues[name] = initDynamicVue(element, data_key, data_val);
	}
}

$(document).ready(function() {
	available_vues = {};
});