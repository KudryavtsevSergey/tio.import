<?php

namespace Tio\Import\Gateway;

abstract class BusGateway
{
    protected abstract function getApiUrl();

    /**
     * @param string $method
     * @param array $parameters
     * @return bool|false|mixed|string
     */
    protected function sendRequest(string $method, array $parameters = [])
    {
        $url = $this->getApiUrl() . $method;

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        $result = file_get_contents($url, false, stream_context_create(['http' => ['method' => "GET", 'header' => 'Accept-Charset: UTF-8, *;q=0']]));

        $result = mb_convert_encoding($result, "HTML-ENTITIES", "UTF-8");
        return $result;
    }

    public abstract function sendListRequest();

    public abstract function sendDetailRequest(string $method);
}
