<?php

namespace Olyslager\API;

use SoapClient;
use SimpleXMLElement;
use Olyslager\API\Exception\InvalidResponseException;

class Api
{
    /** @var SoapClient */
    protected $client;

    /** @var array */
    protected $defaultParams = [];

    /**
     * @param string $wsdl
     * @param string $UserName
     * @param string $Password
     * @param string $LanguageISO3
     */
    public function __construct($wsdl, $UserName, $Password, $LanguageISO3)
    {
        $this->client = new SoapClient($wsdl, [
            'trace' => true,
            'exceptions' => true,
        ]);

        $this->defaultParams = [
            'UserName' => $UserName,
            'Password' => $Password,
            'LanguageISO3' => $LanguageISO3,
        ];
    }

    /**
     * @param array $params
     * @return \SimpleXMLElement
     */
    protected function makeRequest($params = [])
    {
        $method = debug_backtrace()[1]['function'];

        $response = $this->client->$method(
            (object) array_merge($this->defaultParams, $params)
        );

        $prop = ucfirst($method . 'Result');

        $result = simplexml_load_string($response->$prop->any);

        $this->checkResultStatus($result);

        return $result;
    }

    /**
     * @param \SimpleXMLElement $result
     * @throws \Olyslager\API\Exception\InvalidResponseException
     */
    protected function checkResultStatus(SimpleXMLElement $result)
    {
        if (isset($result->resultcode) && $result->resultcode !== 1) {
            throw new InvalidResponseException($result->resultdescription . " (code: $result->resultcode)");
        }
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getCategoryList()
    {
        return $this->makeRequest();
    }

    /**
     * @param int $CategoryID
     * @return \SimpleXMLElement
     */
    public function getMakeList($CategoryID)
    {
        return $this->makeRequest(['CategoryID' => $CategoryID]);
    }

    /**
     * @param string $MakeID
     * @return \SimpleXMLElement
     */
    public function getModelList($MakeID)
    {
        return $this->makeRequest(['MakeID' => $MakeID]);
    }

    /**
     * @param string $ModelID
     * @return \SimpleXMLElement
     */
    public function getTypeList($ModelID)
    {
        return $this->makeRequest(['ModelID' => $ModelID]);
    }

    /**
     * @param string      $SearchText
     * @param null|string $CategoryID
     * @param null|string $BuildYear
     * @return \SimpleXMLElement
     */
    public function getTypeListFromSearch($SearchText, $CategoryID = null, $BuildYear = null)
    {
        return $this->makeRequest([
            'SearchText' => $SearchText,
            'CategoryID' => $CategoryID,
            'BuildYear' => $BuildYear,
        ]);
    }

    /**
     * TypeID2Recommendation
     *
     * @param string $Type
     * @return \SimpleXMLElement
     */
    public function TypeID2Recommendation($Type)
    {
        return $this->makeRequest(['Type' => $Type]);
    }
}
