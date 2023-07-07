# Monogo_QrCode

Monogo_QrCode is a Magento 2 module that allows you to generate QR codes by sending requests to
the `https://www.de-vis-software.ro/qrcodeme.aspx` endpoint.

## Installation

1. Download the Monogo_QrCode module.
2. Extract the module files and copy them to the `app/code/Monogo/QrCode` directory of your Magento 2 installation.
3. Run the following command in the Magento 2 root directory:
   #### bin/magento module:enable Monogo_QrCode
   #### bin/magento setup:upgrade
4. Flush the cache by running:
   #### bin/magento cache:flush
5. Fill in the configuration settings for Monogo_QrCode. In the Magento admin panel, navigate to `Stores -> Configuration -> MONOGO -> QR Code` and enter the required information.
## Usage

After installing the Monogo_QrCode module, you can generate QR codes by making requests to the `https://www.de-vis-software.ro/qrcodeme.aspx` endpoint. The module provides a CLI command `monogo:qrcode:update` to generate QR codes in batch mode.

To generate QR codes using the CLI command, run the following command:
#### bin/magento monogo:qrcode:update [--size BATCH_SIZE] [--sku SKU_LIST]
The command supports the following optional parameters:

- `--size`: Specifies the batch size for generating QR codes (default: 100).
- `--sku`: Specifies a comma-separated list of SKUs for which QR codes should be generated. If not provided, QR codes will be generated for all products.

Example usage:
#### bin/magento monogo:qrcode:update --size=50 --sku=SKU001,SKU002,SKU003
This command will send requests to the `https://www.de-vis-software.ro/qrcodeme.aspx` endpoint, generating QR codes for the specified SKUs in batches of 50.

## Contributing

Contributions are welcome! If you encounter any issues or have suggestions for improvements, please open an issue or submit a pull request on the [GitHub repository](https://github.com/monogo/qr-code).

## License

Monogo_QrCode is released under the [MIT License](LICENSE.txt).


### Test Task Requirements for module
Preparation of product attribute update module which also formats the attribute value to a
QR code
1. create a new product EAV attribute using magento installer
2. using the CLI command and magento queues, copy the names of all products to the
   newly created product attribute
3. CLI command should be constructed in such a way, that you can pass a number of
   synchronized products as an argument, otherwise - read it from configuration.
4. the new attribute should be supported in Magento Web API both when reading and
   writing the product using a view model feature
5. the QR code with the saved value of the attribute should be displayed on the product
   page
6. to generate the QR code, use the API
   https://www.de-vis-software.ro/qr-code-generator-api.aspx service
