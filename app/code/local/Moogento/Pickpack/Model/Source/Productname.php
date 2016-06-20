<?php 
/** 
* Moogento
* 
* SOFTWARE LICENSE
* 
* This source file is covered by the Moogento End User License Agreement
* that is bundled with this extension in the file License.html
* It is also available online here:
* https://moogento.com/License.html
* 
* NOTICE
* 
* If you customize this file please remember that it will be overwrtitten
* with any future upgrade installs. 
* If you'd like to add a feature which is not in this software, get in touch
* at www.moogento.com for a quote.
* 
* ID          pe+sMEDTrtCzNq3pehW9DJ0lnYtgqva4i4Z=
* File        Filterbystatus.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class Moogento_Pickpack_Model_Source_Productname
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
          return array(
         array('value' => 'storeview', 'label' => Mage::helper('pickpack')->__('Name from Product > Order Store *Default*')),
         array('value' => 'default', 'label' => Mage::helper('pickpack')->__('Name from Product > Default Store')),
         array('value' => 'specificstore', 'label' => Mage::helper('pickpack')->__('Name from Product > Specific Store')),
         array('value' => 'itemname', 'label' => Mage::helper('pickpack')->__('Name from Order'))
     );
    }
}
