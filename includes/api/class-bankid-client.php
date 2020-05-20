<?php
/**
 * Bank ID client class for interacting with bankid API
 *
 * @author Dornaweb
 * @contribute Am!n <dornaweb.com>
 */

namespace WP_PDFGEN\API;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;

/**
 * This class handles requests and responses to and from the bankid v5 API
 * HTTP1.1 is required
 * All methods are accessed using HTTPPOST to /rp/v5/<method>.
 * Http header 'Content-Type' must be set to 'application/json' (no charset).
 * SSL Certificate provided by the bank must be included in each request
 * it is recommended to verify the response with the ssl certificate
 * For Signing contracts it is recommended to send Visible data base-64 encoded
 *
 * @author Amin <amin.nz>
 * @version apiapi2.v5
 * @link appapi2.bankid.com
 */
class Bankid_Client
{
    /**
     * GuzzleHttp Client
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Base bankid v5 api url
     *
     * @var string
     */
    private $api_url = 'https://appapi2.test.bankid.com/rp/v5/';

    /**
     * Guzzle http options
     *
     * @see http://docs.guzzlephp.org/en/stable/request-options.html
     * @var array
     */
    private $client_options = [];

    /**
     * End user ip address
     *
     * @var string
     */
    private $endUserIp = '';

    /**
     * SSL Certificate path
     */
    private $cert;

    public function __construct($options = []) {
        $this->endUserIp = dw_get_client_ip_address();
        $this->cert = WP_BANKID_ABSPATH . 'includes/cert/cert.pem';
        $this->client_options = [
            'base_uri'  => $this->api_url,
            'json'      => true,
            'verify' => false,
            'cert'   => $this->cert
        ];

        $this->client_options = wp_parse_args($options, $this->client_options);

        $this->client = new Client($this->client_options);
    }

    /**
     * Initiates an authentication request to the bankid API
     * If the request is successful, an "orderRef" and "autoStartToken" will be returned
      * When this request is successful a /collect request must be sent immediately and repeated every 2 seconds to understand the status of ongoing /auth or /sign request
     *
     * @param  string $personalNumber  user personalNumber
     * @param  array  $requirements    Requirements on how the author sign ordermust be performed (See documentation)
     */
    public function auth($personalNumber = '', $requirements = []) {
        $params = [
            'endUserIp'      => $this->endUserIp,
            'requirement'    => wp_parse_args($requirements, [
                'allowFingerprint' => true,
            ]),
        ];

        if ($personalNumber) {
            $params['personalNumber'] = $personalNumber;
        }

        $response = $this->post('auth', $params);

        return $response;
    }


    /**
     * Initiates a signing request to the bankid API
     * If the request is successful, an "orderRef" and "autoStartToken" will be returned
     * When this request is successful a /collect request must be sent immediately and repeated every 2 seconds to understand the status of ongoing /auth or /sign request
     *
     * @param  string $personalNumber       user personalNumber
     * @param  string $userVisibleData      The text to be displayed and signed. String. The text can be formatted using CR, LF and CRLF for new lines. The text must be encoded as UTF-8 and then base 64 encoded. 1--40 000 characters after base 64 encoding
     * @param  string $userNonVisibleData   Data not displayed to the user. String. Thevalue must be base 64-encoded. 1-200 000charactersafter base 64-encoding.
     * @param  array  $requirements         Requirements on how the author sign ordermust be performed (See documentation)
     */
    public function sign($personalNumber = '', $userVisibleData = '', $userNonVisibleData = '', $requirements = []) {
        $params = [
            'endUserIp'      => $this->endUserIp,
            'requirement'    => wp_parse_args($requirements, [
                'allowFingerprint' => true,
            ]),
        ];

        if ($userVisibleData) {
            $params['userVisibleData'] = base64_encode($userVisibleData);
        }

        if ($userNonVisibleData) {
            $params['userNonVisibleData'] = base64_encode($userNonVisibleData);
        }

        if ($personalNumber) {
            $params['personalNumber'] = $personalNumber;
        }

        $response = $this->post('sign', $params);

        return $response;
    }

    /**
     * Collect request must be sent every 2 seconds after a /auth or /sign request to see and verify the status of ongoing request
     *
     * @param string $orderRef   The order ref id returned by a successful /auth or /sign request
     */
    public function collect($orderRef) {
        $params = [
            'orderRef'      => $orderRef
        ];

        $response = $this->post('collect', $params);

        return $response;
    }

    /**
     * Cancel the ongoing request
     *
     * @param string $orderRef  The order ref id returned by a successful /auth or /sign request
     */
    public function cancel($orderRef) {
        $params = [
            'orderRef'      => $orderRef
        ];

        $response = $this->post('cancel', $params);

        return $response;
    }

    /**
     * Send a HTTP post request
     *
     * @param string $endpoint url endpoint
     * @param array  $params   list of parameters
     * @param array  $options
     */
    public function post($endpoint, $params = [], $options = []) {
        $options = wp_parse_args(['json' => [], $options]);
        $options['json'] = $params;

        try {
            return $this->client->post($endpoint, $options);

        } catch (ConnectException $e) {
            $error = $e->getHandlerContext();
            return [
                'error'  => $error['error'],
                'errorno'   => $error['errno'],
                'http_code'   => $error['http_code'],
                'ssl_verifyresult'   => $error['ssl_verifyresult'],
            ];

        } catch (ClientException $e) {
            return $e->hasResponse() ? (array) json_decode($e->getResponse()->getBody()->getContents()) : 'unknown_http_error'; // Later: Specify more information
        }
    }
}
