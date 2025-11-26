<?php
// world.php
header('Content-Type: text/html; charset=utf-8');

// Update these with the MySQL user you created for the lab
$host = 'localhost';
$username = 'lab5_user';   // <-- change if needed
$password = 'password123'; // <-- change if needed
$database = 'world';

// Connect
$mysqli = new mysqli($host, $username, $password, $database);
if ($mysqli->connect_errno) {
    echo "<p>Failed to connect to MySQL: " . htmlspecialchars($mysqli->connect_error) . "</p>";
    exit;
}

// Get parameters
$country = isset($_GET['country']) ? $_GET['country'] : '';
$lookup = isset($_GET['lookup']) ? $_GET['lookup'] : ''; // optional 'cities'

// Helper to escape output
function h($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// If lookup=cities, return cities for the country (join)
if (strtolower($lookup) === 'cities') {

    // Use LIKE for partial matches; if no country provided, return all cities (but that's probably large)
    if ($country === '') {
        // All cities joined with country name
        $sql = "SELECT ci.name AS city, ci.district, ci.population, co.name AS country
                FROM cities ci
                JOIN countries co ON ci.country_code = co.code
                ORDER BY co.name, ci.population DESC
                LIMIT 1000"; // guard: limit to 1000 to avoid enormous output
        $stmt = $mysqli->prepare($sql);
    } else {
        $sql = "SELECT ci.name AS city, ci.district, ci.population, co.name AS country
                FROM cities ci
                JOIN countries co ON ci.country_code = co.code
                WHERE co.name LIKE ?
                ORDER BY ci.population DESC";
        $stmt = $mysqli->prepare($sql);
        $param = '%' . $country . '%';
        $stmt->bind_param('s', $param);
    }

    if (!$stmt->execute()) {
        echo "<p>Error executing query: " . h($stmt->error) . "</p>";
        exit;
    }
    $res = $stmt->get_result();

    // Output table
    echo '<h2>Cities</h2>';
    echo '<table border="1" cellpadding="6" cellspacing="0">';
    echo '<thead><tr><th>Country</th><th>City</th><th>District</th><th>Population</th></tr></thead><tbody>';
    while ($row = $res->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . h($row['country']) . '</td>';
        echo '<td>' . h($row['city']) . '</td>';
        echo '<td>' . h($row['district']) . '</td>';
        echo '<td>' . h(number_format($row['population'])) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    $stmt->close();
    $mysqli->close();
    exit;
}

// Otherwise: return country information
// If empty, return all countries; else return matching countries using LIKE
if ($country === '') {
    $sql = "SELECT name, continent, independence_year, head_of_state FROM countries ORDER BY name";
    $stmt = $mysqli->prepare($sql);
} else {
    $sql = "SELECT name, continent, independence_year, head_of_state
            FROM countries
            WHERE name LIKE ?
            ORDER BY name";
    $stmt = $mysqli->prepare($sql);
    $param = '%' . $country . '%';
    $stmt->bind_param('s', $param);
}

if (!$stmt->execute()) {
    echo "<p>Error executing query: " . h($stmt->error) . "</p>";
    exit;
}
$res = $stmt->get_result();

// Output table
echo '<h2>Countries</h2>';
echo '<table border="1" cellpadding="6" cellspacing="0">';
echo '<thead><tr><th>Country</th><th>Continent</th><th>Independence Year</th><th>Head of State</th></tr></thead><tbody>';
while ($row = $res->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . h($row['name']) . '</td>';
    echo '<td>' . h($row['continent']) . '</td>';
    echo '<td>' . ($row['independence_year'] === null ? '' : h($row['independence_year'])) . '</td>';
    echo '<td>' . h($row['head_of_state']) . '</td>';
    echo '</tr>';
}
echo '</tbody></table>';

$stmt->close();
$mysqli->close();
