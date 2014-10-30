(function() {
    var GeoJSONLayers = function() {
        var handler = function()
        {
            this.clear();
        };
 
        handler.prototype.clear = function()
        {
            this.json = {
                type : "FeatureCollection",
                features : []
            };
            this.layerStyles = {};
        };
 
        handler.prototype.addLayer = function(layerID, geoJSON, styleCallback)
        {

            this.layerStyles[layerID] = styleCallback(geoJSON);

            // tag features with their assigned layer
            geoJSON.features.forEach(function(feature, i) {
                feature.properties.__LAYERID__ = layerID;
            });
 
            // merge into current objects
            Array.prototype.push.apply(this.json.features, geoJSON.features);
        };
 
        handler.prototype.removeLayer = function(layerID)
        {
            var feats = this.json.features,
                i = 0;
 
            delete this.layerStyles[layerID];
 
            // remove relevant geoJSON objects as well
            for (; i < feats.length; ++i) {
                feature = feats[i];
                if (feature.properties.__LAYERID__ == layerID) {
                    feats.splice(i, 1);
                    --i;
                }
            }
        };
 
        handler.prototype.__handleStyle = function(feature)
        {
            if (feature.properties['__LAYERID__'] === undefined) {
                return {};
            }

            if (typeof this.layerStyles[feature.properties.__LAYERID__] === 'function') {
                return this.layerStyles[feature.properties.__LAYERID__](feature);
            } else if (typeof this.layerStyles[feature.properties.__LAYERID__] === 'object') {
                return this.layerStyles[feature.properties.__LAYERID__];
            }
        };
 
        // return geoJSON data for assignment to scope
        handler.prototype.get = function()
        {
            var self = this;
 
            return {
                data: this.json,
                style: function(feature) {
                    return self.__handleStyle.call(self, feature);
                },
                resetStyleOnMouseout: true
            };
        };
 
        return handler;
    };
    

    GeoJSONLayers.$inject = [];
    angular.module('mapocApp').factory('GeoJSONLayers', GeoJSONLayers);

}());