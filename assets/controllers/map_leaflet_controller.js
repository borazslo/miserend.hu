import { Controller } from '@hotwired/stimulus';
import leaflet from 'leaflet'

import 'leaflet/dist/leaflet.css'

import activeRomanPoiIcon from '../img/marker_church_rm_inv.png'
import activeGreekPoiIcon from '../img/marker_church_gr_inv.png'
import inactiveRomanPoiIcon from '../img/marker_church_rm.png'
import inactiveGreekPoiIcon from '../img/marker_church_gr.png'

export default class MapLeafletController extends Controller {
    static targets = ['maplink']

    center = null
    location = null
    boundary = null

    activeRomanLayer = null
    activeGreekLayer = null
    inactiveLayer = null

    connect() {
        this.map = leaflet.map('mapid')
        this.map.invoker = this

        if (this.element.dataset.centerPoint) {
            this.center = this.element.dataset.centerPoint.split('x').map(coord => coord.length > 0 ? parseFloat(coord) : null)
        }

        if (this.element.dataset.boundary) {
            this.boundary = this.element.dataset.boundary
        }

        this.init()
    }

    get churchId() {
        return this.element.dataset.churchId*1 ?? null
    }

    init() {
        this.initLogo()
        this.initViewport()
        this.initTileLayers()
        this.initDiocesesLayer()
        this.initLocation()
        this.initLayerControls()

        this.map.on('moveend', this.mapDidMove);

        this.fetchVisitorLocation()
    }

    initLogo() {
        let logo = leaflet.control({position: 'bottomright'});
        logo.onAdd = this.logoWillAdd
        logo.addTo(this.map);
    }

    logoWillAdd(map) {
        let div = leaflet.DomUtil.create('div', 'myclass');
        div.innerHTML= '<div class="leaflet-bar"><a id="maplink" href="/map/" data-map-leaflet-target="maplink">  <i class="fac fa-splat"></i><i class="fa fa-expand-arrows-alt" title=""></i></a></div>';
        return div;
    }

    initViewport() {
        this.map.on('load', this.mapDidMove);
        this.map.on('load', this.boundaryWillLoad);

        if (this.center) {
            this.map.setView([this.center[0], this.center[1]], this.center[2] ? this.center[2] : 13)
        } else if (this.location) {
            this.map.setView([this.location[0], this.location[1]], this.location[2] ? this.location[2] : 13)
        } else {
            this.map.setView([47.5, 19.05 ], 13)
        }
    }

    initTileLayers() {
//https://leaflet-extras.github.io/leaflet-providers/preview/
        /*let CartoDB_Voyager = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 19
        });*/

        /*var Stamen_Terrain = 		L.tileLayer('https://stamen-tiles-{s}.a.ssl.fastly.net/terrain/{z}/{x}/{y}{r}.{ext}', {
            attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            subdomains: 'abcd',
            minZoom: 0,
            maxZoom: 18,
            ext: 'png'
        });*/

        let OpenStreetMapMapnik = leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        OpenStreetMapMapnik.addTo(this.map);
    }

    initLocation() {
        if (this.location === null) {
            return
        }

        let greenIcon = new leaflet.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [27, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        })

        let marker = leaflet.marker([this.location[0], this.location[1]], {icon: greenIcon})
        marker.addTo(this.map)
    }

    initDiocesesLayer() {
        // {% if dioceseslayer %}
        // var diocesesLayer = L.geoJSON({{ dioceseslayer.geoJson|raw }}, {
        //     onEachFeature: function (feature, layer) {
        //         /*layer.setText('maci maci', {repeat: true, offset: -5});* /
        //     },
        //     style: {
        //         fillColor: 'blue',
        //             weight: 3,
        //             opacity: 0.4,
        //             color: 'blue',
        //             dashArray: '3',
        //             fillOpacity: 0.2,
        //             fillRule: null,
        //             fill: null,
        //     }
        //     /* onEachFeature: onEachFeature * /
        // });
        // diocesesLayer.addTo(mymap);
        // layerControls['Római katolikus egyházmegyék'] = diocesesLayer;
        // {% endif %}
    }

    initLayerControls() {

        this.activeRomanLayer = leaflet.geoJSON(null, {
            pointToLayer: this.layerIcon,
            onEachFeature: this.onEachFeature
        }).addTo(this.map);

        this.activeGreekLayer = leaflet.geoJSON(null, {
            pointToLayer: this.layerIcon,
            onEachFeature: this.onEachFeature
        }).addTo(this.map);

        this.inactiveLayer = leaflet.geoJSON(null, {
            pointToLayer: this.layerIcon,
            onEachFeature: this.onEachFeature
        }).addTo(this.map);

        let layerControls = {
            'Római katolikus aktív templomok': this.activeRomanLayer,
            'Görögkatolikus aktív templomok': this.activeGreekLayer,
            'Templomok és misézőhelyek rendszeres szentmisék nélkül': this.inactiveLayer,
        };
        leaflet.control.layers( null, layerControls).addTo(this.map);
    }

    getCurrentUrl() {
        let zoom = this.map.getZoom()
        let center = this.map.getCenter()
        let queryParams = {
            map: `${zoom}/${center.lat}/${center.lng}`
        }
        if (this.map.invoker.churchId) {
            queryParams['tid'] = this.map.invoker.churchId;
        }
        if (this.boundary) {
            queryParams['boundary'] = this.map.invoker.boundary;
        }

        return `/terkep?${(new URLSearchParams(queryParams)).toString()}`
    }

    updateMapLink() {
        let url = this.getCurrentUrl()

        if (this.maplinkTarget) {
            this.maplinkTarget.href = url
        }
        if (window.location.pathname === '/terkep') {
            history.replaceState(null, '', url);
        }
    }

    clearLayers() {
        if (this.activeRomanLayer) {
            this.activeRomanLayer.clearLayers();
        }
        if (this.activeGreekLayer) {
            this.activeGreekLayer.clearLayers();
        }
        if (this.inactiveLayer) {
            this.inactiveLayer.clearLayers();
        }
    }

    async mapDidMove(event) {
        let map = event.target
        let box = map.getBounds()
        let params = [
            box['_southWest']['lat'],
            box['_southWest']['lng'],
            box['_northEast']['lat'],
            box['_northEast']['lng'],
        ].join(';')

        map.invoker.updateMapLink()
        let zoom = map.getZoom()

        if (zoom < 11) {
            map.invoker.clearLayers()
            return
        }

        let churchId = map.invoker.churchId
        const response = await fetch(`/ajax/churchesinbbox?bbox=${params}`);

        try {
            const data = await response.json();

            if (!data) {
                return
            }

            map.invoker.clearLayers()

            let items = []
            for (let poi of data) {
                let current = churchId ?? -1

                if (current !== poi.id) {
                    let popupContent = `<a href="/templom/${poi.id.toString()}">${poi.nev}</a>`

                    if (poi.thumbnail) {
                        popupContent += `<br/><img src="https://miserend.hu/${poi.thumbnail}" />`
                    }

                    var geojsonFeature = {
                        type: "Feature",
                        properties: {
                            name: poi.nev,
                            popupContent: popupContent,
                            active: poi.active,
                            denomination: poi.denomination,
                        },
                        geometry: {
                            type: "Point",
                            coordinates: [poi.lon, poi.lat]
                        },
                    };

                    if (poi.active === 1) {
                        if (poi.denomination === 'roman_catholic') {
                            map.invoker.activeRomanLayer.addData(geojsonFeature);
                        } else if (poi.denomination === 'greek_catholic') {
                            map.invoker.activeGreekLayer.addData(geojsonFeature);
                        }
                    } else {
                        map.invoker.inactiveLayer.addData(geojsonFeature);
                    }
                }
            }
        } catch (e) {
            console.error(e)
        }
    }

    static iconUrlWithFeature(feature) {
        if (feature.properties.active === 1) {
            if (feature.properties.denomination === 'roman_catholic') {
                return activeRomanPoiIcon;
            } else if (feature.properties.denomination === 'greek_catholic') {
                return activeGreekPoiIcon;
            }
        } else {
            if (feature.properties.denomination === 'roman_catholic') {
                return inactiveRomanPoiIcon;
            }
            else if (feature.properties.denomination === 'greek_catholic') {
                return inactiveGreekPoiIcon;
            }
        }

        return 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png';
    }

    layerIcon(feature, latLng) {
        let iconUrl = MapLeafletController.iconUrlWithFeature(feature)

        let greenIcon = new leaflet.Icon({
            iconUrl:  iconUrl,
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [27, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        return leaflet.marker(latLng, {
            icon: greenIcon
        });
    }

    async boundaryWillLoad() {
        if (this.boundary === null) {
            return
        }

        try {
            const response = await fetch(`/ajax/boundarygeojson?osm=${this.boundary}`);
            const data = await response.json();

            /* Feltételes formázásra példa itt: http://jsfiddle.net/hx5pxdt8/ */
            let boundaryLayer = leaflet.geoJSON(data, {
                style: {
                    fillColor: 'blue',
                    weight: 2,
                    opacity: 1,
                    color: 'white',
                    dashArray: '3',
                    fillOpacity: 0.2,
                }
            });
            boundaryLayer.addTo(this.map);
            this.map.fitBounds(boundaryLayer.getBounds());
        } catch (e) {
        }
    }

    onEachFeature(feature, layer) {
        // does this feature have a property named popupContent?
        if (feature.properties && feature.properties.popupContent) {
            layer.bindPopup(feature.properties.popupContent,{
                maxWidth: "auto",
                autoPan: false
            });
        }
        layer.on('mouseover', function(event){
            layer.openPopup();
        });
    }

    fetchVisitorLocation() {
        if (this.center !== null || this.location !== null) {
            return
        }

        if (!navigator.geolocation) {
            return
        }

        navigator.geolocation.getCurrentPosition(this.visitorLocationDidLoad.bind(this));
    }

    visitorLocationDidLoad(position) {
        this.map.flyTo(new leaflet.LatLng(position.coords.latitude, position.coords.longitude))
    }
}