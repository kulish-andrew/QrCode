<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\RabbitMQ\Products;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\Serialize\SerializerInterface;
use Monogo\QrCode\Api\Clients\QrCodeInterface;
use Monogo\QrCode\Api\Data\QrCodeAttributeInterface;
use Monogo\QrCode\Model\ApiClients\QrCode;
use Psr\Log\LoggerInterface;

class SetQrCodeAttrConsumer
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var Action
     */
    private Action $productAction;

    /**
     * @var QrCodeInterface
     */
    private QrCodeInterface $qrCodeApiClient;

    /**
     * UpdateConsumer constructor
     *
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param Action $productAction
     * @param QrCode $qrCodeApiClient
     */
    public function __construct(
        LoggerInterface     $logger,
        SerializerInterface $serializer,
        Action              $productAction,
        QrCodeInterface     $qrCodeApiClient
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->productAction = $productAction;
        $this->qrCodeApiClient = $qrCodeApiClient;
    }

    /**
     * @param string $productData
     */
    public function processMessage(string $productData): void
    {
        try {
            $productDataList = $this->serializer->unserialize($productData);
            foreach ($productDataList as $productId => $qrCodeData) {
                $qrCode = $this->qrCodeApiClient->getQrCodeData($qrCodeData);
                if ($qrCode) {
                    $this->productAction->updateAttributes(
                        [$productId],
                        [QrCodeAttributeInterface::ATTR_CODE => $qrCode],
                        0
                    );
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
