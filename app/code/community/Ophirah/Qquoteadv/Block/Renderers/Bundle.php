<?php

class Ophirah_Qquoteadv_Block_Renderers_Bundle extends Ophirah_Qquoteadv_Block_Renderers_Abstract
{

    /**
     * @return bool
     */
    public function hasBundleOptions()
    {
        if ($this->getBundleOptions() && count($this->getBundleOptions())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getBundleOptions()
    {
        if ($this->_productValues instanceof Varien_Object) {
            return $this->_productValues->getBundleOptions();
        } else {
            return array();
        }
    }

    public function getPrice()
    {
        $block = $this->getLayout()->createBlock('bundle/catalog_product_price')->setProduct($this->getProduct());
        $block->setTemplate('bundle/catalog/product/view/price.phtml');

        return $block->toHtml();
    }


}