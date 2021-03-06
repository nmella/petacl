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
* File        Notesmessage.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Model_Source_Labelzebra_Ordernotesfilteroption
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return array(
            array('value' => 'yesfrontend', 'label' => Mage::helper('pickpack')->__('Yes, show only notes tagged \'visible on frontend\'')),
            array('value' => 'no', 'label'=>Mage::helper('pickpack')->__('No'))
        );
    }

}
