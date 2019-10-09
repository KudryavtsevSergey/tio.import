<?php

namespace Tio\Import\Gateway;

use Tio\Import\Adapters\CacheAdapter;

class BelOrientirGateway extends BusGateway
{
    protected function getApiUrl()
    {
        return 'https://www.bel-orientir.ru';
    }

    public function sendListRequest()
    {
        $id = 'list-bel-orientir';
        $cache = new CacheAdapter($id);

        if (!empty($result = $cache->get())) {
            return $result[$id];
        }

        $response = $cache->caching(function () use ($id) {
            return [$id => $this->sendRequest('/tours/bus/')];
        });

        return $response[$id];
    }

    public function sendDetailRequest(string $method)
    {
        $id = "detail-bel-orientir{$method}";
        $cache = new CacheAdapter($id);

        if (!empty($result = $cache->get())) {
            return $result[$id];
        }

        $response = $cache->caching(function () use ($id, $method) {
            return [$id => $this->sendRequest($method)];
        });

        return $response[$id];
    }
}