<?php

namespace Metacritic\API;

use Unirest;

class MetacriticAPI
{
    private $response_body = "";
    private $baseUrl = "http://www.metacritic.com/game/";
    private $arrSystems = array();

    public function __construct($system = "pc")
    {
        $this->arrSystems[] = $system;
    }

    public function getMetacriticPage($game_name)
    {
        $returnValue = "";
        # Remove spaces
        $game_name = trim($game_name);
        # convert spaces to -
        $game_name = str_replace(' ', '-', $game_name);
        # Remove &<space>
        $game_name = str_replace('& ', '', $game_name);
        # lowercase
        $game_name = strtolower($game_name);
        # Remove all special chars execept a-z, digits, --sign, ?-sign, !-sign
        $game_name = preg_replace('/[^a-z\d\?!\-]/', '', $game_name);

        # Get the webpage
        $i = 0;
        do {
            $system = $this->arrSystems[$i++];
            $url = $this->baseUrl . $system . "/" . $game_name;
            $response = Unirest\Request::get($url, $headers = array(), $parameters = null);
        } while ($response->code <> 200 and $i < count($this->arrSystems));

        if ($response->code == 200) {
            $returnValue = $response->raw_body;
        }
        $this->response_body = $returnValue;
    }

    public function getMetacriticScores(): array
    {
        # Get DOM by string content
        $html = str_get_html($this->response_body);
        # Define json output array
        $json_output = array();
        $error = false;
        # init all vars
        $name = "";
        $metascritic_score = 0;
        $user_score = 0.0;
        $rating = "";
        $developer = "";
        $publisher = "";
        $genres = "";
        $release_date = "";
        $image_url = "";
        $cheat_url = "";

        if (!$html) {
            $json_output['error'] = "Page could not be loaded!";
            $error = true;
        }

        if (!$error) {
            foreach ($html->find('div.product_title h1') as $element) {
                $name = trim($element->plaintext);
            }

            foreach ($html->find('div.metascore_w.game span') as $element) {
                $metascritic_score = intval($element->plaintext);
            }

            foreach ($html->find("div.metascore_w.user.large.game") as $element) {
                $user_score = floatval($element->plaintext);
            }

            foreach ($html->find('li.summary_detail.product_rating span.data') as $element) {
                $rating = trim($element->plaintext);
            }

            $genres = array();
            foreach ($html->find('li.summary_detail.product_genre span.data') as $element) {
                array_push($genres, trim($element->plaintext));
            }

            foreach ($html->find('li.summary_detail.developer span.data') as $element) {
                $developer = trim($element->plaintext);
            }
            $developers = explode(", ", $developer);

            foreach ($html->find('li.summary_detail.publisher span.data a') as $element) {
                $publisher = trim($element->plaintext);
            }

            foreach ($html->find('li.summary_detail.release_data span.data') as $element) {
                $release_date = trim($element->plaintext);
            }

            $also_on = array();
            $also_on_url = array();
            foreach ($html->find('li.summary_detail.product_platforms span.data a') as $element) {
                array_push($also_on, trim($element->plaintext));
                array_push($also_on_url, $element->href);
            }

            foreach ($html->find('img.product_image.large_image') as $element) {
                $image_url = $element->src;
            }

            foreach ($html->find('li.summary_detail.product_cheats span.data a') as $element) {
                $cheat_url = $element->href;
            }

            # Prevent memory leak
            $html->clear();
            unset($html);

            # Fill-in the array
            $json_output['name'] = $name;
            $json_output['metascritic_score'] = $metascritic_score;
            $json_output['users_score'] = $user_score;
            $json_output['rating'] = $rating;
            $json_output['genres'] = $genres;
            $json_output['developers'] = $developers;
            $json_output['publishers'] = $publisher;
            $json_output['release_date'] = $release_date;
            $json_output['also_on'] = $also_on;
            $json_output['also_on_url'] = $also_on_url;
            $json_output['thumbnail_url'] = $image_url;
            $json_output['cheat_url'] = $cheat_url;
        }

        # Return JSON format
        return $json_output;
    }
}