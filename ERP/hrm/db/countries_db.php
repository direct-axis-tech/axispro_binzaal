<?php

/**
 * Get the list of all countries
 * 
 * @return mysqli_result
 */
function getCountries(): mysqli_result {
    $sql = "SELECT * FROM `0_countries` ORDER BY `name`";

    return db_query($sql, "Could not retrieve the list of countries");
}

/**
 * Get the list of all countries keyed by the code
 * 
 * @return array
 */
function getCountriesKeyedByCode(): array {
    $countryCursor = getCountries();

    $countries = [];
    while ($country = $countryCursor->fetch_assoc()) {
        $countries[$country['code']] = $country;
    }

    return $countries;
}