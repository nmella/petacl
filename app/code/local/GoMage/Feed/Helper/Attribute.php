<?php

/**
 * GoMage.com
 *
 * GoMage Feed Pro
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2015 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.6
 * @since        Class available since Release 3.4
 */
class GoMage_Feed_Helper_Attribute extends Mage_Core_Helper_Abstract
{

    protected $attribute_collection = null;
    protected $attribute_options = null;
    protected $output_types = null;

    public function getAttributeCollection()
    {
        if (is_null($this->attribute_collection)) {
            $this->attribute_collection = Mage::getResourceModel('eav/entity_attribute_collection')
                ->setItemObjectClass('catalog/resource_eav_attribute')
                ->setEntityTypeFilter(Mage::getResourceModel('catalog/product')->getTypeId())
                ->addFieldToFilter('attribute_code', array('nin' => array('gallery', 'media_gallery')));
        }

        return $this->attribute_collection;
    }

    public function getAttributeOptionsArray()
    {
        if (is_null($this->attribute_options)) {
            $this->attribute_options = array();

            $this->attribute_options['Product Id']             = array('code' => "entity_id", 'label' => "Product Id");
            $this->attribute_options['Is In Stock']            = array('code' => "is_in_stock", 'label' => "Is In Stock");
            $this->attribute_options['Qty']                    = array('code' => "qty", 'label' => "Qty");
            $this->attribute_options['Image']                  = array('code' => "image", 'label' => "Image");
            $this->attribute_options['URL']                    = array('code' => "url", 'label' => "URL");
            $this->attribute_options['Category']               = array('code' => "category", 'label' => "Category");
            $this->attribute_options['Final Price']            = array('code' => "final_price", 'label' => "Final Price");
            $this->attribute_options['Store Price']            = array('code' => "store_price", 'label' => "Store Price");
            $this->attribute_options['Image 2']                = array('code' => "image_2", 'label' => "Image 2");
            $this->attribute_options['Image 3']                = array('code' => "image_3", 'label' => "Image 3");
            $this->attribute_options['Image 4']                = array('code' => "image_4", 'label' => "Image 4");
            $this->attribute_options['Image 5']                = array('code' => "image_5", 'label' => "Image 5");
            $this->attribute_options['SKU Amazon']             = array('code' => "sku_amazon", 'label' => "SKU Amazon");
            $this->attribute_options['Category > SubCategory'] = array('code' => "category_subcategory", 'label' => "Category > SubCategory");

            $custom_attributes = Mage::getResourceModel('gomage_feed/custom_attribute_collection');

            foreach ($custom_attributes as $attribute) {
                $label                           = '* ' . $attribute->getName();
                $this->attribute_options[$label] = array('code' => sprintf('custom:%s', $attribute->getCode()), 'label' => $label);
            }

            foreach ($this->getAttributeCollection() as $attribute) {
                if ($attribute->getFrontendLabel()) {
                    $this->attribute_options[$attribute->getFrontendLabel()] = array('code' => $attribute->getAttributeCode(), 'label' => ($attribute->getFrontendLabel() ? $attribute->getFrontendLabel() : $attribute->getAttributeCode()));
                }
            }

            ksort($this->attribute_options);

        }

        return $this->attribute_options;

    }

    public function getAttributeSelect($i, $current = null, $active = true)
    {
        $options   = array();
        $options[] = "<option value=''>Not Set</option>";
        foreach ($this->getAttributeOptionsArray() as $attribute) {
            extract($attribute);
            $selected = '';
            if ($code == $current) {
                $selected = 'selected="selected"';
            }
            $options[] = "<option value=\"{$code}\" {$selected}>{$label}</option>";
        }
        return '<select style="width:260px;display:' . ($active ? 'block' : 'none') . '" id="mapping-' . $i . '-attribute-value" name="field[' . $i . '][attribute_value]">' . implode('', $options) . '</select>';
    }

    public function getOutputTypes()
    {
        if (is_null($this->output_types)) {
            $this->output_types = array(
                array('code' => '', 'label' => $this->__('Default')),
                array('code' => 'int', 'label' => $this->__('Integer')),
                array('code' => 'float', 'label' => $this->__('Float')),
                array('code' => 'striptags', 'label' => $this->__('Striptags')),
                array('code' => 'htmlspecialchars', 'label' => $this->__('Encode special chars')),
                array('code' => 'htmlspecialchars_decode', 'label' => $this->__('Decode special chars')),
                array('code' => 'delete_space', 'label' => $this->__('Delete Space')),
                array('code' => 'big_to_small', 'label' => $this->__('Big to small')),
            );
        }

        return $this->output_types;
    }

    public function getOutputTypeSelect($i, $values = '')
    {
        $values   = explode(',', $values);
        $multiple = (count($values) > 1 ? 'multiple="multiple"' : '');
        $options  = array();

        foreach ($this->getOutputTypes() as $output_type) {
            extract($output_type);
            $selected = '';
            if (in_array($code, $values)) {
                $selected = 'selected="selected"';
            }
            $options[] = "<option value=\"{$code}\" {$selected}>{$label}</option>";
        }

        $select_id = 'field_' . $i . '_output_type';

        return '<select ' . $multiple . ' id="' . $select_id . '" name="field[' . $i . '][output_type][]">' . implode('', $options) . '</select><a class="gfp-toggle-multi" href="javascript:void(0)" onclick="gfp_toggle_multi(this, \'' . $select_id . '\')">' . (count($values) > 1 ? '-' : '+') . '</a>';
    }

}
