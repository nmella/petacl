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
* File        Pack.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Adminhtml_System_Config_Backend_Field_Shippingbackground_Pack
    extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave() {

        $value = $this->getValue();
        if ($post = Mage::app()->getRequest()->getParam('groups')) {
            if (isset($post['wonder']['fields']['shipping_address_background_shippingmethod']['value'])) {
                $value = $post['wonder']['fields']['shipping_address_background_shippingmethod']['value'];
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
                if (!empty($_FILES['groups']['tmp_name']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'])) {
                    $_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['name']     = $_FILES['groups']['name']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['type']     = $_FILES['groups']['type']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['tmp_name'] = $_FILES['groups']['tmp_name']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['error']    = $_FILES['groups']['error']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'];
                    $_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['size']     = $_FILES['groups']['size']['wonder']['fields']['shipping_address_background_shippingmethod']['value']['pack_row_'.$i]['file'];

					if($_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['size'] > 500000)
					{
						
						$error_message = "Error in 'Shipping Address Background Images' : The image filesize in the '".$value['pack_row_'.$i]['name']."' row is too large. Please use an image less than 500kb in size. There are correctly-sized image templates in the extension download folder.";

                        Mage::log($error_message.' Filesize: '.$_FILES['shipping_address_background_shippingmethod_pack_row_' . $i]['size']);
                        Mage::getSingleton('adminhtml/session')->addError($error_message);
						continue;
					}
                    try {
                        $uploader = new Varien_File_Uploader('shipping_address_background_shippingmethod_pack_row_' . $i);
                        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(true);
                        $uploader->setAllowCreateFolders(true);
                        $uploader->save(Mage::getBaseDir('media').DS.'moogento/pickpack/wonder');
                        $filename = $uploader->getUploadedFileName();
                        $value['pack_row_'.$i]['file'] = $filename;
                    } catch (Exception $e) {
                        $value['pack_row_'.$i]['file'] = (isset($oldValue['pack_row_'.$i]['file'])) ? $oldValue['pack_row_'.$i]['file'] : '';
                        throw $e;
                    }

                } else {
                    $value['pack_row_'.$i]['file'] = (isset($oldValue['pack_row_'.$i]['file'])) ? $oldValue['pack_row_'.$i]['file'] : '';
                }
            }
            $value = serialize($value);
        }
        $this->setValue($value);
    }
}
