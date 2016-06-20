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
* File        Day.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Source_Condition_Day
{
    public function toOptionArray() {
        return array(
            array(
                'value' => 0,
                'label' => 'Sunday',
            ),
            array(
                'value' => 1,
                'label' => 'Monday',
            ),
            array(
                'value' => 2,
                'label' => 'Tusday',
            ),
            array(
                'value' => 3,
                'label' => 'Wednesday',
            ),
            array(
                'value' => 4,
                'label' => 'Thursday',
            ),
            array(
                'value' => 5,
                'label' => 'Friday',
            ),
            array(
                'value' => 6,
                'label' => 'Saturday',
            ),
        );
    }
}
