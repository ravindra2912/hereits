@extends('front.layouts.main', ['seo' => [
'title' => 'Register your business | Hereits',
'description' => 'Register your business with Hereits and get more customers',
'keywords' => 'register, business, Hereits',
'image' => '' ,
'city' => '',
'state' => '',
'position' => ''
]
])
@section('content')
@section('title', 'Register your business')

@push('css')
<link href="{{ asset('assets/common/css/select2.min.css') }}?v={{ filemtime(public_path('assets/common/css/select2.min.css')) }}" rel="stylesheet" />

@endpush

<section class="registration-page">
    <div class="container registration-container">


        <div class="registration-card shadow-lg p-0">
            @if(Auth::check())
            <form id="loginForm2" action="{{ route('register.business.store') }}" method="POST" enctype="multipart/form-data" data-action="redirect" class="formaction">
                @csrf
                <input type="hidden" name="plan_id" value="{{ request()->plan_id }}">
                <!-- Section 1: Basic Information -->
                <div class="form-section">
                    <div class="section-header text-center">
                        <h2 class="section-title">Business Profile</h2>
                        <p class="section-subtitle">Let's start with the basics of your business.</p>
                    </div>

                    <div class="row g-4 justify-content-center">
                        <!-- Business Images Section -->
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-center gap-5">
                                <!-- Business Image (Banner) -->
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <input type='file' id="profile" name="business_image" accept=".png, .jpg, .jpeg, .webp" class="avtar_input image-upload-input" data-preview="imagePreview" />
                                        <label for="profile"><i class="fas fa-camera"></i></label>
                                    </div>
                                    <div class="avatar-preview">
                                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage('') }}" class="avtar_img" id="imagePreview" loading="lazy" onclick="document.getElementById('profile').click()" style="cursor: pointer;">
                                    </div>
                                    <div class="text-center mt-3">
                                        <span class="small fw-bold text-muted">Business Image <span class="text-danger">*</span></span>
                                    </div>
                                </div>

                                <!-- Business Logo -->
                                <div class="avatar-upload">
                                    <div class="avatar-edit">
                                        <input type='file' id="logo" name="business_logo" accept=".png, .jpg, .jpeg, .webp" class="avtar_input image-upload-input" data-preview="logoPreview" required />
                                        <label for="logo"><i class="fas fa-camera"></i></label>
                                    </div>
                                    <div class="avatar-preview" style="border-radius: 50%;">
                                        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage('') }}" class="avtar_img" id="logoPreview" loading="lazy" onclick="document.getElementById('logo').click()" style="cursor: pointer;">
                                    </div>
                                    <div class="text-center mt-3">
                                        <span class="small fw-bold text-muted">Business Logo <span class="text-danger">*</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required" name="business_name" id="business_name" placeholder="E.g. Apple Inc" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Whatsapp Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required numeric" name="business_contact" id="business_contact" placeholder="+91 99999 00000" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Business Category <span class="text-danger">*</span></label>
                                <select class="form-select required select2-search" name="business_category_id" required data-placeholder="Select Business Category">
                                    <option value="">Select Category</option>
                                    @foreach ( $businessCat as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Business Type <span class="text-danger">*</span></label>
                                <select class="form-select required" name="business_type" required>
                                    <option value="">Select Business Type</option>
                                    @foreach (config('const.business_type') as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Referral Code <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" name="user_referral_code" id="user_referral_code" placeholder="Enter referral code if you have one">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Location & Address -->
                <div class="form-section bg-light-section" style="background: #fafafa;">
                    <div class="section-header text-center">
                        <h2 class="section-title">Business Location</h2>
                        <p class="section-subtitle">Where can customers find you? Pin your location on the map.</p>
                    </div>

                    <div class="map-wrapper mb-5 shadow-sm">
                        <div class="place-autocomplete-card" id="place-autocomplete-card"></div>
                        <button type="button" onclick="goToCurrentLocation()" class="current-loc-btn shadow-sm" title="Use current location">
                            <i class="fas fa-location-arrow text-dark fs-5"></i>
                        </button>
                        <div id="map"></div>
                    </div>
                    <input type="hidden" name="latitude" id="lat" />
                    <input type="hidden" name="longitude" id="lng" />

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                <textarea class="form-control required" id="address" name="address" rows="2" placeholder="Street name, building, area..." required></textarea>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select required" name="state_id" id="state_id" required>
                                    <option value="">Select State</option>
                                    @foreach ( getStates() as $state)
                                    <option value="{{ $state->id }}" {{ $state->id == 12?'selected':'' }}>{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <select class="form-select required" name="city_id" id="city_id" required>
                                    <option value="">Select City</option>
                                    @foreach ( getCities(12) as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Area <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required" name="area" placeholder="Enter area name" required />
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                <input type="text" class="form-control required numeric" name="pincode" placeholder="6-digit pincode" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Finalize -->
                <div class="form-section">
                    <div class="section-header text-center">
                        <h2 class="section-title">Finalize Registration</h2>
                        <p class="section-subtitle">Review our policies and agree to the terms to complete.</p>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card bg-white border-2 rounded-4 p-4 mb-5 text-center shadow-sm">
                                <div class="mb-4">
                                    <i class="fas fa-shield-alt text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="fw-bold mb-3">Policies & Agreements</h5>
                                <div class="form-check text-start mb-3">
                                    <input id="agree" class="form-check-input" type="checkbox" required>
                                    <label class="form-check-label text-dark fw-bold" for="agree">
                                        I agree to the <a href="{{ route('termAndCondition') }}" target="_blank" class="text-primary text-decoration-none border-bottom border-primary">Terms</a> and <a href="{{ route('privacyPolicy') }}" target="_blank" class="text-primary text-decoration-none border-bottom border-primary">Privacy Policy</a>.
                                    </label>
                                </div>
                                <div class="form-check text-start mb-3">
                                    <input id="agree_policy" class="form-check-input" type="checkbox" required>
                                    <label class="form-check-label text-dark fw-bold" for="agree_policy">
                                        I have read and agree to the <a href="{{ route('VendorPolicy') }}" target="_blank" class="text-primary text-decoration-none border-bottom border-primary">Vendor Policy</a>.
                                    </label>
                                </div>
                                <p class="small text-muted mb-0">You must agree to both to proceed.</p>
                            </div>

                            <div class="text-center">
                                <button class="btn-submit btn-lg w-100 py-3 btn_action" id="submitBtn" type="submit">
                                    <span id="buttonText" class="fs-5">Complete Registration</span>
                                    <span id="loader" class="d-none">Processing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <div class="p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-user-lock text-muted" style="font-size: 4rem;"></i>
                </div>
                <h3 class="fw-bold">Login Required</h3>
                <p class="text-muted mb-4">Please login to your account to register your business.<br>If you don't have an account, you can register for free.</p>
                <button class="btn btn-primary btn-lg rounded-pill px-5" data-bs-toggle="modal" data-bs-target="#authModal" onclick="switchAuthSection('login')">Login / Sign up</button>
            </div>
            @endif
        </div>
    </div>
</section>

<!-- vendor policy models -->
<div id="vendor-policy-modal" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0">
            <div class="modal-header">
                <div class="modal-title">
                    <h5 class="mb-0">Vendor Policy</h5>
                    <p class="my-0">Please read and accept the vendor policy to proceed.</p>
                </div>
                <button type="button" class="close close-outside" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
            </div>
            <div class="modal-body " style="height: 400px; overflow-y: auto;">
                @php
                /*
                {!! $VendorPolicy->description !!}
                */
                @endphp

            </div>
            <div class="modal-footer">
                <button class="btn btn-primary float-right" id="accept-vendor-policy">Accept & Continue</button>
            </div>
        </div>
    </div>
</div>

</section>


@push('js')

<!-- prettier-ignore -->
<script>
    (g => {
        var h, a, k, p = "The Google Maps JavaScript API",
            c = "google",
            l = "importLibrary",
            q = "__ib__",
            m = document,
            b = window;
        b = b[c] || (b[c] = {});
        var d = b.maps || (b.maps = {}),
            r = new Set,
            e = new URLSearchParams,
            u = () => h || (h = new Promise(async (f, n) => {
                await (a = m.createElement("script"));
                e.set("libraries", [...r] + "");
                for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]);
                e.set("callback", c + ".maps." + q);
                a.src = `https://maps.${c}apis.com/maps/api/js?` + e;
                d[q] = f;
                a.onerror = () => h = n(Error(p + " could not load."));
                a.nonce = m.querySelector("script[nonce]")?.nonce || "";
                m.head.append(a)
            }));
        d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
    })
    ({
        key: "{{ env('GOOGLE_MAP_KEY') }}",
        v: "beta",
        libraries: "places"
    });
</script>

<script>
    const agreeCheckbox = document.getElementById("agree");
    const policyCheckbox = document.getElementById("agree_policy");
    const submitBtn = document.getElementById("submitBtn");

    function toggleSubmitButton() {
        submitBtn.disabled = !(agreeCheckbox.checked && policyCheckbox.checked);
    }

    agreeCheckbox.addEventListener("change", toggleSubmitButton);
    policyCheckbox.addEventListener("change", toggleSubmitButton);

    // Initial check
    toggleSubmitButton();

    $('#accept-vendor-policy').on('click', function() {
        $('#vendor-policy-modal').modal('hide');
        policyCheckbox.checked = true;
        toggleSubmitButton();
    });



    // Initialize map on page load
    $(document).ready(function() {
        setTimeout(initMap, 500);
    });
</script>
<script>
    let map_state = '';
    let map_city = '';
    let map_area = '';
    let map_pincode = '';

    let map;
    let marker;
    let infoWindow;
    let center = {
        lat: 21.170240,
        lng: 72.831060
    }; // New York City
    async function initMap() {
        if (typeof google === 'undefined' || !google.maps) return;

        // Request needed libraries.
        const {
            Map,
            InfoWindow
        } = await google.maps.importLibrary("maps");
        const {
            AdvancedMarkerElement
        } = await google.maps.importLibrary("marker");
        const {
            PlaceAutocompleteElement
        } = await google.maps.importLibrary("places");

        const mapEl = document.getElementById('map');
        if (!mapEl) return;

        // Initialize the map.
        map = new Map(mapEl, {
            center,
            zoom: 15,
            mapId: '4504f8b37365c3d0',
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false
        });

        // Setup PlaceAutocompleteElement
        const placeAutocomplete = new PlaceAutocompleteElement({
            locationBias: center
        });
        placeAutocomplete.id = 'place-autocomplete-input';
        // placeAutocomplete.types = ['locality', 'sublocality', 'neighborhood'];

        const card = document.getElementById('place-autocomplete-card');
        card.appendChild(placeAutocomplete);
        map.controls[google.maps.ControlPosition.TOP_LEFT].push(card);

        // Create the marker and infowindow.
        marker = new AdvancedMarkerElement({
            map,
            gmpDraggable: true,
            position: center
        });
        infoWindow = new InfoWindow({});

        // Click to place marker
        map.addListener('click', (e) => {
            const clickedLocation = e.latLng;
            marker.position = clickedLocation;
            setAddress(clickedLocation);
        });

        // Marker drag end
        marker.addListener('dragend', () => {
            setAddress(marker.position);
        });

        console.log('Autocomplete initialized:', placeAutocomplete);

        // Autocomplete selection
        placeAutocomplete.addEventListener('gmp-select', async ({
            placePrediction
        }) => {
            const place = placePrediction.toPlace();

            await place.fetchFields({
                fields: ['location', 'viewport']
            });

            if (place.viewport) {
                map.fitBounds(place.viewport);
            } else {
                map.setCenter(place.location);
                map.setZoom(17);
            }

            marker.position = place.location;
            setAddress(place.location);
        });
    }

    function getAddressPart(components, type) {
        const component = components.find(c => c.types.includes(type));
        return component ? component.long_name : '';
    }

    async function setAddress(data) {
        const {
            Geocoder
        } = await google.maps.importLibrary("geocoding");
        const geocoder = new Geocoder();

        // ✅ Get lat/lng from `data`
        const lat = typeof data.lat === 'function' ? data.lat() : data.lat;
        const lng = typeof data.lng === 'function' ? data.lng() : data.lng;
        $('#lat').val(lat);
        $('#lng').val(lng);

        geocoder.geocode({
            location: data
        }, (results, status) => {
            if (status === 'OK' && results[0]) {
                const components = results[0].address_components;

                map_state = getAddressPart(components, 'administrative_area_level_1', true);
                map_city = getAddressPart(components, 'locality', true);
                map_area = getAddressPart(components, 'sublocality_level_1', true) || getAddressPart(components, 'neighborhood', true) || getAddressPart(components, 'sublocality_level_2', true);
                map_pincode = getAddressPart(components, 'postal_code', true);

                // Set form values
                $('#address').val(results[0].formatted_address);
                $('input[name="area"]').val(map_area);
                $('input[name="pincode"]').val(map_pincode);

                // Select state and trigger chain
                selectOptionByText('state_id', map_state);
            } else {
                console.error('Geocoder failed due to:', status);
            }
        });
    }

    function selectOptionByText(elementId, text) {
        const selectElement = document.getElementById(elementId);
        // Get all options as an array
        const options = Array.from(selectElement.options);

        // Loop through and print value and text
        options.forEach(option => {
            if (option.text.toLowerCase() === text.toLowerCase()) {
                option.selected = true;

                // 🔔 Trigger change event
                const event = new Event('change', {
                    bubbles: true
                });
                selectElement.dispatchEvent(event);
            }
            // console.log('Value:', option.value, 'Text:', option.text);
        });
    }

    function goToCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    const userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    // Set map center & marker
                    map.setCenter(userLocation);
                    map.setZoom(17);
                    marker.position = userLocation;

                    // Get address and fill fields
                    setAddress(userLocation);
                },
                error => {
                    alert("Unable to retrieve your location.");
                    console.error(error);
                }
            );
        } else {
            alert("Geolocation is not supported by your browser.");
        }
    }
</script>


<script>
    $('#state_id').on('change', function(event) {
        $.ajax({
            type: "POST",
            url: "{{ route('admin.getCities') }}",
            data: {
                state_id: $(this).val()
            },
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('#city_id').html('<option value="">Loading ...</option>');
            },
            success: function(states) {
                $('#city_id').html('<option value="">Select CitY</option>');
                $.each(states, function(index, item) {
                    $('#city_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                if (map_city !== '') {
                    selectOptionByText('city_id', map_city);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error: " + error);
                $('#city_id').html('<option value="">Select CitY</option>');
                alert("There was an error state chnage.");
            }
        });
    });

    $('#city_id').on('change', function(event) {
        if (map_area !== '') {
            $('input[name="area"]').val(map_area).prop('readonly', true);
        } else {
            $('input[name="area"]').val('').prop('readonly', false);

        }

        if (map_pincode !== '') {
            $('input[name="pincode"]').val(map_pincode).prop('readonly', true)
        } else {
            $('input[name="pincode"]').val('').prop('readonly', false)
        }
    });

    $('.image-upload-input').on('change', function(event) {
        var input = event.target;
        var previewId = $(this).data('preview');
        var image = $('#' + previewId);
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                image.attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    });
</script>
<!-- Select2 JS -->
<script src="{{ asset('assets/common/js/select2.min.js') }}?v={{ filemtime(public_path('assets/common/js/select2.min.js')) }}"></script>
<script>
    $(document).ready(function() {
        $('.select2-search').select2({
            width: '100%',
            placeholder: $(this).data('placeholder') || 'Select an option',
            allowClear: true
        });

        @if(!Auth::check())
            $('#authModal').modal('show');
            if (typeof switchAuthSection === 'function') {
                switchAuthSection('login');
            }
        @endif
    });
</script>
@endpush

@endsection