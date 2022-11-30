<?php

use Metacritic\API\MetacriticAPI;

require __DIR__ . '/vendor/autoload.php';

# Ignore Unirest warning if any (eg. safe mode related)
#error_reporting(E_ERROR | E_PARSE);

$metacritic_api = new MetacriticAPI();
$metacritic_api->getMetacriticPage("The Elder Scrolls V: Skyrim");
$json_reponse = $metacritic_api->getMetacriticScores();

echo "Json Output:\n<br/><br/> " . $json_reponse;
