<?php

namespace SalesForce;

/**
 * SalesForce Exception
 *
 * @author Daniel Boorn
 */
class Exception extends \Exception
{
    //
}

/**
 * SalesForce API
 *
 * A simple and lightweight SalesForce API implementation with no dependencies
 *
 * @author Daniel Boorn
 */
class Api
{

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $securityToken;

    /**
     * @var array
     */
    protected $session;

    /**
     * @var string
     */
    protected $servicesEndpoint = '/services/data/v56.0';


    /**
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     * @param string $securityToken
     * @param bool $auth
     */
    public function __construct(string $baseUrl, string $clientId, string $clientSecret, string $username, string $password, string $securityToken, bool $auth = true)
    {
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->securityToken = $securityToken;

        if ($auth) {
            $this->authenticate();
        }
    }

    /**
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     * @param string $securityToken
     * @param bool $auth
     * @return Api
     */
    public static function forge(string $baseUrl, string $clientId, string $clientSecret, string $username, string $password, string $securityToken, bool $auth = true)
    {
        return new self($baseUrl, $clientId, $clientSecret, $username, $password, $securityToken, $auth = true);
    }

    /**
     * @param string $path
     * @param array $params
     * @param string $verb
     * @param string $contentType
     * @param bool $auth
     * @return mixed
     */
    public function fetch(string $path, array $params, string $verb = 'GET', string $contentType = 'application/json', bool $auth = true)
    {

        $data = stripos($contentType, 'json') === false ? http_build_query($params) : json_encode($params);

        $headers = array();
        $headers[] = "Content-Type: {$contentType}";

        if ($verb !== 'GET') {
            $headers[] = "ContentLength: " . strlen($data);
        } else {
            $path .= "?{$data}";
        }

        if ($auth) {
            $headers[] = "Authorization: Bearer {$this->session['access_token']}";
        }
        
        $context = stream_context_create(array(
            'http' => array(
                'header'        => implode("\r\n", $headers) . "\r\n",
                'timeout'       => 60.0,
                'ignore_errors' => false,
                'method'        => $verb,
                'content'       => $data,
            ),
            'ssl'  => array(
                'verify_peer' => true,
            ),
        ));

        $path = ltrim($path, '/');
        $apiUrl = $this->baseUrl . $path;

        $r = @file_get_contents($apiUrl, false, $context);
        $r = json_decode($r, true);

        return $r;
    }

    /**
     * Authenticate with SalesForce API using OAuth credentials and user credentials including security token
     *
     * @return void
     * @throws Exception
     */
    public function authenticate()
    {
        $params = [
            'grant_type'    => 'password',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'username'      => $this->username,
            'password'      => $this->password . $this->securityToken,
        ];

        $r = $this->fetch('/services/oauth2/token', $params, 'POST', 'application/x-www-form-urlencoded', false);

        if (empty($r['access_token'])) {
            $msg = "Failed to Authenticate: {$r['error']}: {$r['error_description']}";
            throw new Exception($msg);
        }

        $this->session = $r;
    }

    /**
     * @param string $query
     * @return array
     */
    public function search(string $query)
    {
        return $this->fetch("{$this->servicesEndpoint}/search", ['q' => $query], 'GET', 'application/x-www-form-urlencoded');
    }    
    
    /**
     * @param string $query
     * @return array
     */
    public function query(string $query)
    {
        return $this->fetch("{$this->servicesEndpoint}/query", ['q' => $query], 'GET', 'application/x-www-form-urlencoded');
    }

    /**
     * @param string $object
     * @param array $params
     * @return array
     */
    public function create(string $object, array $params)
    {
        return $this->fetch("{$this->servicesEndpoint}/sobjects/{$object}", $params, 'POST');
    }

    /**
     * @param string $object
     * @param string $id
     * @param array $params
     * @return array
     */
    public function patch(string $object, string $id, array $params)
    {
        return $this->fetch("{$this->servicesEndpoint}/sobjects/{$object}/{$id}", $params, 'PATCH');
    }

    /**
     * @param $object
     * @param $id
     * @return array
     */
    public function delete($object, $id)
    {
        return $this->fetch("{$this->servicesEndpoint}/sobjects/{$object}/{$id}", [], 'DELETE');
    }


}
