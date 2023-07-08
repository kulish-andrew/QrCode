<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\ApiClients;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\SerializerInterface;
use Monogo\QrCode\Api\Clients\QrCodeInterface;
use Monogo\QrCode\Model\Config;

class QrCode implements QrCodeInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var ClientFactory
     */
    private ClientFactory $clientFactory;

    /**
     * @var ResponseFactory
     */
    private ResponseFactory $responseFactory;

    /**
     * QrCode constructor
     *
     * @param Config $config
     * @param SerializerInterface $serializer
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        Config              $config,
        SerializerInterface $serializer,
        ClientFactory       $clientFactory,
        ResponseFactory     $responseFactory
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param string $data
     * @return string|null
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function getQrCodeData(string $data): ?string
    {
        $response = $this->doRequest($data);
        $content = $response->getBody()->getContents();
        if (!$content) {
            throw new LocalizedException(__('[Monogo_QrCode]: No content for requested data: %1', $data));
        }

        $unSerializedContent = $this->serializer->unserialize($content);
        $qrCode = $unSerializedContent[self::QR_CODE_FIELD] ?? null;
        if (!$qrCode) {
            throw new LocalizedException(__('[Monogo_QrCode]: No QR Code data. Response: %1', $content));
        }

        return $qrCode;
    }

    /**
     * @param string $data
     * @param array $parameters
     * @return Response
     * @throws ValidatorException
     */
    private function doRequest(string $data, array $parameters = []): Response
    {
        $parameters = $this->prepareParams($data, $parameters);
        $client = $this->clientFactory->create();

        try {
            $response = $client->request(
                self::REQUEST_METHOD,
                $this->getEndpointUrl(),
                $parameters
            );
        } catch (GuzzleException $exception) {
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
    }

    /**
     * @param string $data
     * @param array $parameters
     * @return array
     * @throws ValidatorException
     */
    private function prepareParams(string $data, array $parameters): array
    {
        $parameters[RequestOptions::HEADERS] = [
            'Content-Type' => self::CONTENT_TYPE,
            'cache-control' => self::CACHE_CONTROL
        ];
        $parameters[RequestOptions::HEADERS]['Authorization'] = $this->getAuthData();
        $parameters['json'] = $this->serializer->serialize(['plainText' => $data]);

        return $parameters;
    }

    /**
     * @return string
     * @throws ValidatorException
     */
    private function getAuthData(): string
    {
        $username = $this->config->getApiUsername();
        $password = $this->config->getApiPassword();
        if (!$username || !$password) {
            throw new ValidatorException(__('[Monogo_QrCode]: Please verify credentials and try again.'));
        }
        $hash = base64_encode($username . ':' . $password);

        return "Basic {$hash}";
    }

    /**
     * @return string
     * @throws ValidatorException
     */
    private function getEndpointUrl(): string
    {
        $uriEndpoint = $this->config->getApiUrl();
        if (!$uriEndpoint) {
            throw new ValidatorException(__('[Monogo_QrCode]: Please verify the endpoint URL.'));
        }

        return $uriEndpoint;
    }
}
