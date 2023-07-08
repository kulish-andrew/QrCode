<?php

namespace Monogo\QrCode\Api\Clients;

interface QrCodeInterface
{
    const QR_CODE_FIELD = 'base64QRCode';

    /**
     * @param string $data
     * @return string|null
     */
    public function getQrCodeData(string $data): ?string;
}
