<?php
class SMSCloud
{
    protected $_apiKey;

    // Locks you into a non-changing version of the API (invalid or empty version uses latest version)
    protected $_apiVersion = '1.0';

    // This can be either jsonrpc or xmlrpc
    protected $_apiEndpoint = 'xmlrpc';
    //protected $_apiEndpoint = 'jsonrpc';

    // Can also be https, use the setUseSSL() method to toggle
    protected $_scheme = 'http';

    protected $_apiPrimaryHost = 'api.smscloud.com';
    protected $_apiSecondaryHost = 'api-backup.smscloud.com';

    // Using IP's instead of hostnames to avoid slow DNS lookup
    //protected $_apiPrimaryHost = '8.20.116.105';
    //protected $_apiSecondaryHost = '';

    public function __construct($apiKey = false, $apiVersion = false)
    {
        if ($apiKey) {
            $this->setApiKey($apiKey);
        }
        if ($apiVersion) {
            $this->setApiVersion($apiVersion);
        }
        switch ($this->_apiEndpoint) {
            case 'jsonrpc':
                if (!function_exists('json_encode')) {
                    throw new Exception('JSON-RPC API was specified, but the JSON PHP extension is not installed. See http://www.php.net/manual/en/json.installation.php');
                }
                break;
            case 'xmlrpc':
                if (!function_exists('xmlrpc_encode_request')) {
                    throw new Exception('XML-RPC API was specified, but the XML-RPC PHP extension is not installed. See http://www.php.net/manual/en/xmlrpc.installation.php');
                }
                break;
            default:
                throw new Exception('Invalid API endpoint specified: '.$this->_apiEndpoint.'. Valid endpoints are jsonrpc and xmlrpc');
                break;
        }
    }

    public function setUseSSL($boolean)
    {
        if ($boolean) {
            $this->_scheme = 'https';
        } else {
            $this->_scheme = 'http';
        }
    }

    public function setApiKey($apiKey)
    {
        $this->_apiKey = $apiKey;
    }

    public function setApiVersion($apiVersion)
    {
        $this->_apiVersion = $apiVersion;
    }

    public function carrierLookup($phoneNumber)
    {
        $method = 'nvs.carrierLookup';
        $params = array($phoneNumber);
        return $this->request($method, $params);
    }

    public function carrierLookupBulk($phoneNumbers)
    {
        $method = 'nvs.carrierLookupBulk';
        $params = array($phoneNumbers);
        return $this->request($method, $params);
    }

    public function sendSMS($fromNumber, $toNumber, $message, $priority = 1)
    {
        $method = 'sms.send';
        $params = array($fromNumber, $toNumber, $message, $priority);
        return $this->request($method, $params);
    }

    public function request($method, $params)
    {
        switch ($this->_apiEndpoint) {
            case 'jsonrpc':
                return $this->_jsonRpcRequest($method, $params);
                break;
            case 'xmlrpc':
                return $this->_xmlRpcRequest($method, $params);
                break;
            default:
                throw new Exception('Invalid API endpoint specified: '.$this->_apiEndpoint.'. Valid endpoints are jsonrpc and xmlrpc');
                break;
        }
    }

    protected function _jsonRpcRequest($method, $params)
    {
        $requestID = uniqid('',true);
        $request = json_encode(array('method' => $method, 'params' => $params, 'id'=>$requestID));
        $headers = array('Content-Type: application/json-rpc');
        $apiUrl = "{$this->_scheme}://{$this->_apiPrimaryHost}/jsonrpc?key={$this->_apiKey}";
        if ($this->_apiVersion) {
            $apiUrl .= "&apiVersion={$this->_apiVersion}";
        }
        $file = $this->_curlRequest($apiUrl, $request, $headers);
        if (!$file) {
            // Failover
            $apiUrl = "{$this->_scheme}://{$this->_apiSecondaryHost}/jsonrpc?key={$this->_apiKey}";
            if ($this->_apiVersion) {
                $apiUrl .= "&apiVersion={$this->_apiVersion}";
            }
            $file = $this->_curlRequest($apiUrl, $request, $headers);
            if (!$file) {
                throw new Exception('SMS Cloud API failed to respond. Please check internet connection or contact support.');
                return false;
            }
        }
        $response = @json_decode($file, true);
        if (!$response) {
            throw new Exception('API returned an invalid response. Please notify support of the following response: '.$file);
            return false;
        }
        return $response;
    }

    protected function _xmlRpcRequest($method, $params)
    {
        $request = xmlrpc_encode_request($method, $params);
        $headers = array('Content-Type: text/xml');
        $apiUrl = "{$this->_scheme}://{$this->_apiPrimaryHost}/xmlrpc?key={$this->_apiKey}";
        if ($this->_apiVersion) {
            $apiUrl .= "&apiVersion={$this->_apiVersion}";
        }
        $file = $this->_curlRequest($apiUrl, $request, $headers);
        if (!$file) {
            // Failover
            $apiUrl = "{$this->_scheme}://{$this->_apiSecondaryHost}/xmlrpc?key={$this->_apiKey}";
            if ($this->_apiVersion) {
                $apiUrl .= "&apiVersion={$this->_apiVersion}";
            }
            $file = $this->_curlRequest($apiUrl, $request, $headers);
            if (!$file) {
                throw new Exception('SMS Cloud API failed to respond. Please check internet connection or contact support.');
                return false;
            }
        }
        $response = @xmlrpc_decode($file);
        if (!$response) {
            throw new Exception('API returned an invalid response. Please notify support of the following response: '.$file);
            return false;
        }
        if ($response && xmlrpc_is_fault($response)) {
            throw new Exception("XML-RPC Fault: {$response['faultCode']} - {$response['faultString']}");
        } else {
            return $response;
        }
    }

    protected function _newCurlHandle($url, $postData = false, $headers = false)
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init($url);
        if ($postData) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if (substr($url, 0, 5) == 'https' && $this->_scheme == 'https') {
            curl_setopt($ch, CURLOPT_PORT , 443);
            curl_setopt($ch, CURLOPT_SSLVERSION, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // because we might use IP instead of host
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        return $ch;
    }

    protected function _curlRequest($url, $postData = false, $headers = false)
    {
        $ch = $this->_newCurlHandle($url, $postData, $headers);
        $return = curl_exec($ch);
        curl_close($ch);
        if(!$return){ return false; }
        return $return;
    }

}
