<?php

namespace Monogo\QrCode\Api\Clients;

use Magento\Framework\Webapi\Rest\Request;

interface QrCodeInterface
{
    public const QR_CODE_FIELD = 'base64QRCode';
    public const REQUEST_METHOD = Request::HTTP_METHOD_POST;
    public const CONTENT_TYPE = 'application/json';
    public const CACHE_CONTROL = 'no-cache';

    /**
     * @param string $data
     * @return string|null
     */
    public function getQrCodeData(string $data): ?string;
}
