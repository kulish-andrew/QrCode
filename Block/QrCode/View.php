<?php
declare(strict_types=1);

namespace Monogo\QrCode\Block\QrCode;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class View extends Template
{
    /**
     * @var Product|null
     */
    protected ?Product $product = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        if (!$this->product) {
            $this->product = $this->_coreRegistry->registry('product');
        }

        return $this->product;
    }

    /**
     * @param string $base64Image
     * @return bool
     */
    public function isBase64Image(string $base64Image): bool
    {
        $decodedImage = base64_decode($base64Image, true);
        if (!$decodedImage) {
            return false;
        }

        $fileInfo = finfo_open();
        $mimeType = finfo_buffer($fileInfo, $decodedImage, FILEINFO_MIME_TYPE);
        finfo_close($fileInfo);

        return str_starts_with($mimeType, 'image/');
    }
}
