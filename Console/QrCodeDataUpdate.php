<?php
declare(strict_types=1);

namespace Monogo\QrCode\Console;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Serialize\SerializerInterface;
use Monogo\QrCode\Model\Config;
use Monogo\QrCode\Model\RabbitMQ\Products\ProductDataPublisher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QrCodeDataUpdate extends Command
{
    private const COMMAND_QRCODE_UPDATE = 'monogo:qrcode:update';
    private const COMMAND_QRCODE_UPDATE_DESCRIPTION = 'QR Code product attribute data update';
    private const COMMAND_SIZE_OPTION_NAME = 'size';
    private const COMMAND_SKU_OPTION_NAME = 'sku';

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $productCollectionFactory;

    /**
     * @var ProductDataPublisher
     */
    private ProductDataPublisher $productDataPublisher;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Config constructor
     *
     * @param Config $config
     * @param CollectionFactory $productCollectionFactory
     * @param ProductDataPublisher $productDataPublisher
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        Config               $config,
        CollectionFactory    $productCollectionFactory,
        ProductDataPublisher $productDataPublisher,
        SerializerInterface  $serializer,
        LoggerInterface      $logger,
        string               $name = null
    ) {
        $this->config = $config;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productDataPublisher = $productDataPublisher;
        $this->serializer = $serializer;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(self::COMMAND_QRCODE_UPDATE);
        $this->setDescription(self::COMMAND_QRCODE_UPDATE_DESCRIPTION);
        $this->setHelp('The command will update products and set QR Code data into an appropriate attribute.');
        $this->addOption(
            self::COMMAND_SIZE_OPTION_NAME,
            's',
            InputOption::VALUE_OPTIONAL,
            'Batch Size'
        );
        $this->addOption(
            self::COMMAND_SKU_OPTION_NAME,
            null,
            InputOption::VALUE_OPTIONAL,
            'SKU list(comma separated)'
        );

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $batchSize = $input->getOption(self::COMMAND_SIZE_OPTION_NAME);
            if (!$batchSize) {
                $batchSize = $this->config->getBatchSize();
            }

            $skuList = $input->getOption(self::COMMAND_SKU_OPTION_NAME);
            if ($skuList) {
                $skuList = $this->convertSkusToArray($skuList);
            }

            $this->updateProducts($output, $batchSize, $skuList);
            $output->writeln('<info>Successfully done.</info>');
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $output->writeln('<error>An error was encountered.</error>');
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @param string $batchSize
     * @param array|null $skuList
     * @return void
     */
    public function updateProducts(OutputInterface $output, string $batchSize, ?array $skuList): void
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        if ($skuList) {
            $collection->addFieldToFilter('sku', ['in' => $skuList]);
        }
        $collection->setPageSize($batchSize);
        $pages = $collection->getLastPageNumber();
        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $collection->setCurPage($currentPage);
            $collection->load();
            $productData = [];
            foreach ($collection as $product) {
                $productData[$product->getId()] = $product->getName();
            }
            $productDataJson = $this->serializer->serialize($productData);
            $this->productDataPublisher->execute($productDataJson);
            $collection->clear();
        }
    }

    /**
     * @param string $skus
     * @return array
     */
    private function convertSkusToArray(string $skus): array
    {
        $skuList = explode(',', $skus);
        return array_map('trim', $skuList);
    }
}
