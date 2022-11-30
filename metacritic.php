<?php

require __DIR__ . '/vendor/autoload.php';

use Metacritic\API\MetacriticAPI;

if ($_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    if (isset($_GET['game_title'])) {
        $metacritic_api = new MetacriticAPI();
        $metacritic_api->getMetacriticPage($_GET['game_title']);
        echo json_encode($metacritic_api->getMetacriticScores());
    } else {
        echo json_encode(array("error" => "Game title is empty"));
    }
}
