<?php

namespace Openbuildings\Postmark;

/**
 * Class for manupulating a server
 *
 * @package        openbuildings\postmark
 * @author         Ivan Kerin <ikerin@gmail.com>
 * @copyright  (c) 2013 OpenBuildings Ltd.
 * @license        http://spdx.org/licenses/BSD-3-Clause
 */
class Api
{
    const SEND_URI = 'http://api.postmarkapp.com/email';

    const SEND_URI_SECURE = 'https://api.postmarkapp.com/email';

    /**
     * @var string Postmark API token
     */
    protected $token;

    /**
     * @var boolean Use secure Postmark API URI (with https:// protocol).
     */
    protected $secure = true;

    public function __construct($token = null)
    {
        $this->setToken($token);
    }

    /**
     * Get the Postmark API token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the Postmark API token.
     *
     * @param null|string $token
     * @return $this
     */
    public function setToken($token)
    {
        if ($token !== null) {
            $this->token = (string) $token;
        }

        return $this;
    }

    /**
     * Get the headers needed to be sent to the Postmark API.
     *
     * @return array of header strings
     * @throws Exception If the Postmark API token is not yet set.
     */
    public function getHeaders()
    {
        if (! $this->getToken()) {
            throw new \Exception('You must set postmark token');
        }

        return array(
            'Accept: application/json',
            'Content-Type: application/json',
            'X-Postmark-Server-Token: '.$this->getToken(),
        );
    }

    /**
     * Send a request to the Postmark Send API.
     * See http://developer.postmarkapp.com/developer-send-api.html
     *
     * @param array $data
     * @return array Postmark API response.
     * @throws Exception If API request failed or JSON returned was invalid.
     * @throws Openbuildings\Postmark\Exception If Postmark API returned an error.
     * @uses Openbuildings\Postmark\Api::getSendUri to determine the request URI
     * @uses Openbuildings\Postmark\Api::getHeaders for the request headers
     */
    public function send(array $data)
    {
        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $this->getSendUri(),
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $this->getHeaders(),
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_RETURNTRANSFER => true
            )
        );

        $response = curl_exec($curl);

        if (! $response) {
            $curlError = curl_error($curl) ?: 'unknown cURL error';
            throw new \Exception(sprintf(
                'Postmark delivery failed: %s',
                $curlError
            ));
        }

        $response = @json_decode($response, true);

        if (! $response) {
            throw new \Exception('Postmark delivery failed: wrong json response');
        }

        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($responseCode != 200) {
            throw new Exception(
                sprintf('Postmark delivery failed: %s', $response['Message']),
                (int) $response['ErrorCode']
            );
        }

        return $response;
    }

    /**
     * Whether to use or not to use the secure Postmark API URI.
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Set whether to use or not to use the secure Postmark API URI.
     *
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = (bool) $secure;

        return $this;
    }

    /**
     * Get the Postmark API URI.
     *
     * @return string
     * @uses Openbuildings\Postmark\Api::isSecure to determine which URI to use.
     */
    public function getSendUri()
    {
        if ($this->isSecure()) {
            return static::SEND_URI_SECURE;
        } else {
            return static::SEND_URI;
        }
    }
}
