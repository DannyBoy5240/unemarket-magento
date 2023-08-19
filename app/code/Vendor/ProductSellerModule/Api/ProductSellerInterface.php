<?php
namespace Vendor\ProductSellerModule\Api;
 
interface ProductSellerInterface
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