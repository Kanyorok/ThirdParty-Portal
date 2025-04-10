const nightMode = new google.maps.StyledMapType(
        [
            {elementType: "geometry", stylers: [{color: "#242f3e"}]},
            {elementType: "labels.text.stroke", stylers: [{color: "#242f3e"}]},
            {elementType: "labels.text.fill", stylers: [{color: "#746855"}]},
            {
                featureType: "administrative.locality",
                elementType: "labels.text.fill",
                stylers: [{color: "#d59563"}],
            },
            {
                featureType: "administrative",
                elementType: "geometry.stroke",
                stylers: [{color: "#170101"}],
            },
            {
                featureType: "poi.business",
                stylers: [{visibility: "off"}],
            },
            {
                featureType: "poi",
                stylers: [{visibility: "off"}],
            },
            {
                featureType: "poi.park",
                elementType: "geometry",
                stylers: [{color: "#263c3f"}],
            },
            {
                featureType: "poi.park",
                elementType: "labels.text.fill",
                stylers: [{color: "#6b9a76"}],
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{color: "#38414e"}],
            },
            {
                featureType: "road",
                elementType: "geometry.stroke",
                stylers: [{color: "#212a37"}],
            },
            {
                featureType: "road",
                elementType: "labels.text.fill",
                stylers: [{color: "#9ca5b3"}],
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{color: "#746855"}],
            },
            {
                featureType: "road.highway",
                elementType: "geometry.stroke",
                stylers: [{color: "#1f2835"}],
            },
            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{color: "#f3d19c"}],
            },
            {
                featureType: "transit",
                elementType: "geometry",
                stylers: [{color: "#2f3948"}],
            },
            {
                featureType: "transit.station",
                stylers: [{visibility: "off"}],
                /*elementType: "labels.text.fill",
            stylers: [{ color: "#d59563" }]*/
            },
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{color: "#17263c"}],
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{color: "#515c6d"}],
            },
            {
                featureType: "water",
                elementType: "labels.text.stroke",
                stylers: [{color: "#17263c"}],
            },
        ],
        {name: "Night"}
    ),
    styledMapType = new google.maps.StyledMapType(
        [
            {elementType: "geometry", stylers: [{color: "#ebe3cd"}]},
            {elementType: "labels.text.fill", stylers: [{color: "#523735"}]},
            {elementType: "labels.text.stroke", stylers: [{color: "#f5f1e6"}]},
            {
                featureType: "administrative",
                elementType: "geometry.stroke",
                stylers: [{color: "#c9b2a6"}],
            },
            {
                featureType: "administrative.land_parcel",
                elementType: "geometry.stroke",
                stylers: [{color: "#dcd2be"}],
            },
            {
                featureType: "administrative.land_parcel",
                elementType: "labels.text.fill",
                stylers: [{color: "#ae9e90"}],
            },
            {
                featureType: "landscape.natural",
                elementType: "geometry",
                stylers: [{color: "#dfd2ae"}],
            },
            {
                featureType: "poi",
                elementType: "geometry",
                stylers: [{color: "#dfd2ae"}],
            },
            {
                featureType: "poi",
                elementType: "labels.text.fill",
                stylers: [{color: "#93817c"}],
            },
            {
                featureType: "poi.park",
                elementType: "geometry.fill",
                stylers: [{color: "#a5b076"}],
            },
            {
                featureType: "poi.park",
                elementType: "labels.text.fill",
                stylers: [{color: "#447530"}],
            },
            {
                featureType: "road",
                elementType: "geometry",
                stylers: [{color: "#f5f1e6"}],
            },
            {
                featureType: "road.arterial",
                elementType: "geometry",
                stylers: [{color: "#fdfcf8"}],
            },
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{color: "#f8c967"}],
            },
            {
                featureType: "road.highway",
                elementType: "geometry.stroke",
                stylers: [{color: "#e9bc62"}],
            },
            {
                featureType: "road.highway.controlled_access",
                elementType: "geometry",
                stylers: [{color: "#e98d58"}],
            },
            {
                featureType: "road.highway.controlled_access",
                elementType: "geometry.stroke",
                stylers: [{color: "#db8555"}],
            },
            {
                featureType: "road.local",
                elementType: "labels.text.fill",
                stylers: [{color: "#806b63"}],
            },
            {
                featureType: "transit.line",
                elementType: "geometry",
                stylers: [{color: "#dfd2ae"}],
            },
            {
                featureType: "transit.line",
                elementType: "labels.text.fill",
                stylers: [{color: "#8f7d77"}],
            },
            {
                featureType: "transit.line",
                elementType: "labels.text.stroke",
                stylers: [{color: "#ebe3cd"}],
            },
            {
                featureType: "transit.station",
                elementType: "geometry",
                stylers: [{color: "#dfd2ae"}],
            },
            {
                featureType: "water",
                elementType: "geometry.fill",
                stylers: [{color: "#b9d3c2"}],
            },
            {
                featureType: "water",
                elementType: "labels.text.fill",
                stylers: [{color: "#92998d"}],
            },
        ],
        {name: "Retro"}
    );

let stationaryIcon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 7,
        rotation: 0,
        fillColor: "#ff8604",
        strokeColor: "#ff8604",
        fillOpacity: 0.8,
    },
    movingIcon = {
        path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
        scale: 4,
        rotation: 0,
        fillColor: "#ff8604",
        strokeColor: "#ff8604",
        fillOpacity: 0.8,
    },
    filter,
    markers = [],
    db_call,
    interval,
    cluster,
    clockInterval,
    hasCluster = false,
    timestamp = moment().format(),
    animatedSeconds = 18,
    filterRange = [-1000, 4000],
    categoriesContent = "",
    map;

createMap = function (
    options = {
        element: "map",
        center: {lat: 0.648392, lng: 37.904596},
        zoom: 5,
        map: {
            type: "retro",
            filter: {
                categories: false,
                clock: false,
                altitude: false,
            },
            control: {
                cluster: false,
                map_type: true
            }
        },
    }
) {
    filter = options.map.filter;
    hasCluster = options.map.control.cluster;

    const map = new google.maps.Map(document.getElementById(options.element), {
        center: options.center,
        zoom: options.zoom,
        mapTypeControl: options.map.control.map_type,
        mapTypeControlOptions: {
            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
            position: google.maps.ControlPosition.TOP_CENTER,
            mapTypeIds: ["roadmap", "satellite", "hybrid", "terrain", "night", "retro"],
        },

        zoomControl: true,
        zoomControlOptions: {
            position: google.maps.ControlPosition.LEFT_CENTER,
        },
        scaleControl: true,
        rotateControl: true,
        rotateControlOptions: {
            position: google.maps.ControlPosition.RIGHT_BOTTOM,
        },
        streetViewControl: false,
        fullscreenControl: false, //todo sort this out error because of popup
    });
    map.mapTypes.set("night", nightMode);
    map.mapTypes.set("retro", styledMapType);
    map.setMapTypeId(options.map.type);

    if (hasCluster) {
        cluster = new MarkerClusterer(map, markers, {imagePath: "https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m"});
    }
    if (filter.clock) {
        const dateTimeControlDiv = document.createElement("div");
        new DateTimeControl(dateTimeControlDiv, map);
        map.controls[google.maps.ControlPosition.LEFT_TOP].push(dateTimeControlDiv);
    }
    if (filter.categories) {
        const categoryControlDiv = document.createElement("div");
        new FilterCategoryControl(categoryControlDiv, map);
        map.controls[google.maps.ControlPosition.BOTTOM_CENTER].push(categoryControlDiv);
    }
    if (filter.altitude) {
        const filterControlDiv = document.createElement("div");
        new FilterControl(filterControlDiv, map);
        filterControlDiv.index = 1;
        map.controls[google.maps.ControlPosition.RIGHT_CENTER].push(filterControlDiv);
    }

    setMapColour();

    return map;
}

addPolygon = function (options = {
    coordinates: [//,,
        {lat: -1.290090, lng: 36.816893},
        {lat: -1.290004, lng: 36.817507},
        {lat: -1.290417, lng: 36.816828},
        {lat: -1.290401, lng: 36.817450}
    ],
    styles: {
        border: "#FF0000",
        weight: 3,
        colour: "#FF0000"
    },
    map: window.map
}) {
    const polygon = new google.maps.Polygon({
        paths: options.coordinates,
        strokeColor: options.styles.border,
        strokeOpacity: 0.8,
        strokeWeight: options.styles.weight,
        fillColor: options.styles.colour,
        fillOpacity: 0.35,
    });

    polygon.setMap(options.map);
}

addRectangle = function (options = {
    bounds: {
        north: -1.290090,
        south: -1.290004,
        east: 36.816893,
        west: 36.817507
    },
    styles: {
        border: "#FF0000",
        weight: 3,
        colour: "#FF0000"
    },
    map: window.map
}) {
    new google.maps.Rectangle({
        strokeColor: options.styles.border,
        strokeOpacity: 0.8,
        strokeWeight: options.styles.weight,
        fillColor: options.styles.colour,
        fillOpacity: 0.35,
        map,
        bounds: options.bounds
    });

}

addPolyline = function (options = {
    coordinates: [
        {lat: -1.290090, lng: 36.816893},
        {lat: -1.290004, lng: 36.817507},
        {lat: -1.290417, lng: 36.816828},
        {lat: -1.290401, lng: 36.817450}
    ],
    styles: {
        weight: 3,
        colour: "#FF0000"
    },
    map: window.map
}) {

    const path = new google.maps.Polyline({
        path: options.coordinates,
        geodesic: true,
        strokeColor: options.styles.colour,
        strokeOpacity: 0.8,
        strokeWeight: options.styles.weight,
    });

    path.setMap(map);
    return path;
}

addCircle = function (options = {
    center: {lat: -1.290090, lng: 36.816893},
    radius: 100000,
    styles: {
        border: "#FF0000",
        weight: 3,
        colour: "#FF0000"
    },
    map: window.map
}) {

    return new google.maps.Circle({
        strokeColor: options.styles.border,
        strokeOpacity: 0.8,
        strokeWeight: options.styles.weight,
        fillColor: options.styles.colour,
        fillOpacity: 0.35,
        map,
        center: options.center,
        radius: options.radius
    });

}

addMarker = function (device) {
    const marker = new google.maps.Marker({
        id: device.id,
        position: {lat: device.latitude, lng: device.longitude},
        altitude: device.altitude,
        sensor_id: device.sensor_id,
        flight_id: device.flight_id,
        category: device.category,
        title: device.pop,
        icon: stationaryIcon,
        draggable: false,
        map: map,
    });


    if (filter.altitude && !(marker.altitude >= filterRange[0] && marker.altitude <= filterRange[1])) {
        marker.setMap(null);
    } else if (filter.categories && !(checkMarkerCategory(marker))) {
        marker.setMap(null);
    } else if (hasCluster) {
        cluster.addMarker(marker);
    }

    markers[device.id] = marker;

    marker.addListener("click", function () {
        markerClicked(marker);
    });
}

addLocationMarker = function (id, position, title, contentString, category) {
    const marker = new google.maps.Marker({
        id: id,
        position: position,
        map: map,
        title: title,
        altitude: 0,
        category: category,
    });

    const infowindow = new google.maps.InfoWindow({
        content: contentString,
    });

    marker.addListener("click", () => {
        infowindow.open({
            anchor: marker,
            map,
            shouldFocus: false,
        });
    });

    if (filter.categories && !(checkMarkerCategory(marker))) {
        marker.setMap(null);
    }
    if (hasCluster) {
        cluster.addMarker(marker);
    }

    markers[id] = marker;
}

markerClicked = function (marker) {
}

setHeight = function () {
}

// Sets the map on all markers in the array.
setMapOnAll = function (contextMap) {
    for (var key in markers) {
        const marker = markers[key];
        if (marker) {
            marker.setMap(contextMap);
            if (hasCluster) {
                if (contextMap == null) {
                    cluster.removeMarker(marker);
                } else {
                    cluster.addMarker(marker);
                }
            }
        }
    }
}

// Removes the markers from the map, but keeps them in the array.
clearMarkers = function () {
    setMapOnAll(null);
}

// Shows any markers currently in the array.
showMarkers = function () {
    setMapOnAll(map);
}

// Deletes all markers in the array by removing references to them.
deleteMarkers = function () {
    clearMarkers();
    markers = [];
    live = false;
    $.slidePanel.hide();
}
pinMap = function (filtered) {
    filtered.forEach(function (device) {
        if (!markers[device.id]) {
            addMarker(device);
        } else {
            const marker = markers[device.id];
            if (parseInt(marker.sensor_id) !== parseInt(device.sensor_id)) {
                let newIcon = movingIcon;
                if (marker.position && device.latitude.toFixed(6) !== marker.position.lat().toFixed(6) && device.longitude.toFixed(6) !== marker.position.lng().toFixed(6)) {
                    newIcon = setIconColor(marker.altitude, device.altitude, newIcon);
                    newIcon.rotation = device.direction;
                    marker.setIcon(newIcon);
                    animatedMove(marker, marker.position, new google.maps.LatLng(device.latitude, device.longitude));
                } else {
                    newIcon = setIconColor(marker.altitude, device.altitude, stationaryIcon);
                    marker.setIcon(newIcon);
                }
                marker.sensor_id = device.sensor_id;
                marker.altitude = device.altitude;
            }
        }
    });
}

//change colour if altitude change by 10ft
setIconColor = function (oldAltitude, newAltitude, icon) {
    const difference = parseFloat(newAltitude) - parseFloat(oldAltitude);
    if (difference > 10) {
        //rising
        icon.fillColor = "#ea5959";
        icon.strokeColor = "#ff0000";
    } else if (difference < -10) {
        //going down
        icon.fillColor = "#00ff1e";
        icon.strokeColor = "#197a04";
    } else {
        //hovering
        icon.fillColor = "#ffffff";
        icon.strokeColor = "#f1d700";
    }
    return icon;
}

removePins = function (data) {
    data.forEach(function (device) {
        var marker = markers[device.id];
        if (marker && marker.flight_id === device.flight_id) {
            if (hasCluster) {
                cluster.removeMarker(marker);
            }
            marker.setMap(null);
            markers[device.id] = null;
            notif({
                msg: '<b><i class="fa fa-stop"></i> ' + device.pop + "</b> Flight Ended",
                type: "success",
                position: "center"
            });

            if (document.getElementById("device_" + device.id)) {
                closeSide();
            }
        }
    });
}

loop = function (timeinms = 5000) {
    clearTimeout(interval);
    interval = setTimeout(function () {
        call();
    }, timeinms);
}

call = function () {
}

animatedMove = function (marker, current, moveto) {
    var lat = current.lat();
    var lng = current.lng();

    var deltalat = (moveto.lat() - current.lat()) / 100;
    var deltalng = (moveto.lng() - current.lng()) / 100;

    if (!animatedSeconds) {
        animatedSeconds = 10; // 10 seconds
    }

    var delay = 10 * animatedSeconds; //4.7; //4.7 seconds -> add to 20 seconds
    for (var i = 0; i < 100; i++) {
        (function (ind) {
            setTimeout(function () {
                var lat = marker.position.lat();
                var lng = marker.position.lng();
                lat += deltalat;
                lng += deltalng;
                latlng = new google.maps.LatLng(lat, lng);
                marker.setPosition(latlng);
            }, delay * ind);
        })(i);
    }
}

FilterControl = function (controlDiv, map) {
    controlDiv.style.opacity = 0.85;
    // Set CSS for the control border.
    var controlUI = document.createElement("div");
    controlUI.style.backgroundColor = "rgb(50, 50, 50)";
    controlUI.style.border = "2px solid rgb(34, 30, 30)";
    controlUI.style.borderRadius = "5px";
    controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
    controlUI.style.cursor = "pointer";
    controlUI.style.userSelect = "none";
    controlUI.style.marginBottom = "22px";
    controlUI.style.marginRight = "10px";
    controlUI.style.textAlign = "center";
    controlUI.title = "Filter with Altitude";
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior.
    var controlContent = document.createElement("div");
    controlContent.id = "contentDiv";
    controlContent.className = "d-none";
    controlContent.style.color = "rgb(236, 220, 220)";
    controlContent.style.fontSize = "10px";
    controlContent.style.width = "77px";
    controlContent.style.minHeight = "150px";
    controlContent.style.marginTop = "10px";
    controlContent.style.marginBottom = "10px";
    controlContent.style.paddingLeft = "5px";
    controlContent.style.paddingRight = "10px";
    controlContent.innerHTML = '<input id="slider" type="text" data-slider-ticks-tooltip="true"/>';
    controlUI.appendChild(controlContent);

    setFilterAltitudeOnMap();
}

FilterCategoryControl = function (controlDiv, map) {
    controlDiv.style.opacity = 0.85;
    controlDiv.style.maxWidth = "70%";
    controlDiv.index = 13000;
    // Set CSS for the control border.
    var controlUI = document.createElement("div");
    controlUI.style.backgroundColor = "rgb(50, 50, 50)";
    controlUI.style.border = "2px solid rgb(34, 30, 30)";
    controlUI.style.borderRadius = "5px";
    controlUI.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
    controlUI.style.cursor = "pointer";
    controlUI.style.marginBottom = "10px";
    controlUI.style.maxHeight = "37px";
    controlUI.style.overflowY = "auto";
    controlUI.style.textAlign = "justified";
    controlUI.title = "Filter with Category";
    controlDiv.appendChild(controlUI);

    var controlContent = document.createElement("div");
    controlContent.id = "contentCategoryDiv";
    controlContent.style.color = "rgb(236, 220, 220)";
    controlContent.style.fontSize = "16px";
    controlContent.innerHTML = '<ul class="list-unstyled list-inline" id="droneCategories"></ul>';
    controlUI.appendChild(controlContent);

    setCategoriesonMap();
}

DateTimeControl = function (controlDiv, map) {
    controlDiv.style.opacity = 0.55;
    controlDiv.style.maxWidth = "70%";
    controlDiv.index = 13000;
    // Set CSS for the control border.
    var controlUI = document.createElement("div");
    //controlUI.style.backgroundColor = 'rgb(50, 50, 50)';
    //controlUI.style.border = '2px solid rgb(34, 30, 30)';
    //controlUI.style.borderRadius = '5px';
    //controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
    controlUI.style.cursor = "pointer";
    //controlUI.style.padding = '10px';
    controlUI.title = "Data Dated on";
    controlDiv.appendChild(controlUI);

    var controlContent = document.createElement("div");
    controlContent.id = "contentDateTimeDiv";
    controlContent.style.color = "rgb(236, 220, 220)";
    controlContent.style.fontSize = "16px";
    controlContent.innerHTML = '<div class="menu"><h4  class="mt-10 text-primary wfnt"><span id="dayPart"></span><span id="yearMonthPart"></span></h4><h2 class="mt-5 text-success" id="timePart"></h2></div>';
    controlUI.appendChild(controlContent);

    startClockonMap();
}

setFilterAltitudeOnMap = function () {
    if (!document.getElementById("contentDiv")) {
        setTimeout(() => {
            setFilterAltitudeOnMap();
        }, 1000);
    } else {
        // MAp has completed Loading
        $("#contentDiv").removeClass("d-none");
        new Slider("#slider", {
            id: "mapSlider",
            min: 0,
            max: 3500,
            range: true,
            step: 10,
            value: [0, 3500],
            ticks_labels: ["0 ft", "700 ft", "1400 ft", "2100 ft", "2800 ft", "3500 ft"],
            ticks: [0, 700, 1400, 2100, 2800, 3500],
            ticks_tooltip: true,
            focus: true,
            orientation: "vertical",
            tooltip_position: "left",
            reversed: true,
        }).on("slideStop", function (values) {
            filterRangeMarkers(values);
        });
    }
}

addCategory = function (id, name) {
    categoriesContent +=
        '<li class="list-inline-item"><div class="checkbox-custom checkbox-primary"><input type="checkbox" value="' +
        "" +
        id +
        '" checked="" id="customCheck' +
        id +
        '" class="toggleCategory"><label for="customCheck' +
        id +
        '">' +
        "" +
        name +
        "</label></div></li>";
}

startClockonMap = function () {
    if (!document.getElementById("contentDateTimeDiv")) {
        setTimeout(() => {
            startClockonMap();
        }, 1000);
    } else {
        clock(moment.unix(timestamp));
    }
}

setCategoriesonMap = function () {
    if (!document.getElementById("contentCategoryDiv")) {
        setTimeout(() => {
            setCategoriesonMap();
        }, 1000);
    } else {
        $("#droneCategories").html(categoriesContent);
        $(".toggleCategory").prop("checked", true);
        $(window).trigger("resize");
        setHeight();

        //fetch drones
        call();
    }
}

filterRangeMarkers = function (range) {
    markers.forEach(function (marker) {
        marker.setMap(null);
        if (hasCluster) {
            cluster.removeMarker(marker);
        }
        if (checkMarkerRange(marker, range) && checkMarkerCategory(marker)) {
            marker.setMap(map);
            if (hasCluster) {
                cluster.addMarker(marker);
            }
        }
    });
    filterRange = range;
}

checkMarkerCategory = function (marker) {
    return $("#customCheck" + marker.category).is(":checked");
}

checkMarkerRange = function (marker, range) {
    return marker.altitude >= range[0] && marker.altitude <= range[1];
}

toggleDroneCategory = function (show, category) {
    markers.forEach(function (marker) {
        if (parseInt(marker.category) === parseInt(category)) {
            if (show && checkMarkerRange(marker, filterRange)) {
                //show
                marker.setMap(map);
                if (hasCluster) {
                    cluster.addMarker(marker);
                }
            } else {
                //hide
                marker.setMap(null);
                if (hasCluster) {
                    cluster.removeMarker(marker);
                }
            }
        }
    });
}

setMapColour = function () {
    setMapStyle();
    $('a[title="Open this area in Google Maps (opens a new window)"]').addClass("d-none");
    $(".gm-style-cc").addClass("d-none");
}

setMapStyle = function () {
    if (!document.querySelector('div[title="Change map style"]')) {
        setTimeout(() => {
            setMapStyle();
        }, 1000);
    } else {
        $('div[title="Change map style"]').addClass("map-element");
    }
}

clock = function (dateGiven) {
    if (!dateGiven) {
        dateGiven = moment();
    }
    let momentNow = dateGiven;
    //clearInterval(clockInterval);
    clockInterval = setInterval(function () {
        momentNow = momentNow.add(1, "seconds");
        $("#yearMonthPart").html(momentNow.format("MMM D, YYYY"));
        $("#timePart").html(momentNow.format("HH:mm:ss"));
        $("#_datetimestamp").val(momentNow.unix());
        timestamp = momentNow.unix();
    }, 1000);
}

dateTimeChanged = function (currentTime) {
    clearInterval(clockInterval);
    $("#_clearAll").val(0);
    $("#selectDateModal").modal("hide");
    db_call.abort();
    deleteMarkers();
    call();
    $("#date_time").val(currentTime.format("DD-MM-YYYY HH:mm"));
    clock(moment.unix(currentTime.unix()));
};

contentDateTimeDivClicked = function () {
    const currentTime = moment();
    $("#date_time").datetimepicker({
        format: "DD-MM-YYYY HH:mm",
        inline: true,
        useCurrent: true,
        sideBySide: true,
        showToday: true,
        maxDate: currentTime,
    });
    $("#selectDateModal").modal("show");
}

$(document).on("click", "#contentDateTimeDiv", function () {
    contentDateTimeDivClicked();
});

$(document).on("click", "#setDateTimeNow", function () {
    const currentTime = moment($("#date_time").val(), "DD-MM-YYYY HH:mm");
    dateTimeChanged(currentTime);
});

$(document).on("click", "#SetNowDateTime", function () {
    const currentTime = moment();
    dateTimeChanged(currentTime);
});

$(window).bind("beforeunload", function () {
    db_call.abort();
});

$(document).on("change", "input.toggleCategory", function () {
    toggleDroneCategory($(this).is(":checked"), $(this).val());
});
