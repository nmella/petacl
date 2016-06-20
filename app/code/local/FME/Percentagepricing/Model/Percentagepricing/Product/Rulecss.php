<?php
/**
 * Add Product to CMS extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    AddProducttocms
 * @author     Mahmood.KK <mahmood.rehman@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
class FME_Percentagepricing_Model_Percentagepricing_Product_Rulecss extends FME_Percentagepricing_Model_Percentagepricing_Rule
{
   public function getConditionsInstance()
   { 
        return Mage::getModel('percentagepricing/rule_condition_combine');
   }

   public function _resetConditions($conditions=null)
   { 
        parent::_resetConditions($conditions);
        $this->getConditions($conditions)
                ->setId('css_conditions')
                ->setPrefix('css');
                
        return $this;
   }

}
