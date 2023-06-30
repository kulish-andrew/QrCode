<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\ApiClients;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\SerializerInterface;
use Monogo\QrCode\Model\Config;
use RuntimeException;

class QrCode
{
    private const QR_CODE_FIELD = 'base64QRCode';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * QrCode constructor
     *
     * @param Config $config
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Config              $config,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @param string $data
     * @return string|null
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function getQrCodeData(string $data): ?string
    {
        $ch = curl_init();
        if (false === $ch) {
            throw new RuntimeException('Failed to initialize cURL.');
        }

        $requestData = $this->serializer->serialize(['plainText' => $data]);
        $this->setCurlOptions($ch, $requestData);
        $content = curl_exec($ch);

        if (false === $content) {
            $errorCode = curl_errno($ch);
            $errorMessage = curl_error($ch);
            throw new LocalizedException(__('cURL failed with error #%1: %2', $errorCode, $errorMessage));
        }
        curl_close($ch);

        $content = $this->serializer->unserialize($content);
        return $content[self::QR_CODE_FIELD] ?? null;
    }

    /**
     * @param $ch
     * @param string $requestData
     * @return void
     * @throws ValidatorException
     */
    private function setCurlOptions($ch, string $requestData): void
    {
        $url = $this->config->getApiUrl();
        $username = $this->config->getApiUsername();
        $password = $this->config->getApiPassword();

        if (!$url || !$username || !$password) {
            throw new ValidatorException(__('Please verify credentials and try again.'));
        }

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
