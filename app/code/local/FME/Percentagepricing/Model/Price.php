<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product type price model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class FME_Percentagepricing_Model_Price extends Mage_Catalog_Model_Product_Type_Price
{
    
    public function getPrice($product)
    {
        //load product to get the all required data
        $model = Mage::getModel('catalog/product'); 
        $_product = $model->load($product->getId());
        
        //get value of cost
        $finalPrice = $_product->getData('cost');
        //get value of disable rule
        $ruleEnable = $_product->getAttributeText('fme_rule_enable');
        
        //check rule is allow to apply
        //if($ruleEnable=="No"):
        if($ruleEnable=="Yes"):
        

            $customergroupId = (int) Mage::getSingleton('customer/session')->getCustomerGroupId();
            $storeId = (int) Mage::app()->getStore()->getStoreId();
            //get collection of our rules only when(status=enable, storevalue is true and customer group is true)
            $collection = Mage::getModel('percentagepricing/percentagepricing')->getCollection();
            $collection->addFieldToFilter('status', 1);
            $collection->addFieldToFilter('customer_group_ids', array('finset'=>$customergroupId));
            $collection->setOrder('priorty', 'ASC');
            if($collection)
            {
                
                $boolen=true;
                foreach($collection as $_collection)
                {
                    
                    if((in_array("0",explode(",",$_collection->getWebsiteIds())))|| in_array($storeId,explode(",",$_collection->getWebsiteIds())))
                    {
                        $getdataofrule = unserialize($_collection->getPercentagepricingRule());
                        $checkvalue = $getdataofrule['conditions'];
                        if($checkvalue['value']==0)
                        $true = "false";
                        if($checkvalue['value']==1)
                        $true = "true";
                        $check_aggregator = $getdataofrule['conditions'];
                        $check_rule = $getdataofrule['conditions'];
                        $boolen=false;
                        $break = false;

                        foreach($check_rule['css'] as $_rules)
                        {
                        
                            if(($check_aggregator['aggregator']=="any")&&($boolen==true))
                            {
                                $break=true;
                            }
                            if($break==true)
                            break;
                            if($_rules['attribute']=='category_ids')
                            {
                                $result="0";
                                if(array_intersect($_product->getCategoryIds(), explode(",",$_rules['value'])))
                                $result="1";
                                if(($true=="false")&&($result=="1"))
                                {
                                    $boolen=false;
                                }
                                else if(($true=="true")&&($result=="0"))
                                {
                                    $boolen=false;
                                }
                                else
                                {
                                    $boolen=true;
                                }
                            }
                            else
                            {
                                $result="0";
                                if($_rules['operator']=="==")
                                {
									if(in_array($_product->getData(trim($_rules['attribute'])), explode(",",$_rules['value']),false))
									$result="1";
								}
                                if($_rules['operator']=="!=")
                                {
									if(!in_array($_product->getData(trim($_rules['attribute'])), explode(",",$_rules['value']),false))
									$result="1";
								}
                                if($_rules['operator']==">=")
                                {
									if($_product->getData(trim($_rules['attribute']))>= $_rules['value'])
									$result="1";
								}
                                if($_rules['operator']=="<=")
                                {
									if($_product->getData(trim($_rules['attribute']))<= $_rules['value'])
									$result="1";
								}
                                if($_rules['operator']==">")
                                {
									if($_product->getData(trim($_rules['attribute'])) > $_rules['value'])
									$result="1";
								}
                                if($_rules['operator']=="<")
                                {
									if($_product->getData(trim($_rules['attribute'])) < $_rules['value'])
									$result="1";
								}
                                if(($true=="false")&&($result=="1"))
                                {
                                    $boolen=false;
                                }
                                else if(($true=="true")&&($result=="0"))
                                {
                                    $boolen=false;
                                }
                                else
                                {
                                    $boolen=true;
                                }
                            }
                        
                            if(($check_aggregator['aggregator']=="all")&&($boolen==false))
                            {
                                //echo"<br>come in aggregate all and bolen false";
                                $break=true;
                            }
                        
                        }
                        //echo"////////////////////////////////////////////////";
                        if($boolen==true)
                        {
                            //for fixed
                            if($_collection->getApply()=="2")
                            {
								if($_collection->getAction()=="0")
								$finalPrice = $finalPrice+$_collection->getAmount();
								if($_collection->getAction()=="1")
								$finalPrice = $finalPrice-$_collection->getAmount();
							}
                            //for %
                            if($_collection->getApply()=="1")
                            {
                                $percentageamount = $finalPrice*($_collection->getAmount()/100);
								if($_collection->getAction()=="0")
								$finalPrice = $finalPrice+$percentageamount;
								if($_collection->getAction()=="1")
								$finalPrice = $finalPrice-$percentageamount;
                                
                            }
                            $product->setIsSuperMode(true);
                            $product->setData('price', $finalPrice);
                            $product->setData('special_price', $finalPrice);
                            $product->setData('final_price', $finalPrice);
                            break;
                        }
                    }
                }
            }//endif

        endif;
        
        //return the value
        return $product->getData('price'); 
    }

}
