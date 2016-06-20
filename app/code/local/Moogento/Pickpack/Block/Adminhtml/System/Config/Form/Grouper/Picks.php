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
* File        Picks.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Grouper_Picks
    extends Moogento_Pickpack_Block_Adminhtml_System_Config_Form_Grouper_Abstract
{
    protected $_openers = array(
        'pickpack_options_picks_auto_processing' => 'Automated printing action',
        'pickpack_options_picks_auto_processing_check' => 'Order filter'                
    );
    
    protected $_openers_none_border = array(
        'pickpack_options_picks_auto_processing_additional_action' =>'Order action',
        'pickpack_options_picks_auto_processing_condition_type' =>'Automated order processing'
    );
    protected $_closers = array(
        'pickpack_options_picks_auto_processing_groupping' => true,
        'pickpack_options_picks_auto_processing_check_attribute_value' => true,
        
		'pickpack_options_picks_szy_own_value2' => true,
		'pickpack_options_picks_auto_processing_szy_own_value2' => true,
		
    );

}
