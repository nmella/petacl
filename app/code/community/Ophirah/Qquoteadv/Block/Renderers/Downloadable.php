<?php

class Ophirah_Qquoteadv_Block_Renderers_Downloadable extends Ophirah_Qquoteadv_Block_Renderers_Abstract
{

    /**
     * @return bool
     */
    public function hasLinks()
    {
        return (bool)count($this->getLinks());
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        if (!$this->hasErrors()) {
            $links = Mage::helper('downloadable/catalog_product_configuration')->getLinks($this->_addProductResult);
        } else {
            $links = array();
        }
        return $links;
    }


}