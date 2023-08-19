<?php
namespace Vendor\CustomFieldUpdateModule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Magento\Framework\App\Response\RedirectInterface;

class CategorySaveAfter implements ObserverInterface
{
    protected $redirect;

    public function __construct(
        RedirectInterface $redirect
    ) {
        $this->redirect = $redirect;
    }

    public function execute(Observer $observer)
    {
        // Category Smart Contract Variables Update and call SC etc
        $category = $observer->getEvent()->getData('category');

        $categoryId = $category->getId();
        $categoryName = $category->getName();
        $categoryLevel = $category->getLevel();
        $parentId = $category->getParentId();
        $parentCategory = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Catalog\Model\CategoryFactory::class)->create()->load($parentId);

        $parentCategoryName = '';
        if ($parentCategory)    $parentCategoryName = $parentCategory->getName();

        $customFields = array();

        // $customFields[0] = $payment_method = $category->getData("payment_method");
        $customFields[0] = $direct_bonus_attr = $category->getData('direct_bonus_attr');
        $customFields[1] = $reserve_for_matching_bonus_attr = $category->getData('reserve_for_matching_bonus_attr');
        $customFields[2] = $reserve_for_team_bonus_attr = $category->getData('reserve_for_team_bonus_attr');
        $customFields[3] = $reserve_for_range_bonus_attr = $category->getData('reserve_for_range_bonus_attr');
        $customFields[4] = $reserve_for_anual_bonus_attr = $category->getData('reserve_for_anual_bonus_attr');
        $customFields[5] = $genu_revenues_attr = $category->getData('genu_revenues_attr');
        $customFields[6] = $salesman_attr = $category->getData('salesman_attr');
        $customFields[7] = $community_fund_attr = $category->getData('community_fund_attr');
        $customFields[8] = $liquidity_fund_attr = $category->getData('liquidity_fund_attr');
        $customFields[9] = $infrastructure_expenses_attr = $category->getData('infrastructure_expenses_attr');
        $customFields[10] = $i_d_attr = $category->getData('i_d_attr');
        $customFields[11] = $others_attr = $category->getData('others_attr');
        $customFields[12] = $founders_attr = $category->getData('founders_attr');

        // Call Smart Contract with $categoryName, $parentCategoryName, $payment_method ..
        $canBeUpdated = true;
        if ($categoryLevel > 3 || $categoryLevel == 1) $canBeUpdated = false;
        if ($categoryLevel == 3) {
            // $status = (($payment_method == "MOS" || $payment_method == "UNE") && $our_margin_attr != "" && $direct_bonus_attr != "" && $reserve_for_matching_bonus_attr != ""
            //     && $reserve_for_team_bonus_attr != "" && $reserve_for_range_bonus_attr != "" && $reserve_for_anual_bonus_attr != "" && 
            //     $net_after_bonuses_reservation_attr != "" && $administrative_cost_attr != "" && $genu_revenues_attr != "" && $salesman_attr!= "" &&
            //     $community_fund_attr != "" && $liquidity_fund_attr != "" && $infrastructure_expenses_attr != "" && $partners_attr != "" && $i_d_attr != "" && 
            //     $others_attr != "" && $founders_attr != "");

            // Call Mobile app backend API

            $url = 'http://167.86.118.183:80/transaction/setMagentCategory';
            $params = array(
                'id' => $categoryId,
                'category' => $parentCategoryName,
                'subcategory' => $categoryName,
                'bonuspercentage' => $customFields
            );
    
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query($params),
                ),
            );
    
            // $context = stream_context_create($options);
            // $response = file_get_contents($url, false, $context);
            
            // if ($response === false) {
                // handle error
            // } else {
                // handle response
                // echo $response;
            // }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
	    $response = curl_exec($ch);
	    
	    if ($response === false) {
		    // Handle cURL error
		    $error = curl_error($ch);
	    } else {
		    // Process the API response
		    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    if ($httpCode === 200) {
			// API call successful
			// Handle the response data
			// echo $response;
			$canBeUpdated = true;
		    } else {
			// API call failed
			// Handle the error
			// echo "API call failed with HTTP code: " . $httpCode;
			$canBeUpdated = false;
		    }
	    }

	    // Close the cURL session
	    curl_close($ch);
	    
	    // $canBeUpdated = true;
        }
       
        if (!($canBeUpdated)) {
            // if INFO is wrong, then it redirects
            $this->redirect->redirect($this->getResponse(), 'catalog/category/view', ['id' => $categoryId]);
        }
    }
}
