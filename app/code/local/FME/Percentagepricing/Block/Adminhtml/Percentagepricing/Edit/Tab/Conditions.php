<?php
/**
 * Add Product to CMS extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Percentage Pricing
 * @author     Qazi Mustafa <qazi.mustafa@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */
class FME_Percentagepricing_Block_Adminhtml_Percentagepricing_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form 
{
		protected function _prepareForm()
		{
				$cms_id = $this->getRequest()->getParam('id');
				$model = Mage::getModel('percentagepricing/percentagepricing')->load($cms_id);

				$form = new Varien_Data_Form();
				$helper = Mage::helper('percentagepricing'); 
  
				$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
								->setTemplate('promo/fieldset.phtml')
								->setNewChildUrl($this->getUrl('*/*/newConditionHtml', array(
								'form' => 'css_conditions_fieldset',
								'prefix' => 'css', 
								'rule' => base64_encode('percentagepricing/percentagepricing_product_rulecss'))));
   
				$fieldset = $form->addFieldset('css_conditions_fieldset', array(
								//'legend' => $this->__('Conditions (leave blank for all products)')
								'legend' => $this->__('Conditions')
								))->setRenderer($renderer);
   
				$rule = Mage::getModel('percentagepricing/percentagepricing_product_rulecss');
				$rule->getConditions()->setJsFormObject('css_conditions_fieldset');
				$rule->getConditions()->setId('css_conditions_fieldset');
  
				$rule->setForm($fieldset);
				if ($model->getData('percentagepricing_rule') && is_array($model->getData('percentagepricing_rule')->getData('conditions')))
				{
						$conditions = $model->getData('percentagepricing_rule')->getData('conditions');
						$rule->getConditions()->loadArray($conditions, 'css');
						$rule->getConditions()->setJsFormObject('css_conditions_fieldset');
				}

				$fieldset->addField('css_conditions', 'text', array(
						'name' => 'css_conditions',
						'label' => $this->__('Apply To'),
						'title' => $this->__('Apply To'),
						'required' => true,
						))->setRule($rule)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
   
				$form->setValues($model->getData());
				
				$this->setForm($form);
 
				return parent::_prepareForm();
    }
}