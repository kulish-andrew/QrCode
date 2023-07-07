<?php
declare(strict_types=1);

namespace Monogo\QrCode\ViewModel;

use Magento\Framework\Api\ImageContent;
use Magento\Framework\Api\ImageContentFactory;
use Magento\Framework\Api\ImageContentValidatorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Psr\Log\LoggerInterface;

class QrCode implements ArgumentInterface
{
    /**
     * @var ImageContentFactory
     */
    private ImageContentFactory $imageContentFactory;

    /**
     * @var ImageContentValidatorInterface
     */
    private ImageContentValidatorInterface $imageContentValidator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ImageContentFactory $imageContentFactory
     * @param ImageContentValidatorInterface $imageContentValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImageContentFactory            $imageContentFactory,
        ImageContentValidatorInterface $imageContentValidator,
        LoggerInterface                $logger
    ) {
        $this->imageContentFactory = $imageContentFactory;
        $this->imageContentValidator = $imageContentValidator;
        $this->logger = $logger;
    }

    /**
     * @param string $base64Image
     * @return bool
     */
    public function isBase64Image(string $base64Image): bool
    {
        try {
            return $this->validateImage($base64Image);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * @param string $base64Image
     * @return bool
     * @throws InputException
     * @throws LocalizedException
     */
    protected function validateImage(string $base64Image)
    {
        $decodedImage = base64_decode($base64Image);
        if (!$decodedImage) {
            return false;
        }

        $imageProperties = getimagesizefromstring($decodedImage);
        if (!$imageProperties) {
            throw new LocalizedException(__('Unable to get properties from image.'));
        }

        /* @var ImageContent $imageContent */
        $imageContent = $this->imageContentFactory->create();
        $imageContent->setBase64EncodedData($base64Image);
        $imageContent->setName('qrcode');
        $imageContent->setType($imageProperties['mime']);

        return $this->imageContentValidator->isValid($imageContent);
    }
}
