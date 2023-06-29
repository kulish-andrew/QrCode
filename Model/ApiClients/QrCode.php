<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\ApiClients;

use JsonException;
use Monogo\QrCode\Model\Config;
use RuntimeException;

class QrCode
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * QrCode constructor
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param string $data
     * @return string
     * @throws JsonException
     */
    public function call(string $data): string
    {
        $ch = curl_init();
        if (false === $ch) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        $requestData = json_encode(['plainText' => $data], JSON_THROW_ON_ERROR);
        $this->setCurlOptions($ch, $requestData);
        $content = curl_exec($ch);

        if (false === $content) {
            $errorCode = curl_errno($ch);
            $errorMessage = curl_error($ch);
            throw new RuntimeException(
                sprintf('cURL failed with error #%d: %s', $errorCode, $errorMessage),
                $errorCode
            );
        }
        curl_close($ch);

        return $content;
    }

    /**
     * @param $ch
     * @param string $requestData
     * @return void
     */
    private function setCurlOptions($ch, string $requestData): void
    {
        $url = $this->config->getApiUrl();
        $username = $this->config->getApiUsername();
        $password = $this->config->getApiPassword();
        $hash = base64_encode($username . ':' . $password);
        $headers = ['Authorization: Basic ' . $hash];

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers,
        ]);
    }
}
