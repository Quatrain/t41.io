if (! window.wa) {
        window.wa = {};
}


window.wa.address = function(obj) {
        
        this.obj = obj;
        
        this.communes = [];
        
        
        this.init = function(communes) {
            jQuery('#road').on('change', jQuery.proxy(this, 'switchRoad'));
            jQuery('#city').on('change', jQuery.proxy(this, 'switchCity'));
            jQuery('#country').on('change', jQuery.proxy(this, 'switchCountry'));
                
                //this.switchPays();
                setTimeout(function() { 
                	if (maell.view.get('_city') != false) {
                		maell.view.get('_city').callbacks.preQuery = function(params) {
                			if (! params.data.extra) params.data.extra = {};
                            	params.data.extra.country = jQuery('#country').val();
                                return params;
                        };
                    };
                    
                	if (maell.view.get('_road') != false) {
                		maell.view.get('_road').callbacks.preQuery = function(params) {
                			if (! params.data.extra) params.data.extra = {};
                            	params.data.extra.city = jQuery('#city').val();
                                return params;
                        };
                    };
                    
                	if (maell.view.get('_plot') != false) {
                		maell.view.get('_plot').callbacks.preQuery = function(params) {
                			if (! params.data.extra) params.data.extra = {};
                            	params.data.extra.road = jQuery('#road').val();
                                return params;
                        };
                    };                  
                },1000);
                this.switchCountry();
        };

        this.switchRoad = function(obj) {
        	if (! obj || obj.currentTarget.value == '_NONE_') {
        		this.showHideRow('plot','hide');
        		jQuery('#_road').focus();
        	} else {
        		jQuery.ajax('/api/v1/plots/?_road=' + jQuery('#road').val() + '&token=fa8c9c082de9d2542b993cad0d887d29205d5bd36836a064', {type:'GET', success:jQuery.proxy(this,'parsePlots')})
        		this.showHideRow('plot','show');
        		jQuery('#_plot').focus();
        	}
        };
        
        
        this.parsePlots = function(data) {
        	jQuery('#_plot').hide();
        	var sel = jQuery('<select>');
        	for (var key in data.data) {
        	    sel.append(jQuery('<option>').attr('value', key).text(data.data[key]['number']));
        	}
    	    sel.append(jQuery('<option>').attr('value', 'missing').text("Autre numéro"));        	
        	jQuery('#elem_plot').append(sel);
        };
        
        
        this.switchCity = function(obj) {
        	if (! obj || obj.currentTarget.value == '_NONE_') {
        		this.showHideRow('road,plot','hide');
        		jQuery('#_city').focus();
        	} else {
        		this.showHideRow('road','show');
        		jQuery('#_road').focus();
        	}
        };
        
        
        this.switchCountry = function(obj) {
        	if (! obj || obj.currentTarget.value == '_NONE_') {
        		this.showHideRow('city,road,plot','hide');
        		jQuery('#_country').focus();
        	} else {
        		this.showHideRow('city','show');
        		jQuery('#_city').focus();
        	}
        };
        
        
        this.showHideRow = function(id, action) {
                var ids = id.split(',');
                for (var id in ids) {
                        var elems = jQuery("[id$='" + ids[id] + "']");

                        switch (action) {
                        
                                case 'show':
                                        elems.show();
                                        break;
                                        
                                case 'hide':
                                        elems.hide();
                                        elems.val('');
                                        break;
                                        
                                default:
                                        elems.toggle(); 
                                        break;
                        }
                }
        };
};