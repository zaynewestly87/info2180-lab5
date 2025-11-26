// world.js
document.addEventListener('DOMContentLoaded', function () {
  const lookupBtn = document.getElementById('lookup');
  const lookupCitiesBtn = document.getElementById('lookup-cities'); // id for "Lookup Cities" button
  const resultDiv = document.getElementById('result');
  const countryInput = document.getElementById('country');

  function doLookup(lookupType) {
    const country = countryInput.value.trim();
    // Build query string
    const params = new URLSearchParams();
    if (country.length > 0) params.append('country', country);
    if (lookupType === 'cities') params.append('lookup', 'cities');

    const url = 'world.php' + (params.toString() ? ('?' + params.toString()) : '');

    // Show a quick message while loading
    resultDiv.innerHTML = '<p>Loading…</p>';

    fetch(url)
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
        return response.text();
      })
      .then(html => {
        resultDiv.innerHTML = html;
      })
      .catch(err => {
        resultDiv.innerHTML = '<p>Error: ' + err.message + '</p>';
      });
  }

  if (lookupBtn) {
    lookupBtn.addEventListener('click', function (e) {
      e.preventDefault();
      doLookup('countries');
    });
  }

  if (lookupCitiesBtn) {
    lookupCitiesBtn.addEventListener('click', function (e) {
      e.preventDefault();
      doLookup('cities');
    });
  }

  // Optional: allow Enter in input to trigger country lookup
  countryInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      doLookup('countries');
    }
  });
});
