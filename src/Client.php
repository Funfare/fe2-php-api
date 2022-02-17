<?php

namespace Funfare\Fe2;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Collection;

class Client
{
    private string $host;
    private string $token;
    private ?int $port;

    private GuzzleClient $client;

    public function __construct(string $host, string $token = '', int $port = null) {
        $this->host = $host;
        $this->token = $token;
        $this->port =  $port;
        $this->setUpClient();
    }

    public function login(string $user, string $pass, string $source = 'WEB') {
        $response = $this->client->post('login', [
            'json' => [
                'username' => $user,
                'password' => $pass,
                'source' => $source,
            ]
        ]);
        $json = json_decode($response->getBody());
        $this->token = 'JWT '.$json->token;
        $this->setUpClient();
        return true;
    }

    public function getAddressBook() {
        $data = json_decode($this->client->get('addressbook')->getBody(), true);

        $persons = new Collection();

        foreach($data['persons'] as $row) {
            $persons[] = new Person($this->client, $row['personID'], $row);
        }
        
        return $persons;
    }

    private function setUpClient() {
        $this->client = new GuzzleClient([
            'base_uri' => $this->host.'/rest/',
            'headers' => [
                'Accept' => 'application/json, text/plain, */*',
                'Authorization' => $this->token,
                'User-Agent' => 'Funfare FE2 PHP Api v1',
                'Referer' => $this->host,
            ],
        ]);
    }
}