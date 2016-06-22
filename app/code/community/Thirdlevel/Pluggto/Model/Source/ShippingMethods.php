<?php


class Thirdlevel_Pluggto_Model_Source_ShippingMethods {

    public function toOptionArray() {

        try{
            $methods = Mage::getSingleton('shipping/config')->getAllCarriers();
           // $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();


            $shipping =  array(array('carrier'=>'', 'title'=>Mage::helper('adminhtml')->__('--Please Select--')));
            foreach ($methods as $_ccode => $_carrier) {

                if ($_methods = $_carrier->getAllowedMethods()) {

                    if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                        $_title = $_ccode;
                    foreach ($_methods as $_mcode => $_method) {

                        if($_ccode != $_mcode){
                            $_code = $_ccode . '_' . $_mcode;
                        } else {
                            $_code = $_ccode;
                        }
                        $shipping[$_code] = array('title' => $_method, 'carrier' => $_title);
                    }
                }
            }




            foreach($shipping as $v => $id){
                $cur[] = array('value' => $v, 'label'=>Mage::helper('adminhtml')->__($id['title']));
            }

            return $cur;

        } catch (exception $e){

            $cur[] = array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Impossible to retrive store shipping methods'));
            return $cur;
        }
    }

}