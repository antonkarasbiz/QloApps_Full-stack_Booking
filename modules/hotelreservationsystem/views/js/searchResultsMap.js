/**
* 2010-2022 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through LICENSE.txt file inside our module
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to CustomizationPolicy.txt file inside our module for more information.
*
* @author Webkul IN
* @copyright 2010-2022 Webkul IN
* @license LICENSE.txt
*/

function initMap() {
    const hotelLocation = {
        lat: Number(hotel_location.latitude),
        lng: Number(hotel_location.longitude),
    };

    const map = new google.maps.Map($('#search-results-wrap .map-wrap').get(0), {
        zoom: 10,
        center: hotelLocation,
        disableDefaultUI: true,
        fullscreenControl: true,
        mapId: PS_MAP_ID
    });

    let icon = document.createElement('img');
    icon.src = PS_STORES_ICON;
    icon.style.width = '24px';
    icon.style.height = '24px';

    const marker = new google.maps.marker.AdvancedMarkerElement({
        map: map,
        position: hotelLocation,
        title: location.hotel_name,
        content: icon,
    });

    marker.query = location.query || null;
    marker.latitude = hotelLocation.lat;
    marker.longitude = hotelLocation.lng;

    marker.addListener('click', function() {
        let query = '';
        if (this.query) {
            query = this.query;
        } else if (this.latitude && this.longitude) {
            query = `${this.latitude},${this.longitude}`;
        }

        if (query) {
            window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`, '_blank');
        }
    });
}

