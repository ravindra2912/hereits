@php
    $userLocation = App\Http\Controllers\Front\LocationController::getUserLocation();
@endphp

<!-- Location Selection Modal -->
<div class="modal fade" id="locationModal" @if(!$userLocation) data-bs-backdrop="static" data-bs-keyboard="false" @endif tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="locationModalLabel">Select Your Location</h5>
                <button type="button" class="btn-close {{ !$userLocation ? 'd-none' : '' }}" id="closeLocationModal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <p class="text-muted mb-4">Select your location to discover businesses and services near you.</p>

                <!-- Google Places Autocomplete Search -->
                <div class="mb-4">
                    <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden border">
                        <span class="input-group-text border-0 bg-white ps-3">
                            <i class="fas fa-search text-primary"></i>
                        </span>
                        <input type="text" id="location-search-input" class="form-control border-0 py-3" placeholder="Search for area, city..." autocomplete="off">
                    </div>
                </div>

                <div class="divider d-flex align-items-center mb-4">
                    <hr class="flex-grow-1 opacity-25">
                    <span class="px-3 text-muted small fw-bold">OR</span>
                    <hr class="flex-grow-1 opacity-25">
                </div>



                <!-- Manual Selection -->
                <div class="mb-3">

                    <!-- Auto Detect Button -->
                    <button class="btn btn-primary w-100 rounded-pill py-3 mb-4 d-flex align-items-center justify-content-center gap-2 shadow-sm transition-all" onclick="detectUserLocation()">
                        <i class="fas fa-crosshairs"></i>
                        <span class="fw-semibold">Use Current Location</span>
                    </button>

                    <!-- Location History -->
                    @php
                    $locationHistory = json_decode(request()->cookie('location_history', '[]'), true);
                    @endphp
                    @if(count($locationHistory) > 0)
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2">Recent Locations</label>
                        <div class="list-group list-group-flush rounded-3 border overflow-hidden">
                            @foreach($locationHistory as $item)
                            <button type="button" class="list-group-item list-group-item-action py-3 d-flex align-items-center gap-3 border-0"
                                onclick="saveLocation('{{ $item['type'] }}', '{{ $item['location_name'] }}', {{ $item['latitude'] }}, {{ $item['longitude'] }}, { full_address: '{{ $item['full_address'] ?? '' }}', area_lat_long: '{{ $item['area_lat_long'] ?? '' }}' })">
                                <i class="fas fa-history text-muted"></i>
                                <div class="text-start">
                                    <div class="fw-bold small">{{ $item['location_name'] }}</div>
                                    @if(!empty($item['full_address']) && $item['full_address'] != $item['location_name'])
                                    <div class="text-muted extra-small text-truncate" style="max-width: 250px;">{{ $item['full_address'] }}</div>
                                    @endif
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif



                    <!-- <label class="form-label small fw-bold text-muted text-uppercase mb-3">Popular Cities</label>
                    <div class="row g-2">
                        @php
                            $popularCities = [
                                ['name' => 'Mumbai', 'lat' => 19.0760, 'lng' => 72.8777],
                                ['name' => 'Bangalore', 'lat' => 12.9716, 'lng' => 77.5946],
                                ['name' => 'Delhi', 'lat' => 28.6139, 'lng' => 77.2090],
                                ['name' => 'Hyderabad', 'lat' => 17.3850, 'lng' => 78.4867],
                                ['name' => 'Ahmedabad', 'lat' => 23.0225, 'lng' => 72.5714],
                                ['name' => 'Surat', 'lat' => 21.1702, 'lng' => 72.8311],
                            ];
                        @endphp
                        @foreach($popularCities as $city)
                        <div class="col-4">
                            <button class="btn btn-outline-light text-dark border w-100 py-2 small hover-bg-primary" 
                                    onclick="saveLocation('search', '{{ $city['name'] }}', {{ $city['lat'] }}, {{ $city['lng'] }})">
                                {{ $city['name'] }}
                            </button>
                        </div>
                        @endforeach
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-primary:hover {
        background-color: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .pac-container {
        border-radius: 12px;
        margin-top: 5px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border: none;
        z-index: 1060 !important;
    }

    .extra-small {
        font-size: 0.7rem;
    }
</style>


<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        initAutocomplete();

        // Check if location is already set
        const savedLocationName = localStorage.getItem('user_location_name');
        const hasCookieLocation = @json($userLocation ? true : false);

        if (!savedLocationName && !hasCookieLocation) {
            const locationModal = new bootstrap.Modal(document.getElementById('locationModal'));
            locationModal.show();
        } else if (savedLocationName) {
            updateLocationUI(savedLocationName);
        }
    });


    function initAutocomplete() {
        const input = document.getElementById('location-search-input');
        if (!input) return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            // fields: ['geometry'],
            // fields: ['place_id', 'name', 'geometry', 'formatted_address', 'types'],
            componentRestrictions: {
                country: 'in'
            }
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            // Extract viewport boundaries for area filtering
            let areaLatLong = null;
            if (place.geometry.viewport) {
                const sw = place.geometry.viewport.getSouthWest();
                const ne = place.geometry.viewport.getNorthEast();
                areaLatLong = `${sw.lat()},${sw.lng()},${ne.lat()},${ne.lng()}`;
            }

            // Extract Area and City from address components
            let area = '';
            let city = '';

            if (place.address_components) {
                for (const component of place.address_components) {
                    if (component.types.includes('sublocality_level_1')) {
                        area = component.long_name;
                    }
                    if (component.types.includes('locality')) {
                        city = component.long_name;
                    }
                    if (!area && component.types.includes('sublocality')) {
                        area = component.long_name;
                    }
                }
            }

            const locationName = area && city ? `${area}, ${city}` : (city || area || place.name || 'Unknown Location');
            const fullAddress = place.formatted_address;

            saveLocation('search', locationName, lat, lng, {
                full_address: fullAddress,
                area_lat_long: areaLatLong
            });
        });


    }

    function saveLocation(type, name, lat, lng, extra = {}) {

        const data = {
            type: type,
            location_name: name,
            latitude: lat,
            longitude: lng,
            ...extra
        };

        fetch('{{ route("set-location") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    localStorage.setItem('user_location_name', name);
                    updateLocationUI(name);

                    const modalElement = document.getElementById('locationModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) modalInstance.hide();

                    toastr.success(`Location set to ${name}`);

                    // Refresh page to apply filters
                    setTimeout(() => location.reload(), 500);
                } else {
                    toastr.error('Failed to save location');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('Something went wrong');
            });
    }


    function updateLocationUI(locationName) {
        const textElements = document.querySelectorAll('.selected-location-text');
        textElements.forEach(el => {
            el.textContent = locationName;
        });
    }

    function detectUserLocation() {
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;

        if (navigator.geolocation) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Detecting...';

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    const geocoder = new google.maps.Geocoder();
                    geocoder.geocode({
                        location: {
                            lat,
                            lng
                        }
                    }, (results, status) => {
                        if (status === "OK" && results[0]) {
                            let area = '';
                            let city = '';

                            for (const component of results[0].address_components) {
                                if (component.types.includes('sublocality_level_1')) {
                                    area = component.long_name;
                                }
                                if (component.types.includes('locality')) {
                                    city = component.long_name;
                                }
                                if (!area && component.types.includes('sublocality')) {
                                    area = component.long_name;
                                }
                            }

                            const name = area && city ? `${area}, ${city}` : (city || area || "Current Location");
                            const fullAddress = results[0].formatted_address;
                            saveLocation('current_location', name, lat, lng, {
                                radius: 5,
                                full_address: fullAddress
                            });
                        } else {
                            saveLocation('current_location', 'Current Location', lat, lng, {
                                radius: 5
                            });
                        }
                    });


                },
                (error) => {
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                    toastr.error('Unable to detect location. Please select manually.');
                }
            );
        } else {
            toastr.error('Geolocation is not supported by your browser.');
        }
    }

    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
</script>