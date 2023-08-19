<?php
namespace Vendor\VendorWalletAddressModule\Api;
 
interface VendorWalletAddressInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $id product sku array json.
     * @return string Greeting message with users name.
     */
    public function execute($id);
}