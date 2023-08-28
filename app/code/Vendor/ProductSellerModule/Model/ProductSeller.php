<?php
namespace Vendor\ProductSellerModule\Model;
use Vendor\ProductSellerModule\Api\ProductSellerInterface;

use Magento\Framework\App\ResourceConnection;
 
class ProductSeller implements ProductSellerInterface
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

        $resArr = [];

        $temp = json_decode($id, true);

        $customerEmail = '';
        $arrId = [];
        if ($temp && count($temp) > 0 && is_array($temp)) {
            $tcou = 0;
            foreach ($temp as $item) {
                if ($tcou == 0) $arrId = $item;
                if ($tcou == 1) $customerEmail = $item;
                $tcou = $tcou + 1;
            }
        }

        $connection = $this->_resourceConnection->getConnection();

        // Get Product Information
        if (is_array($arrId)) {
            foreach ($arrId as $item) {
                $flag = 0;  // all is fine

                $tableName = $this->_resourceConnection->getTableName('catalog_product_entity');
                $select = $connection->select()->from($tableName)->where('sku = ?', $item);
                $result = $connection->fetchAll($select);
                if ($result && count($result) == 1)     $value = $result[0]['entity_id'];
                else $flag = 1; // sku not found

                if ($flag == 0) {
                    $tableName = $this->_resourceConnection->getTableName('marketplace_product');
                    $select = $connection->select()->from($tableName)->where('mageproduct_id = ?', $value);
                    $result = $connection->fetchAll($select);
                    if ($result && count($result) == 1)     $sellerId = $result[0]['seller_id'];
                    else $flag = 2; // seller_id not found
                }

                if ($flag == 0 || $flag == 2) {
                    $tableName = $this->_resourceConnection->getTableName('catalog_category_product');
                    // $select = $connection->select()
                    //     ->from(['ccp' => $tableName], ['category_id'])
                    //     ->join(
                    //         ['mp' => $this->_resourceConnection->getTableName('marketplace_product')],
                    //         'ccp.product_id = mp.mageproduct_id',
                    //         []
                    //     )
                    //     ->where('mp.mageproduct_id = ?', $value);
                    $select = $connection->select()->from($tableName)->where('product_id = ?', $value);
                    $result = $connection->fetchAll($select);
                    if ($result && count($result) == 1)      $categoryId = $result[0]["category_id"];
                    else $flag = 3;    // category_id not found
                }

                if ($flag == 0) {
                    $customerEntityTableName = $this->_resourceConnection->getTableName('customer_entity');
                    $customerEntityVarcharTableName = $this->_resourceConnection->getTableName('customer_entity_varchar');

                    $select = $connection->select()
                        ->from(['cev' => $customerEntityVarcharTableName], ['value'])
                        ->join(['ce' => $customerEntityTableName], 'ce.entity_id = cev.entity_id', [])
                        ->where('ce.entity_id = ?', $sellerId)
                        ->where('cev.attribute_id = ?', 201);   // wk_customfields : wallet Address
                    $result = $connection->fetchAll($select);
                    if ($result && count($result) == 1)      $walletAddress = $result[0]["value"];
                    else $flag = 4; // wallet address not found
                }

                $data = [
                    'sucess' => $flag,
                    'seller_id' => $flag == 0 || $flag == 3 ? $sellerId : '',
                    'category_id' => $flag == 0 || $flag == 4 || $flag == 2 ? $categoryId : '',
                    'wallet_addr' => $flag == 0 || $flag == 5 ? $walletAddress : '',
                ];
                $json = json_encode($data);

                array_push($resArr, $json);
            }
        }

        // Get OfficeID of the Customer
        $customerId = -1;
        $officeId = '';
        $tableName = $this->_resourceConnection->getTableName('customer_entity');
        $select = $connection->select()->from($tableName)->where('email = ?', $customerEmail);
        $result = $connection->fetchAll($select);
        if ($result && count($result) == 1) $customerId = $result[0]["entity_id"];

        if ($customerId != -1) {
            $tableName = $this->_resourceConnection->getTableName('customer_entity_varchar');
            $select = $connection->select()->from($tableName)->where('entity_id = ?', $customerId)->where('attribute_id = 203');
            $result = $connection->fetchAll($select);
            if ($result && count($result) == 1) $officeId = $result[0]["value"];
        }

        array_push($resArr, $officeId);

        return json_encode($resArr);
    }
}
