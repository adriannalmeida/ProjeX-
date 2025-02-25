/**
 * Update the cityId hidden input based on the selected city.
 */
function updateCityId() {
    const citySelect = document.getElementById('city');
    const cityIdInput = document.getElementById('cityId');
    cityIdInput.value = citySelect.value;
}
document.addEventListener('DOMContentLoaded', function () {
    const countrySelect = document.getElementById('country');
    const citySelect = document.getElementById('city');
    const userCountry = document.getElementById('user_country');
    const cityLabel = document.querySelector('label[for="city"]'); // Label for the city dropdown

    /**
     * Show or hide the city dropdown and its label based on country selection.
     */
    function toggleCityVisibility(show) {
        if (show) {
            citySelect.style.display = 'block';
            cityLabel.style.display = 'block';
        } else {
            citySelect.style.display = 'none';
            cityLabel.style.display = 'none';
        }
    }

     /**
     * Handle country selection changes and fetch related cities.
     */
    function onChangeCountry(countrySelect,) {
        countrySelect.oldvalue = countrySelect.value;
        const hasSelectedCountry = countrySelect.value !== '';
        toggleCityVisibility(hasSelectedCountry);
        fetchCities(countrySelect.value, countrySelect.value === userCountry.value);
    }

    /**
     * Fetch cities for the selected country via an API call.
     */
    function fetchCities(countryId, saveOldCity) {
        const cityIdInput = document.getElementById('cityId');
        let selectedCityId = null;
        if (saveOldCity) selectedCityId = cityIdInput.value;

        // Clear and disable city dropdown during fetch
        citySelect.innerHTML = '<option value="">Select a city</option>';
        citySelect.disabled = true;

        if (!countryId) return;

        // Perform AJAX request to fetch cities
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `/api/get-cities/${countryId}`, true);
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.onload = function () {
            if (xhr.status === 200) {
                const cities = JSON.parse(xhr.responseText);
                // Populate city dropdown with options
                citySelect.innerHTML = '<option value="">Select a city</option>';

                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });

                citySelect.disabled = false;

                // Restore previously selected city, if applicable
                if (selectedCityId) {
                    citySelect.value = selectedCityId;
                } else {
                    citySelect.value = "";
                }

            } else {
                console.error('Error fetching cities:', xhr.statusText);
                citySelect.disabled = true;
            }
        };

        xhr.onerror = function () {
            console.error('Request error');
            citySelect.disabled = true;
        };

        xhr.send();
    }

    // Initialize city dropdown visibility and data on page load
    let selectedCountryId = countrySelect.value || countrySelect.oldvalue;
    if (selectedCountryId == null) {
        selectedCountryId = "";
    }
    toggleCityVisibility(selectedCountryId !== '');
    if (selectedCountryId) {
        fetchCities(selectedCountryId, selectedCountryId === userCountry.value);
    }

    // Handle changes in the country dropdown
    countrySelect.addEventListener('change', function () {
        onChangeCountry(this);
    });
});
