<?php
namespace Vendor\VendorWalletAddressModule\Model;
use Vendor\VendorWalletAddressModule\Api\VendorWalletAddressInterface;

use Magento\Framework\App\ResourceConnection;
 
class VendorWalletAddress implements VendorWalletAddressInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $id Users name.
     * @return string Greeting message with users name.
     */
    public function execute($id) {

        $walletAddress = "";

        $connection = $this->_resourceConnection->getConnection();

        $tableName = $this->_resourceConnection->getTableName('marketplace_product');
        $select = $connection->select()->from($tableName)->where('mageproduct_id = ?', $id);
        $result = $connection->fetchAll($select);
        if ($result && count($result) == 1)     {
            $sellerId = $result[0]['seller_id'];

            $customerEntityTableName = $this->_resourceConnection->getTableName('customer_entity');
            $customerEntityVarcharTableName = $this->_resourceConnection->getTableName('customer_entity_varchar');

            $select = $connection->select()
                ->from(['cev' => $customerEntityVarcharTableName], ['value'])
                ->join(['ce' => $customerEntityTableName], 'ce.entity_id = cev.entity_id', [])
                ->where('ce.entity_id = ?', $sellerId)
                ->where('cev.attribute_id = ?', 201);   // wk_customfields : wallet Address
            $result = $connection->fetchAll($select);

            if ($result && count($result) == 1)      $walletAddress = $result[0]["value"];
        } else {
                $walletAddress = "0x3a0B9FBaE163907544a61aA321A17cE6194B7e5C";
        }

        return $walletAddress;
    }
}