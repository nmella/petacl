<?php

class Ophirah_Qquoteadv_Block_Renderers_Configurable extends Ophirah_Qquoteadv_Block_Renderers_Abstract
{

    /**
     * @return bool
     */
    public function hasAttributeInfo()
    {
        if ($this->getAttributeInfo() && count($this->getAttributeInfo())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getAttributeInfo()
    {
        if ($this->_productValues instanceof Varien_Object) {
            return $this->_productValues->getAttributesInfo();
        } else {
            return array();
        }
    }

}