<?php

namespace Tests\Support\Traits;

trait CsrfPostTrait
{
    protected function csrfParams(array $params = []): array
    {
        $params[csrf_token()] = csrf_hash();

        return $params;
    }

    protected function postWithCsrf(string $url, array $params = [])
    {
        return $this->post($url, $this->csrfParams($params));
    }
}
