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
* File        Shippingmethod.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Backend_Field_Shippingbackground_Shippingmethod
    extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave() {

        $value = $this->getValue();
        if ($post = Mage::app()->getRequest()->getParam('groups')) {
            if (isset($post['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value'])) {
                $value = $post['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value'];
            }
        }
        $oldValue = array();
        if ($this->getOldValue()) {
            try {
                $oldValue = unserialize($this->getOldValue());
            } catch(Exception $e) {
            }
        }
        if (!is_array($value)) {
            $value = '';
        } else {
            for($i=1; $i<=count($value); $i++) {
                if (!empty($_FILES['groups']['tmp_name']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'])) {
                    $_FILES['shipping_address_background_shippingmethod_row_' . $i]['name']     = $_FILES['groups']['name']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_row_' . $i]['type']     = $_FILES['groups']['type']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_row_' . $i]['tmp_name'] = $_FILES['groups']['tmp_name']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_row_' . $i]['error']    = $_FILES['groups']['error']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_row_' . $i]['size']     = $_FILES['groups']['size']['wonder_invoice']['fields']['shipping_address_background_shippingmethod']['value']['row_'.$i]['file'];

                    try {
                        $uploader = new Varien_File_Uploader('shipping_address_background_shippingmethod_row_' . $i);
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setAllowCreateFolders(true);
                        $uploader->save(Mage::getBaseDir('media').DS.'moogento/pickpack/wonder_invoice');
                        $filename = $uploader->getUploadedFileName();
                        $value['row_'.$i]['file'] = $filename;
                    } catch (Exception $e) {
                        $value['row_'.$i]['file'] = (isset($oldValue['row_'.$i]['file'])) ? $oldValue['row_'.$i]['file'] : '';
                        throw $e;
                    }

                } else {
                    $value['row_'.$i]['file'] = (isset($oldValue['row_'.$i]['file'])) ? $oldValue['row_'.$i]['file'] : '';
                }
            }
            $value = serialize($value);
        }
        $this->setValue($value);
    }
}
