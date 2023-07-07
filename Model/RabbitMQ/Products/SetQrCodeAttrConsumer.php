<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\RabbitMQ\Products;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\Serialize\SerializerInterface;
use Monogo\QrCode\Model\ApiClients\QrCode;
use Monogo\QrCode\Setup\Patch\Data\AddQrCodeDataProductAttribute;
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
     * @var QrCode
     */
    private QrCode $qrCodeApiClient;

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
        QrCode              $qrCodeApiClient
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
                    $this->productAction->updateAttributes([$productId], [AddQrCodeDataProductAttribute::ATTR_CODE => $qrCode], 0);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
