<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model\RabbitMQ\Products;

use Exception;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class ProductDataPublisher
{
    private const TOPIC_NAME = 'qrcode.processing';

    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * ProductDataPublisher constructor.
     *
     * @param PublisherInterface $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        PublisherInterface $publisher,
        LoggerInterface    $logger
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @param string $productData
     */
    public function execute(string $productData): void
    {
        try {
            $this->publisher->publish(self::TOPIC_NAME, $productData);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
