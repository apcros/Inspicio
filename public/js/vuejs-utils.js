function initDynamicVue(element,dataKey, dataVal, onRefresh = null) {
	var raw_data = {};
	raw_data[dataKey] = dataVal;
	var computed_data = {};
	computed_data["computed_"+dataKey]=
		 {
			get: function() {
				return this[dataKey];
			},
			set: function(v) {
				this[dataKey] = v;
			}
		};
	return new Vue({
		el: element,
		data: raw_data,
		computed: computed_data,
		updated: function() {
			this.$nextTick(function() {
				if(onRefresh) {
					onRefresh();
				}
			});
		},
		methods: {
			refreshData: function(data){
				this["computed_"+dataKey] = data;
				this.$forceUpdate();
				if(onRefresh) {
					onRefresh();
				}
			}
		}
	});
}

function updateOrCreateVue(name,element, dataKey, dataVal, onRefresh = null) {
	if(available_vues[name] != undefined) {
		available_vues[name].refreshData(dataVal);
	}else {
		var vue = initDynamicVue(element, dataKey, dataVal, onRefresh);
		available_vues[name] = vue;
	}
}

function startLoading(btn) {
	var currentHtml = $(btn).html();
	$(btn).attr("disabled",true);
	var currentOnclick = $(btn).attr("onclick");
	$(btn).removeAttr("onclick");
	$(btn).addClass("disabled");
	$(btn).html("<i class=\"fa fa-refresh fa-spin\" aria-hidden=\"true\"></i>");

	return {
		onclick: currentOnclick,
		html: currentHtml
	};
}

function stopLoading(btn, state) {
	$(btn).removeAttr("disabled");
	$(btn).removeClass("disabled");
	$(btn).attr("onclick",state.onclick);
	$(btn).html(state.html);
}
$(document).ready(function() {
	available_vues = {};
});