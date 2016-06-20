<?php /**
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
 * File        Combined.php
 * @category   Moogento
 * @package    pickPack
 * @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
 * @license    https://moogento.com/License.html
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Cn22 extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
	protected $page_top = 0;
	protected $padded_right = 0;	
    public function nooPage($rotate_document = 0,$page_size = '',$store_id=0) {
    	 if (!$page_size || $page_size == '')
            $page_size = $this->_getConfig('page_size', 'a4', false, 'general');
            
        if ($page_size == 'letter') {
            $settings['page_size'] = Zend_Pdf_Page::SIZE_LETTER;
            $width_pt = 612;
            $height_pt = 792;
            $this->page_top              = 770;
            $this->padded_right          = 587;
        } else if ($page_size == 'a4') {
            $width_pt = 595;
            $height_pt = 842;
            $this->page_top              = 820;
            $this->padded_right          = 570;
        }
        elseif ($page_size == 'a5-landscape') {
            $width_pt = 596;
            $height_pt = 421;
            $this->page_top              = 395;
            $this->padded_right          = 573;
        } elseif ($page_size == 'a5-portrait') {
            $settings['page_size'] = '421:596';
             $width_pt = 421;
            $height_pt = 596;
            $this->page_top              = 573;
            $this->padded_right          = 395;
        }
        else if ($page_size == 'custom') {
        	$demension = explode(",", $this->_getConfig('page_size_custom', '279,245', false,'cn22_label',$store_id,true,'cn22_options'));        
        	$width_pt = $demension[0];
        	$height_pt = $demension[1];    
        	$this->page_top              = $height_pt -10;
            $this->padded_right          = $width_pt-10;    	
        }

//1 px =  	 0.75 point
// $width_px = 279;
//     	$height_px = 245;
//     	
//     	$width_pt = $width_px*0.75;
//     	$height_pt = $height_px*0.75;
    	$size_1 =$width_pt.':'.$height_pt;
    	$size_2 =$height_pt.':'.$width_pt;
    	
    	if($rotate_document == 1)
			$page                                   = $this->_getPdf()->newPage($size_2);        
		else
	        $page                                   = $this->_getPdf()->newPage($size_1);        
        $this->_getPdf()->pages[] = $page;        
        return $page;
    }
    
    private function getRotateReturnAddress($rotate_return_address) {
        
        switch ($rotate_return_address) {
            case 0:
                $rotate = 0;
                break;
            case 1:
                $rotate = 3.14 / 2;
                break;
            case 2:
                $rotate = -3.14 / 2;
                break;
            default:
                $rotate = 0;
        }
        return $rotate;
    }
    
    private function rotateLabel($case_rotate,&$page,$page_top,$padded_right,$nudge_rotate_address_label) {
    	// X nudge --- Y nudge
// 		1. Move top: 
// 		  Increase Y 50px and Decrease X 50px
// 		2. Move bottom: 
// 		  Decrease Y 50px and Increase X 50px
// 		3. Move left: 
// 		  Decrease X 50px and Decrease Y 50px
// 		4. Move right: 
// 		  Increase X 50px and Increase Y 50px
    	//Move all to bototm 100px
    	$x = -30;
    	$y = -55;
    	if($nudge_rotate_address_label[0] > 0 )
    	{
			//Move right
			$x += abs($nudge_rotate_address_label[0]);
			$y += abs($nudge_rotate_address_label[0]);   	
    	}
    	else
			if($nudge_rotate_address_label[0] < 0 )
			{
				//Move left
				$x -= abs($nudge_rotate_address_label[0]);
				$y -= abs($nudge_rotate_address_label[0]);   	
			}
		
		if($nudge_rotate_address_label[1] > 0 )
    	{
			//Move top
			$x += abs($nudge_rotate_address_label[1]);
			$y -= abs($nudge_rotate_address_label[1]);   	
    	}
    	else
			if($nudge_rotate_address_label[1] < 0 )
			{
				//Move bottom
				$x -= abs($nudge_rotate_address_label[1]);
				$y += abs($nudge_rotate_address_label[1]);   	
			}
		$nudge_rotate_address_label[0] = $x;
		$nudge_rotate_address_label[1] = $y;

        switch ($case_rotate) {
            case 1:
                // //TODO Moo rotate 90
                    $rotate = 3.14 / 2;
                    break;
            case 2:
               //TODO Moo rotate 270
                    $rotate = -3.14 / 2;
                    break;
        }
        $page->rotate($page_top/2+$nudge_rotate_address_label[0],$padded_right/2 +$nudge_rotate_address_label[1], $rotate);
    }
    
    public function getPdf($orders = array(),$from_shipment = 'order',$product_filter = array(),$from ='') {
    	
    	$helper = Mage::helper('pickpack');
		$magentoVersion = Mage::getVersion();
        $this->_logo_maxdimensions = explode(',', '269,41');
        //TODO 1: Get and set general configuration values
        $store_id = Mage::app()->getStore()->getId();
        
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        //TODO get configuration
        $page_size = $this->_getConfig('page_size', 'a4', false,'cn22_label', $store_id,true,'cn22_options');   
        $case_rotate = $this->_getConfig('case_rotate_address_label',0, false, 'cn22_label', $store_id,true,'cn22_options');
		$nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','20,0', false,'cn22_label', $store_id,true,'cn22_options'));
		if ($page_size == 'letter') {
          $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','20,0', false,'cn22_label', $store_id,true,'cn22_options'));
        } else if ($page_size == 'a4') {
           $nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','120,-80', false,'cn22_label', $store_id,true,'cn22_options'));
        }else if ($page_size == 'custom') {
        	$nudge_rotate_address_label = explode(',',$this->_getConfig('nudge_rotate_address_label','20,0', false,'cn22_label', $store_id,true,'cn22_options'));
        }
		$nudge_rotate_address_label[0] = 120;
		$nudge_rotate_address_label[1] = -210;		

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style     = new Zend_Pdf_Style();
        
        $label_width = $this->_getConfig('label_width', '279', false,'cn22_label', $store_id,true,'cn22_options');        
        $label_height = $this->_getConfig('label_height', '245', false,'cn22_label', $store_id,true,'cn22_options');                
        $nudge_cn22_label = explode(',',$this->_getConfig('nudge_cn22_label','20,245', false,'cn22_label', $store_id,true,'cn22_options'));             
        $this->_printing_format['padded_left'] = 20;
        $page_count = 1;
        $item_qty_array = array();
        $orderCollection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' =>($orders)))->setPageSize(count($orders))
            ->setCurPage(1);
            $now = Mage::getModel('core/date')->timestamp(time());  
            $time =  date('Y - m - d', $now); 
        foreach ($orderCollection as $order) {
            $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
            foreach ($itemsCollection as $itemId => $item) {
				$item_qty_array[$item->getData('product_id')] = $item->getData('qty_ordered');
            	$children_items = $item->getChildrenItems();
                if($children_items > 0)
                {
                    foreach($children_items as $child)
                    {
                        $item_qty_array[$child->getData('product_id')] = $child->getData('qty_ordered');
                    }
                }
            }
        }
        
        
        $rotate_document = $this->_getConfig('rotate_document',0, false, 'cn22_label', $store_id,true,'cn22_options');
		$customs_section_model = new Moogento_Cn22_Model_Pdf();
		
		//TODO get value from config
		$numger_of_label_on_sheet = $this->_getConfig('number_of_labels_on_sheet',1, false, 'cn22_label', $store_id,true,'cn22_options');
		$number_printed = 0;
		$print_first_page = 0;
		$current_label_xpos = $nudge_cn22_label[0];
		$current_label_ypos = $this->page_top-$nudge_cn22_label[1];
		$label_padding          = explode(",", $this->_getConfig('label_padding', '5,5,5,5', false, 'cn22_label', $store_id,true,'cn22_options'));
		$label_nudge_top =$label_padding[0];
		$label_nudge_right =$label_padding[1];
		$label_nudge_bottom =$label_padding[2];;		
		$label_nudge_left =$label_padding[3];;		
		$first_page =0;
		$print_column_count =1;
		$need_new_page = 1;
         foreach ($orderCollection as $order) {
			 if(($number_printed >= $numger_of_label_on_sheet) || ($need_new_page == 1))
			 {
			 	
				$page = $this->nooPage($rotate_document,$page_size,$store_id);
				$page_count++;
				$this->pagecount++;
				$number_printed = 0;	
				$need_new_page = 0;
				$print_column_count =1;
				$current_label_xpos = $nudge_cn22_label[0];
				$current_label_ypos = $this->page_top-$nudge_cn22_label[1];	

				if($case_rotate > 0)
					$this->rotateLabel($case_rotate,$page,$this->page_top,$this->padded_right,$nudge_rotate_address_label);
			 } 

			try{
				$customs_section_model->printCustomsSection(0,$page,$order,'cn22',$current_label_xpos,$current_label_ypos,$item_qty_array,$label_width,$label_height);
				
				if(($print_column_count % 2) ==1)
					$current_label_xpos += $label_width+$label_nudge_right+$label_nudge_left;
				else
				{
					$current_label_xpos = $nudge_cn22_label[0];
					$current_label_ypos -= $label_height+$label_nudge_bottom+$label_nudge_top;
				}
				$print_column_count++;
				if($case_rotate == 0)
				{
					if($current_label_ypos < $label_height)
					{
						$need_new_page = 1;
					}
					else
						if($current_label_xpos + $label_width > $this->padded_right)
						{
							$need_new_page = 1;
						}
				}
				else 
					if($this->page_top - $current_label_ypos +$label_height > $this->padded_right)
					{
						$need_new_page = 1;
					}
					else
						if($current_label_xpos + $label_width > $this->page_top)
						{
							$need_new_page = 1;
						}
						
				
			}
			catch(Exception $e)
			{
				echo $e->getMessage(); exit;
			}
			
			$number_printed++;	
		}
        $this->_afterGetPdf();
        return $pdf;
    }

    /*
     * need page info
     * all config will get to one obj
     * */
    public function printOneLabelCN22($page,$order,$store_id, $nudgeX = 0, $nudgeY = 0){
        //get config
        $label_width = $this->_getConfig('label_width', '279', false,'cn22_label', $store_id,true,'cn22_options');
        $label_height = $this->_getConfig('label_height', '245', false,'cn22_label', $store_id,true,'cn22_options');

        //need create new page

        /** get list product to print from $order */
        $itemsCollection = Mage::helper('pickpack/order')->getItemsToProcess($order);
        foreach ($itemsCollection as $itemId => $item) {
            $item_qty_array[$item->getData('product_id')] = $item->getData('qty_ordered');
            $children_items = $item->getChildrenItems();
            if($children_items > 0)
            {
                foreach($children_items as $child)
                {
                    $item_qty_array[$child->getData('product_id')] = $child->getData('qty_ordered');
                }
            }
        }

        $pageHeight = $page->getHeight();
        try{
            $customs_section_model = new Moogento_Cn22_Model_Pdf();
            //$customs_section_model->printCustomsSection(0,$page,$order,'cn22',$current_label_xpos,$current_label_ypos,$item_qty_array,$label_width,$label_height);
            $customs_section_model->printCustomsSection(0,$page,$order,'cn22',$nudgeX,$pageHeight - $nudgeY,$item_qty_array,$label_width,$label_height);
        }
        catch(Exception $e)
        {
            echo $e->getMessage(); exit;
        }
        //need create new page
    }
}