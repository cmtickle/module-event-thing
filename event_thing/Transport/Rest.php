<?php

namespace Cmtickle\EventThingRemoteConfig\Transport;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Rest extends \Cmtickle\EventThing\Transport\BaseTransport
{
    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;
    protected \Magento\Framework\Serialize\Serializer\Json $serialize;

    protected bool|string $restUrl = false;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $serialize
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serialize
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serialize = $serialize;
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function getRestUrl(): mixed
    {
        if (false === $this->restUrl) {
            $this->restUrl = $this->scopeConfig->getValue(
                'event_thing/rest/url',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            );
        }

        if (!$this->restUrl) {
            throw new \Exception('Event Thing REST URL not defined, check documentation.');
        }

        return $this->restUrl;
    }

    public function send(array $data):array
    {
        $curl = curl_init($this->getRestUrl());
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->serialize->serialize($data));
        $response = curl_exec($curl);

        return $this->serialize->Unserialize($response ?: $data);
    }
}
