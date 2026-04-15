<html>

<head>
    <title>Place Autocomplete Address Form</title>

</head>

<body>

    <p class="title">Sample address form india</p>
    <label class="full-field">
        <!-- Avoid the word "address" in id, name, or label text to avoid browser autofill from conflicting with Place Autocomplete. Star or comment bug https://crbug.com/587466 to request Chromium to honor autocomplete="off" attribute. -->
        <span class="form-label">Deliver to*</span>
        <input
            id="locationSearch"
            placeholder="Enter your address"
            autocomplete="off" />
    </label>


    <script
        src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAP_KEY')}}&callback=initAutocomplete&libraries=places&v=weekly"
        defer></script>


    <script>
        let autocomplete;
        let address1Field;

        function initAutocomplete() {
            address1Field = document.querySelector("#locationSearch");
            // Create the autocomplete object, restricting the search predictions to
            // addresses in India.
            autocomplete = new google.maps.places.Autocomplete(address1Field, {
                componentRestrictions: {
                    country: ["in"]
                },
                fields: ["address_components", "geometry"],
                types: ["address"],
            });
            // address1Field.focus();
            // When the user selects an address from the drop-down, populate the
            // address fields in the form.
            autocomplete.addListener("place_changed", fillInAddress);
        }

        function fillInAddress() {
            const place = autocomplete.getPlace();

            for (const component of place.address_components) {
                // @ts-ignore remove once typings fixed
                const componentType = component.types[0];
                var address = '';
                switch (componentType) {
                    case "neighborhood": {
                        address = component.long_name
                        break;
                    }

                    case "locality": {
                        address += ', '+component.short_name;
                        break;
                    }
                }
            }

            // ✅ Get Latitude and Longitude
            if (place.geometry && place.geometry.location) {
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                console.log("Latitude:", lat);
                console.log("Longitude:", lng);
                console.log("address:", address);
            } else {
                console.error("No geometry found for the selected place.");
            }
        }

        window.initAutocomplete = initAutocomplete;
    </script>
</body>

</html>