var Geo = new Class({
    token: '<?php echo $this->config['mapbox_token']; ?>',
    zoom: 9,
    style: 'outdoors-v10',
    callbacks: {},
    markers: [],

    initialize: function(wrapper, location) {
        var self = this;

        this.map    = this.createMapInstance(wrapper);
        this.map.addControl(new mapboxgl.NavigationControl())
                .on('load', function() {
            self.callbacks.load && self.callbacks.load();
        });

    },

    setCallback: function(type, callback) {
        this.callbacks[type] = callback;
    },

    createMapInstance: function(wrapper) {
        mapboxgl.accessToken = this.token;

        return map = new mapboxgl.Map({
            container: wrapper,
            style: 'mapbox://styles/mapbox/' + this.style,
            zoom: this.zoom,
            bearingSnap: false,
        });
    },

    addLayer: function(layer) {
        this.map.addLayer(layer);
    },

    setMarker: function(location, content) {
        var marker = new mapboxgl.Marker().setLngLat(location);

        if (content) {
            marker.setPopup(new mapboxgl.Popup({offset: 25}).setHTML(content));
        }

        marker.addTo(this.map);

        this.markers.push(marker);

        return this.markers.length - 1;
    },

    setDragMarker: function() {
        var self = this;

        this.marker = new mapboxgl.Marker({'draggable': true}).on('dragend', function() {
            self.callbacks.markerDragEnd();
        });
    },

    getLocationFromClient: function(callback) {
        var self = this;
        navigator.geolocation.getCurrentPosition(function(location) {
            if (location.coords) {
                callback(location.coords);
            }
        });
    },

    setBounds: function(boundaries) {
        this.map.fitBounds(boundaries, {padding: 50, linear: true});
    },

    getBounds: function() {
        return this.map.getBounds();
    },

    setLocation: function(location) {
        if (location && (typeof location.length == 'undefined' || location.length != 0)) {
            location = this.normalizeLocation(location);
            this.map.setCenter(location);

            if (this.marker) {
                this.marker.setLngLat(location).addTo(this.map);
            }
        } else {
            this.getLocationFromClient((function(location) {return this.setLocation(location)}).bind(this));
        }
    },

    getLocation: function() {
        var location = this.marker.getLngLat();

        if (location) {
            return this.normalizeLocation(location);
        } else {
            return false;
        }
    },

    normalizeLocation: function(location) {
        var loc = {
            'longitude': 0,
            'latitude': 0,
            'lng': 0,
            'lat': 0
        };

        switch (typeof location) {
            case 'object':
                if (location.length) {
                    loc.longitude = location[0];
                    loc.lng       = location[0];
                    loc.latitude  = location[1];
                    loc.lat       = location[1];
                } else if (location.lng) {
                    loc.longitude = location.lng;
                    loc.latitude  = location.lat;
                    loc.lng       = location.lng;
                    loc.lat       = location.lat;
                } else if (location.latitude) {
                    loc.longitude = location.longitude;
                    loc.latitude  = location.latitude;
                    loc.lng       = location.longitude;
                    loc.lat       = location.latitude;
                } else {
                    loc = false;
                }

                break;
        }

        return loc;
    }
});
