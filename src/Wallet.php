<?php

namespace Monero;

use Graze\GuzzleHttp\JsonRpc\Client;

class Wallet
{
    //test
    /**
     * Wallet constructor.
     * @param string $url
     */

    private $config = [];

    function __construct($url = "127.0.0.1:18082/json_rpc", $username = null, $password = null)
    {
        if($username && $password)
            $this->config['auth'] = [$username, $password, 'digest'];

        $this->client = Client::factory($url, $this->config);
    }

    /**
     * Helper function for creating wallet requests
     * @param array $body
     * @return string
     */
    public function _request($body)
    {
         if(isset($body['params'])){
             $response = $this->client->send($this->client->request(0, $body['method'], $body['params']));
         } else {
             $response = $this->client->send($this->client->request(0, $body['method']));
         }
         $response = json_decode($response->getBody());
         // if there is an error, return the error message otherwise respond with result
         if(property_exists($response, 'error')){
             return $response->error;
         } else {
             return $response->result;
         }
    }

    /**
     * Helper function for building transfer or transfer split request body
     * @param array $options
     * @return string
     */
    public function _buildTransfer($options)
    {
        $destinations = $options['destinations'];
        // Convert Monero amount to atomic units
        if(gettype($destinations) == "object"){
            $destinations->amount = $destinations->amount * 1e12;
            $destinations = array($destinations);
        } else {
            foreach ($destinations as &$destination){
                $destination->amount = $destination->amount * 1e12;
            }
        }
        // Define Mixin
        $mixin = (isset($options['mixin']) ? $options['mixin'] : 4);
        // Define Unlock Time
        $unlock_time = (isset($options['unlock_time']) ? $options['unlock_time'] : 0);
        // Define Payment ID
        $payment_id = (isset($options['payment_id']) ? $options['payment_id'] : null);
        // Build params array
        $params = [
            'destinations' => $destinations,
            'mixin' => $mixin,
            'unlock_time' => $unlock_time,
            'payment_id' => $payment_id
        ];
        // Set algorithm type if using transfer_split method
        if($options['method'] == "transfer_split"){
            $new_algorithm = (isset($options['new_algorithm']) ? $options['new_algorithm'] : false);
            $params['new_algorithm'] = $new_algorithm;
        }
        // Build request body
        $body = [
            'method' => $options['method'],
            'params' => $params
        ];
        return $body;
    }

    /**
     * Return total balance and unlocked balance of wallet
     * @return string
     */
    public function getBalance()
    {
        $body = ['method' => 'getbalance'];
        $balance = $this->_request($body);
        return $balance;
    }

    /**
     * Return the address of the wallet
     */
    public function getAddress()
    {
        $body = ['method' => 'getaddress'];
        return response()->json($this->_request($body));
        return $this->_request($body);
    }

    /**
     * Return the current block height.
     * @return string
     */
    public function getHeight()
    {
        $body = ['method' => 'getheight'];
        return $this->_request($body);
    }
    /**
     * Transfer Monero to a single recipient or group of recipients
     * @param array $options
     * @return string
     */
    public function transfer($options)
    {
        $options['method'] = 'transfer';
        $body = $this->_buildTransfer($options);
        return $this->_request($body);
    }

    /**
     * Same as transfer(), but can split into more than one transaction if necessary.
     * @param array $options
     * @return string
     */
    public function transferSplit($options)
    {
        $options['method'] = 'transfer_split';
        $body = $this->_buildTransfer($options);
        return $this->_request($body);
    }

    /**
     * Send all dust output back to the wallet with mixin 0
     * @return string
     */
    public function sweepDust()
    {
        $body = ['method' => 'sweep_dust'];
        return $this->_request($body);
    }

    /**
     * Save the blockchain.
     * @return string
     */
    public function store()
    {
        $body = ['method' => 'store'];
        return $this->_request($body);
    }

    /**
     * Get a list of incoming payments from a given payment ID
     * @param $payment_id
     * @return string
     */
    public function getPayments($payment_id)
    {
        $params = ['payment_id' => $payment_id];
        $body = [
            'method' => 'get_payments',
            'params'=> $params
        ];
        return $this->_request($body);
    }

    /**
     * Get a list of incoming payments from a single payment ID or list of payment IDs from a given height.
     * @param $payment_ids array
     * @param $height int
     * @return string
     */
    public function getBulkPayments($payment_ids, $height)
    {
        $params = [
            'payment_ids' => $payment_ids,
            'min_block_height' => $height
        ];
        $body = [
            'method' => 'get_bulk_payments',
            'params' => $params
        ];
        return $this->_request($body);
    }

    /**
     * Return a list of incoming transfers to the wallet.
     * @param $type string
     * @return string
     */
    public function incomingTransfers($type)
    {
        $params = ['transfer_type' => $type];
        $body = [
            'method' => 'incoming_transfers',
            'params' => $params
        ];
        return $this->_request($body);
    }

    /**
     * Return the spend or view private key.
     * @param $key_type string
     * @return string
     */
    public function queryKey($key_type)
    {
        $params = ['key_type' => $key_type];
        $body = [
            'method' => 'query_key',
            'params' => $params
        ];
        return $this->_request($body);
    }

    /**
     * Make an integrated address from the wallet address and a payment id.
     * @param string $payment_id
     * @return string
     */
    public function integratedAddress($payment_id = null)
    {
        $params = ['payment_id' => $payment_id];
        $body = [
            'method' => 'make_integrated_address',
            'params' => $params
        ];
        return $this->_request($body);
    }

    /**
     * Retrieve the standard address and payment id corresponding to an integrated address.
     * @param $address string
     * @return string
     */
    public function splitIntegratedAddress($address)
    {
        $params = ['integrated_address' => $address];
        $body = [
            'method' => 'split_integrated_address',
            'params' => $params
        ];
        return $this->_request($body);
    }

    /**
     * Stops the wallet, storing the current state.
     * @return string
     */
    public function stopWallet()
    {
        $body = ['method' => 'stop_wallet'];
        return $this->_request($body);
    }
}