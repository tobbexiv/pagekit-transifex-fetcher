<?php

namespace Tobbe\TransifexFetcher\Helper;

use GuzzleHttp\Client;

/**
* Access the Api of transifex.com to fetch resources and translated strings.
* 
* Copied from Pagekit\Console\Translate\TransifexApi as there was an error in the conversion of the response to json.
* Also switched from authentification with username and password to the authentification with apitoken.
*/
class TransifexApi
{
    protected $apitoken;
    protected $project;
    protected $client;

    function __construct($apitoken, $project)
    {
        $this->apitoken = $apitoken;
        $this->project  = $project;

        $this->client = new Client();
    }

    function fetchLocales($resource)
    {
        $url      = sprintf("https://www.transifex.com/api/2/project/%s/resource/%s/", $this->project, $resource);
        $query    = ['details' => 1];
        $auth     = ['api', $this->apitoken];
        $response = $this->client->get($url, compact('query', 'auth'));
        $response = json_decode((string) $response->getBody(), true);

        // We're only interested in locale codes
        $locales = array_map(function($locale) {
            return $locale["code"];
        }, $response["available_languages"]);

        // Do not fetch origin locale
        $locales = array_filter($locales, function($locale) use ($response) {
            return $locale != $response["source_language_code"];
        });

        return $locales;
    }

    function fetchTranslations($resource, $locale)
    {
        $url      = sprintf("https://www.transifex.com/api/2/project/%s/resource/%s/translation/%s/strings", $this->project, $resource, $locale);
        $query    = ['details' => 1];
        $auth     = ['api', $this->apitoken];
        $response = $this->client->get($url, compact('query', 'auth'));
        $response = json_decode((string) $response->getBody(), true);

        // We only need a simple array: source_string -> translation
        $translations = [];
        foreach ($response as $s) {
            $translations[$s['source_string']] = $s['translation'];
        }

        // TODO: collect max value of "last_update" and keep "user"

        return $translations;
    }
}
