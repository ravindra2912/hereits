<!-- Location Selection Modal -->
<div class="modal fade" id="locationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="locationModalLabel">Select Your Location</h5>
                <button type="button" class="btn-close d-none" id="closeLocationModal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Please select your city to see relevant businesses and services near you.</p>
                
                <!-- Auto Detect Button -->
                <button class="btn btn-primary w-100 rounded-pill py-3 mb-4 d-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="detectUserLocation()">
                    <i class="fas fa-crosshairs"></i>
                    <span>Detect My Location</span>
                </button>
                
                <div class="divider d-flex align-items-center mb-4">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted small fw-bold">OR SELECT MANUALLY</span>
                    <hr class="flex-grow-1">
                </div>
                
                <!-- Manual Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Popular Cities</label>
                    <div class="row g-2">
                        @php
                            $popularCities = ['Mumbai', 'Bangalore', 'Delhi', 'Hyderabad', 'Ahmedabad', 'Surat'];
                        @endphp
                        @foreach($popularCities as $city)
                        <div class="col-4">
                            <button class="btn btn-outline-light text-dark border w-100 py-2 small hover-bg-primary-subtle" onclick="setSelectedLocation('{{ $city }}')">{{ $city }}</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <div class="mt-4">
                    <select class="form-select rounded-pill border-2" id="citySelect" onchange="setSelectedLocation(this.value)">
                        <option value="" selected disabled>Other Cities...</option>
                        <option value="Pune">Pune</option>
                        <option value="Chennai">Chennai</option>
                        <option value="Kolkata">Kolkata</option>
                        <option value="Jaipur">Jaipur</option>
                        <option value="Lucknow">Lucknow</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-primary-subtle:hover {
        background-color: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedLocation = localStorage.getItem('user_location');
        if (!selectedLocation) {
            const locationModal = new bootstrap.Modal(document.getElementById('locationModal'));
            locationModal.show();
        } else {
            updateLocationUI(selectedLocation);
        }
    });

    function setSelectedLocation(location) {
        if (!location) return;
        localStorage.setItem('user_location', location);
        updateLocationUI(location);
        
        // Close modal
        const modalElement = document.getElementById('locationModal');
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        }
        
        toastr.success(`Location set to ${location}`);
        
        // Optional: Trigger page refresh or AJAX update for nearest stores
        // location.reload(); 
    }

    function updateLocationUI(location) {
        const textElements = document.querySelectorAll('.selected-location-text');
        textElements.forEach(el => {
            el.textContent = location;
        });
        
        // Also update search input if it exists
        const locationInput = document.getElementById('user-location');
        if (locationInput) {
            locationInput.value = location;
        }
    }

    function detectUserLocation() {
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        
        if (navigator.geolocation) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Detecting...';
            
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    // In a real app, use reverse geocoding to get city name
                    // For now, let's mock it
                    setTimeout(() => {
                        const mockCity = "Surat"; // Example
                        setSelectedLocation(mockCity);
                    }, 1000);
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
</script>
