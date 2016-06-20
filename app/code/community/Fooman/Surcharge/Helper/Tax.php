<?php

class Fooman_Surcharge_Helper_Tax extends Mage_Tax_Helper_Data
{
    /**
     * Magento disconnects applied taxes and sales_order_tax_item
     * re-add surcharge tax here when a breakdown of taxes is required
     *
     * @param $source
     *
     * @return array
     */
    public function getCalculatedTaxes($source)
    {

        $taxesItemsOnly = parent::getCalculatedTaxes($source);
        if ($source->getFoomanSurchargeTaxAmount() != 0
            && ($this->_getFromRegistry('current_invoice')
                || $this->_getFromRegistry('current_creditmemo'))
        ) {
            $totalTax = 0;
            foreach ($taxesItemsOnly as $taxItems) {
                $totalTax += $taxItems['tax_amount'];
            }

            //the surcharge tax is not covered as part of existing tax rates
            if ($totalTax < $source->getTaxAmount()) {
                $taxesItemsOnly[] = array(
                    'tax_amount'      => $source->getFoomanSurchargeTaxAmount(),
                    'base_tax_amount' => $source->getBaseFoomanSurchargeTaxAmount(),
                    'title'           => $this->__('Surcharge Tax'),
                    'percent'         => null
                );
            }
        }

        return $taxesItemsOnly;
    }
}
