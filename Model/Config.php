<?php
declare(strict_types=1);

namespace Monogo\QrCode\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    private const XML_PATH_CLI_BATCH_SIZE = 'qr_code/cli/batch_size';
    private const XML_PATH_API_URL = 'qr_code/api/url';
    private const XML_PATH_API_USERNAME = 'qr_code/api/username';
    private const XML_PATH_API_PASSWORD = 'qr_code/api/password';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * Config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface   $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_URL);
    }

    /**
     * @return string
     */
    public function getApiUsername(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_API_USERNAME);
    }

    /**
     * @return string
     */
    public function getApiPassword(): string
    {
        $configValue = (string)$this->scopeConfig->getValue(self::XML_PATH_API_PASSWORD);
        return $configValue ? $this->encryptor->decrypt($configValue) : '';
    }

    /**
     * @return string
     */
    public function getBatchSize(): string
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_CLI_BATCH_SIZE);
    }
}
