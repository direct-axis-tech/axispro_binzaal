<?php

namespace App\Factories;

use Laravel\Passport\Client;
use League\OAuth2\Server\AuthorizationServer;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class AutofetchTokenFactory
{
    /**
     * Create a new personal access token factory instance.
     *
     * @param  AuthorizationServer  $server
     * @return void
     */
    public function __construct(AuthorizationServer $server)
    {
        $this->server = $server;
    }

    /**
     * Create a new personal access token.
     *
     * @param  array  $scopes
     * @return array
     */
    public function make(array $scopes = [])
    {
        $request = $this->createRequest(Client::first(), $scopes);
        $response = $this->dispatchRequestToAuthorizationServer($request);

        return $response;
    }
    
    /**
     * Create a request instance for the given client.
     *
     * @param  \Laravel\Passport\Client  $client
     * @param  mixed  $userId
     * @param  array  $scopes
     * @return \Zend\Diactoros\ServerRequest
     */
    protected function createRequest($client, array $scopes)
    {
        return (new ServerRequest())->withParsedBody([
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => implode(' ', $scopes),
        ]);
    }

    /**
     * Dispatch the given request to the authorization server.
     *
     * @param  \Zend\Diactoros\ServerRequest  $request
     * @return array
     */
    protected function dispatchRequestToAuthorizationServer(ServerRequest $request)
    {
        return json_decode($this->server->respondToAccessTokenRequest(
            $request, new Response()
        )->getBody()->__toString(), true);
    }
}
