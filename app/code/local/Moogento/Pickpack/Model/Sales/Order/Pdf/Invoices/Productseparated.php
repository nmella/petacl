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

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Productseparated extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices
{
    protected $generalConfig = array();
    protected $_printing_format = array();
    protected $_product_config = array();
    protected $_order_config = array();
    protected $_helper = '';

    protected $_orderCollection = array();
    protected $_itemsCollection = array();
    protected $_productsCollection = array();

    protected $_totalItemsPerOrder = array();   

    protected $store_list = array();
    protected $product_list = '';   
    protected $product_list_arr = array();
    protected $product_list_arr_per_store = array();

    //Total ordered of each product
    protected $product_ordered = array();
    //Number of ordered of each product per order
    protected $product_ordered_per_order = array();

    protected $order_list_of_product = array();
    protected $order_list_of_product_per_option = array(); 

    protected $order_model_list = array();

    protected $product_model_list_arr = array();
    protected $product_order_list_arr = array();    
    protected $item_model_list_arr = array();
    protected $product_name_from_item = array();
    protected $item_model_for_product = array();

    protected $pre_print_time = 0;
    protected $next_print_time = 0;
    protected $max_print_time = 0;
    protected $runtime = 0;
    protected $pagecount =0;
    protected $_logo_maxdimensions = array();
    protected $_columns_xpos_array = array();
    protected $_columns_xpos_array_order = array();

    public function getGeneralConfig($storeId) {
        return Mage::helper('pickpack/config')->getGeneralConfigArray($storeId);
    }
    
     public function updateData($order_arrs,$product_filter = array()) {
        $this->_orderCollection = Mage::getModel('sales/order')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' =>($order_arrs)))->setPageSize(count($order_arrs))//count($orders)); 1000
            ->setCurPage(1);
            $now = Mage::getModel('core/date')->timestamp(time());  
    
            $time =  date('Y - m - d', $now); 
        foreach ($this->_orderCollection as $order) {
            $_orderId = $order->getId();
            $this->order_model_list[$_orderId] = $order;
            $storeId  = $order->getStore()->getId(); 
            if(isset($this->store_list[$storeId]))
                $this->store_list[$storeId] .= ','.$_orderId;
            else
                $this->store_list[$storeId] = $_orderId;
            $order_incre_id = $order->getData('increment_id');          
            
            $this->_itemsCollection[$_orderId] = Mage::helper('pickpack/order')->getItemsToProcess($order, $this->_printing_format['split_bundle_product'] == 1);
            $total_item_count = 0;
            $product_list_order = '';
            $filter_shipped_item_yn =  $this->_getConfig('filter_shipped_item_yn',0, false, 'product_separated', $storeId);
            foreach ($this->_itemsCollection[$_orderId] as $item) {            
                if($filter_shipped_item_yn == 1)
                {
                    if(($item->getProductType() == 'simple') || 
                    ($item->getProductType() == 'bundle') || 
                    ($item->getProductType() == 'configurable')|| 
                    ($item->getProductType() == 'configurable'))
                        $qty_calculate_product = $item->getQtyOrdered() - $item->getQtyShipped();               
                    else
                        $qty_calculate_product = $item->getQtyOrdered() - $item->getQtyInvoiced();              
                }
                else
                    $qty_calculate_product = $item->getQtyOrdered();
                if($qty_calculate_product == 0)
                    continue;

                if($filter_shipped_item_yn == 1)
                    if(isset($product_filter['type']) && isset($product_filter['type']) && isset($product_filter['type']))
                        {
                    if(($product_filter['type'] == 'product_attribute'))
                    {
                        $code = $product_filter['code'];
                        $value = strtolower(trim($product_filter['value']));
                        $item_attribute_value = strtolower(trim($item->getData($code)));
                        if(strpos($item_attribute_value,$value) === FALSE)
                        {
                            continue;
                        }
                    }
                    else
                        if(($product_filter['type'] == 'yes_current_date'))
                        {
                            $code = $product_filter['code'];
                            $item_date = $item->getData($code); 
                            if(strtotime($item_date) === strtotime('today'))
                            {
                                $log_message = 'item ID: '.$item->getData('item_id').' -- orderID: '.$order_incre_id.' created at: '.$item->getData('created_at').' custom date: '.$item->getData($code); 
//                              Mage::log($log_message, null, 'moogento_pickpack.log');
                            }
                            else
                                continue;
                        }
                }               
                
                if($this->_printing_format['split_bundle_product'] == 1)
                {
                    $total_item_count += $item->getIsQtyDecimal() ? $qty_calculate_product : (int)$qty_calculate_product;
                    if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE && $item->getHasChildren()) {
                        $children = $item->getChildrenItems();
                        $child = $children[0];
                        if ($child) {
                            $productId = $child->getProductId();
                            $this->product_name_from_item[$productId] = $child->getName();
                            $this->item_model_for_product[$productId] = $child;
                        } else {
                            $productId = $item->getProductId();
                            $this->product_name_from_item[$productId] = $item->getName();
                            $this->item_model_for_product[$productId] = $item;
                        }
                    } 
                    else {
                        $productId = $item->getProductId();
                        $this->product_name_from_item[$productId] = $item->getName();
                        $this->item_model_for_product[$productId] = $item;
                    }
                }
                else
                {
                    $productId = $item->getProductId();
                    $this->product_name_from_item[$productId] = $item->getName();
                    $this->item_model_for_product[$productId] = $item;
                }
                    

                //TODO new
                if(isset($this->product_list_arr_per_store[$productId]))
                    $this->product_list_arr_per_store[$productId] .= ','.$storeId;
                else
                    $this->product_list_arr_per_store[$productId] = $storeId;
                //End new
                //Product list per store
                if(isset($this->store_list['product'][$storeId]) && (strlen($this->store_list['product'][$storeId]) >0))
                {
                    $this->store_list['product'][$storeId] .= ','.$productId;
                    $product_list_order .= ','.$productId;
                }
                else
                {
                    $this->store_list['product'][$storeId] = $productId;
                    $product_list_order = $productId;                
                }

                //total product ordered
                if(!(isset($this->product_ordered[$productId])))
                    $this->product_ordered[$productId] = $qty_calculate_product;
                else
                    $this->product_ordered[$productId] += $qty_calculate_product;

                //total product ordered per order
                if(!(isset($this->product_ordered_per_order[$productId][$_orderId])))
                    $this->product_ordered_per_order[$productId][$_orderId] = $qty_calculate_product;
                else
                    $this->product_ordered_per_order[$productId][$_orderId] += $qty_calculate_product;

                //list orders of each product
                if(!(isset($this->order_list_of_product[$productId][$_orderId])))
                    $this->order_list_of_product[$productId][$_orderId] = $_orderId;


                //Product list, use to get product collection
                if(strlen($this->product_list) > 0)
                    $this->product_list.= ','.$productId;
                else
                    $this->product_list= $productId;

                //Process for product options
                $options_pre = array();
                $options = array();
                $options_pre = $item->getProductOptions();
                if (isset($options_pre['info_buyRequest']) && is_array($options_pre['info_buyRequest'])) {
                    unset($options_pre['info_buyRequest']['uenc']);
                    unset($options_pre['info_buyRequest']['form_key']);
                    unset($options_pre['info_buyRequest']['related_product']);
                    unset($options_pre['info_buyRequest']['return_url']);
                    unset($options_pre['info_buyRequest']['qty']);
                    unset($options_pre['info_buyRequest']['_antispam']);
                    unset($options_pre['info_buyRequest']['super_attribute']);
                    unset($options_pre['info_buyRequest']['cpid']);
                    unset($options_pre['info_buyRequest']['callback']);
                    unset($options_pre['info_buyRequest']['isAjax']);
                    unset($options_pre['info_buyRequest']['item']);
                    unset($options_pre['info_buyRequest']['original_qty']);
                    unset($options_pre['info_buyRequest']['bundle_option']);
                    if (isset($options_pre['options']) && is_array($options_pre['options']))
                        $options['options'] = $options_pre['options'];

                } else $options = $options_pre;

                if (isset($options_pre['bundle_options']) && is_array($options_pre['bundle_options'])) {
                    $options['bundle_options'] = $options_pre['bundle_options'];
                }
                if (!(isset($options['options'])) || count($options['options']) == 0)
                    if (isset($options_pre['attributes_info']) && is_array($options_pre['attributes_info']))
                        $options['options'] = $options_pre['attributes_info'];
                unset($options_pre);
                $custom_options_output = '';
                $current_option = '';
                if (isset($options['options']) && is_array($options['options'])) {
                    $i = 0;
                    if (isset($options['options'][$i])) $continue = 1;
                    else $continue = 0;

                    while ($continue == 1) {
                        if (trim($options['options'][$i]['label'] . $options['options'][$i]['value']) != '') {
                            if ($i > 0) 
                                $custom_options_output .= ' ';
                            $custom_options_output .= htmlspecialchars_decode('[ ' . $options['options'][$i]['label'] . ' : ' . $options['options'][$i]['value'] . ' ]');
                            $current_option .= '['.$options['options'][$i]['label'].':'.$options['options'][$i]['value'].']';
                    }
                    $i++;
                    if (isset($options['options'][$i])) $continue = 1;
                    else $continue = 0;
                }
                $custom_options_title = '';
                $product_id_option = strip_tags($custom_options_title . $custom_options_output);
                if(!(isset($this->order_list_of_product_per_option[$productId][$current_option])))
                {
                    $this->order_list_of_product_per_option[$productId][$current_option] = $_orderId;
                }
                else
                    $this->order_list_of_product_per_option[$productId][$current_option] .= ','.$_orderId; 
            }
                //End process for product options.
            } 
            $this->_totalItemsPerOrder[$_orderId] = $total_item_count;
            $product_list_order = ltrim($product_list_order,',');
            $this->store_list['order'][$_orderId] = $product_list_order;
            unset($product_list_order);
        }
        $this->product_list_arr = explode(',', $this->product_list);
        $this->product_list_arr = array_unique($this->product_list_arr);
    }
    
    public function getPdf($orders = array(),$from_shipment = 'order',$product_filter = array(),$from ='') {

        $this->_logo_maxdimensions = explode(',', '269,41');
        //TODO 1: Get and set general configuration values
        $store_id = Mage::app()->getStore()->getId();
        
        $this->_beforeGetPdf();
        $this->_initRenderer('invoices');
        
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style     = new Zend_Pdf_Style();
		$this->generalConfig = $this->getGeneralConfig(0);
        $page_size = $this->generalConfig['page_size'];
		
        $this->_printing_format['padded_left'] = 20;
        
        if ($page_size == 'letter') {
            $page                                   = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $this->_printing_format['page_top']     = 770;
            $this->_printing_format['padded_right'] = 587;
        } elseif ($page_size == 'a4') {
            $page                                   = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $this->_printing_format['page_top']     = 820;
            $this->_printing_format['padded_right'] = 570;
        } elseif ($page_size == 'a5-landscape') {
            $page                                   = $pdf->newPage('596:421');
            $this->_printing_format['page_top']     = 395;
            $this->_printing_format['padded_right'] = 573;
        } elseif ($page_size == 'a5-portrait') {
            $page                                   = $pdf->newPage('421:596');
            $this->_printing_format['page_top']     = 573;
            $this->_printing_format['padded_right'] = 395;
        }

        $this->pagecount++;
//         Mage::log(memory_get_usage(),'null','moogento_pickpack.log');
        
        $pdf->pages[] = $page;
        $config_group = 'product_separated';
        $this->generalConfig['font_size_overall']    = 15;
        $this->generalConfig['font_size_productline'] = 9;
        
        $this->_printing_format['red_bkg_color']           = new Zend_Pdf_Color_Html('lightCoral');
        $this->_printing_format['grey_bkg_color']           = new Zend_Pdf_Color_GrayScale(0.85);
        $this->_printing_format['alternate_row_color_temp'] = $this->_getConfig('alternate_row_color', '#DDDDDD', false, $config_group);
        $this->_printing_format['alternate_row_color']      = new Zend_Pdf_Color_Html($this->_printing_format['alternate_row_color_temp']);
        $this->_printing_format['dk_grey_bkg_color']        = new Zend_Pdf_Color_GrayScale(0.3);
        $this->_printing_format['dk_cyan_bkg_color']        = new Zend_Pdf_Color_Html('darkCyan');
        $this->_printing_format['dk_og_bkg_color']          = new Zend_Pdf_Color_Html('darkOliveGreen');
        $this->_printing_format['white_bkg_color']          = new Zend_Pdf_Color_Html('white');
        $this->_printing_format['orange_bkg_color']         = new Zend_Pdf_Color_Html('Orange');
        $this->_printing_format['black_color']              = new Zend_Pdf_Color_Rgb(0, 0, 0);
        $this->_printing_format['grey_color']               = new Zend_Pdf_Color_GrayScale(0.3);
        $this->_printing_format['greyout_color']            = new Zend_Pdf_Color_GrayScale(0.6);
        $this->_printing_format['white_color']              = new Zend_Pdf_Color_GrayScale(1);
        /*
        
                $generalConfig['font_family_header_default']         = 'helvetica';
                $generalConfig['font_size_header_default']           = 16;
                $generalConfig['font_style_header_default']          = 'bolditalic';
                $generalConfig['font_color_header_default']          = 'darkOliveGreen';
                $generalConfig['font_family_subtitles_default']      = 'helvetica';
                $generalConfig['font_style_subtitles_default']       = 'bold';
                $generalConfig['font_size_subtitles_default']        = 15;
                $generalConfig['font_color_subtitles_default']       = '#222222';
                $this->_printing_format['background_color_subtitles_default'] = '#999999';
                $generalConfig['font_family_body_default']           = 'helvetica';
                $generalConfig['font_size_body_default']             = 10;
                $generalConfig['font_style_body_default']            = 'regular';
                $generalConfig['font_color_body_default']            = 'Black';
                */
        
        // $generalConfig['font_family_header'] = $generalConfig['font_family_header'];
        // $generalConfig['font_style_header']  = $generalConfig['font_style_header'];
        // $generalConfig['font_size_header']   = $generalConfig['font_size_header'];
        // $generalConfig['font_color_header']  = $generalConfig['font_color_header'];
        //
        // $generalConfig['font_family_body'] = $generalConfig['font_family_body'];
        // $generalConfig['font_style_body']  = $generalConfig['font_style_body'];
        // $generalConfig['font_size_body']   = $generalConfig['font_size_body'];
        // $generalConfig['font_color_body']  = $generalConfig['font_color_body'];
        //
        // $generalConfig['font_family_subtitles']           = $generalConfig['font_family_subtitles'];
        // $generalConfig['font_style_subtitles']            = $generalConfig['font_style_subtitles'];
        // $generalConfig['font_size_subtitles']             = $generalConfig['font_size_subtitles'];
        // $generalConfig['font_color_subtitles']            = $generalConfig['font_color_subtitles'];
        $this->_printing_format['background_color_subtitles_zend'] = new Zend_Pdf_Color_Html($this->generalConfig['background_color_subtitles']);
        
        $this->_printing_format['font_color_header_zend']    = new Zend_Pdf_Color_Html($this->generalConfig['font_color_header']);
        $this->_printing_format['font_color_subtitles_zend'] = new Zend_Pdf_Color_Html($this->generalConfig['font_color_subtitles']);
        // $this->_printing_format['font_color_body_zend']      = new Zend_Pdf_Color_Html($this->generalConfig['font_color_body']);
        
        $this->_printing_format['round_number'] = 0;
        $this->_printing_format['split_bundle_product'] = 1;
        switch ($this->generalConfig['barcode_type']) {
            case 'code128':
                $font_family_barcode = 'Code128bWin.ttf';
                break;
            
            case 'code39':
                $font_family_barcode = 'CODE39.ttf';
                
                break;
            
            case 'code39x':
                $font_family_barcode = 'CODE39X.ttf';
                
                break;
            
            default:
                $font_family_barcode = 'Code128bWin.ttf';
                break;
        }
        
        $this->_printing_format['date_format'] = $this->_getConfig('date_format', 'M. j, Y', false, 'general');
        $this->_printing_format['page_title'] = $this->_getConfig('pickpack_title_separated', 'Product Separated', false, 'product_separated', $store_id);
//      TODO config heaer logo
//      $this->_printing_format['page_logo'] = $this->_getConfig('product_separated_packlogo', 'Product Separated', false, 'product_separated', $store_id);
//      $show_top_logo_yn = $this->_printing_format['page_logo'];
//      TODO 2: Get and set configuration values for order
        $column_arr = array();
        $column_arr['show']  = $this->_getConfig('show_order_id',1, false, 'product_separated', $store_id);;
        $column_arr['label'] = $this->_getConfig('order_id_title','Orderid', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('order_id_Xpos',25, false, 'product_separated', $store_id);
        $column_arr['code']  = 'order_id';
        $this->_order_config['id'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('show_customer_name',0, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('customer_name_title',"Name", false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('customer_name_Xpos',100, false, 'product_separated', $store_id);
        $column_arr['code']  = 'customer_name';
        $this->_order_config['customer_name'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('show_order_date',0, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('order_date_title','Order time', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('order_date_Xpos',300, false, 'product_separated', $store_id);
        $column_arr['code']  = 'created_at';      
        $this->_order_config['created_at'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('show_customer_email',0, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('customer_email_title','Email', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('customer_email_Xpos',380, false, 'product_separated', $store_id);
        $column_arr['code']  = 'customer_email';
        $this->_order_config['email'] = $column_arr; 
        
        $this->_getConfig('show_customer_phone',0, false, 'product_separated', $store_id);
        $this->_getConfig('customer_phone_title',0, false, 'product_separated', $store_id);
        $this->_getConfig('customer_phone_Xpos',0, false, 'product_separated', $store_id);
        
        
        $column_arr['show']  = $this->_getConfig('show_customer_phone',0, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('customer_phone_title','Phone', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('customer_phone_Xpos',460, false, 'product_separated', $store_id);
        $column_arr['code']  = 'customer_phone';
        $this->_order_config['phone'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('show_product_qty_in_order',1, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('product_qty_in_order_title','Qty', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('product_qty_in_order_Xpos',500, false, 'product_separated', $store_id);
        $column_arr['code']  = 'product_ordered';          
        $this->_order_config['qty'] = $column_arr;
                
        $column_arr['show']  = $this->_getConfig('tickbox_yn',0, false, 'product_separated', $store_id);
        $column_arr['label'] = $this->_getConfig('tickbox_title','', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('tickboxX',530, false, 'product_separated', $store_id);
        $column_arr['code']  = 'tickbox';      
        $this->_order_config['tickbox'] = $column_arr;
                
        //TODO 3: Get and set configuration values for product
        $column_arr['show']  = $this->_getConfig('pickpack_name_yn_separated',1, false, 'product_separated', $store_id);
        $column_arr['label'] = 'Productname';
        $column_arr['Xpos']  = $this->_getConfig('pickpack_name_Xpos_separated',25, false, 'product_separated', $store_id);
        $column_arr['code']  = 'name';      
        $this->_product_config['name'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('pickpack_sku_yn_separated',1, false, 'product_separated', $store_id);
        $column_arr['label'] = 'Sku';
        $column_arr['Xpos']  = $this->_getConfig('pickpack_sku_Xpos_separated',200, false, 'product_separated', $store_id);
        $column_arr['code']  = 'sku';       
        $this->_product_config['sku'] = $column_arr;
        
        $column_arr['show']  = $this->_getConfig('pickpack_type_yn_separated',1, false, 'product_separated', $store_id);
        $column_arr['label'] = 'Type';
        $column_arr['Xpos']  = $this->_getConfig('pickpack_type_Xpos_separated',420, false, 'product_separated', $store_id);
        $column_arr['code']  = 'type_id';      
        $this->_product_config['type'] = $column_arr;

        $column_arr['show']  = 1;
        $column_arr['label'] = $this->_getConfig('pickpack_title_total_qty','Total: ', false, 'product_separated', $store_id);
        $column_arr['Xpos']  = $this->_getConfig('pickpack_position_total_qty',500, false, 'product_separated', $store_id);
        $column_arr['code']  = 'total';      
        $this->_product_config['total'] = $column_arr;
                
        if ($this->_product_config['sku']['show'] == 1) $this->_columns_xpos_array['skuX'] = $this->_product_config['sku']['Xpos'];
        if ($this->_product_config['name']['show'] == 1) $this->_columns_xpos_array['nameX'] = $this->_product_config['name']['Xpos'];
        if ($this->_product_config['type']['show'] == 1) $this->_columns_xpos_array['typeX'] = $this->_product_config['type']['Xpos'];
        if ($this->_product_config['total']['show'] == 1) $this->_columns_xpos_array['totalX'] = $this->_product_config['total']['Xpos'];
        asort($this->_columns_xpos_array);
        
        if ($this->_order_config['id']['show'] == 1) $this->_columns_xpos_array_order['idX'] = $this->_order_config['id']['Xpos'];
        if ($this->_order_config['customer_name']['show'] == 1) $this->_columns_xpos_array_order['customerX'] = $this->_order_config['customer_name']['Xpos'];
        if ($this->_order_config['created_at']['show'] == 1) $this->_columns_xpos_array_order['dateX'] = $this->_order_config['created_at']['Xpos'];
        if ($this->_order_config['email']['show'] == 1) $this->_columns_xpos_array_order['emailX'] = $this->_order_config['email']['Xpos'];
        if ($this->_order_config['phone']['show'] == 1) $this->_columns_xpos_array_order['phoneX'] = $this->_order_config['phone']['Xpos'];
        if ($this->_order_config['qty']['show'] == 1) $this->_columns_xpos_array_order['qtyX'] = $this->_order_config['qty']['Xpos'];
        if ($this->_order_config['tickbox']['show'] == 1) $this->_columns_xpos_array_order['tickboxX'] = $this->_order_config['tickbox']['Xpos'];
        asort($this->_columns_xpos_array_order);
        
        $this->_helper = Mage::helper('pickpack');
        $this->y = $this->_printing_format['page_top'];
        
       
        //2. preLoad Store Order Item Product
        $shipments = explode('|', $from_shipment);
        if ($shipments[0] == 'shipment') {
            unset($from_shipment);
            $from_shipment = 'shipment';
            unset($orders);
            $orders = explode(',', $shipments[1]);
        }
        
        if ($shipments[0] == 'shipment') {
            $shipment_collection = Mage::getModel('sales/order_shipment')
            ->getCollection()
            ->addAttributeToFilter('entity_id', array('in' =>($orders)))->setPageSize(count($orders))
            ->setCurPage(1); 
            foreach ($shipment_collection as $shipment_model) {
                $shiped_items = $shipment_model->getItemsCollection();
                $shiped_items_qty = array();
                foreach ($shiped_items as $shiped_item) {
                    $shiped_items_qty[$shiped_item->getData('product_id')] = $shiped_item->getData('qty');
                }
            }
            $order_arrs[] = $shipment_model->getOrderId();
        }
        else
            $order_arrs = $orders;
        
        if($from == 'manual')
        {   
            $product_filter['type'] = $this->_getConfig('manual_processing_condition_product_attribute',0, false, 'product_separated', $store_id);
            $product_filter['code'] = $this->_getConfig('manual_processing_condition_product_attribute_code','', false, 'product_separated', $store_id);
            $product_filter['value'] = $this->_getConfig('manual_processing_condition_product_attribute_value','', false, 'product_separated', $store_id);                      
        }
        $this->updateData($order_arrs,$product_filter);   
        $helper = Mage::helper('pickpack');
        if(strlen(trim($this->product_list)) ==0)
        {   
            if($from == 'manual')
            {   
                $this->_afterGetPdf();
                return $pdf;
            }
            
            return;
        }
           //1. Print header
        $this->printHeader($page,$store_id);
        //TODO print header logo
//        $this->printLogo($page,$store_id,$show_top_logo_yn);


        foreach($this->product_list_arr as $key => $product_id)
        {
            if($this->product_ordered[$product_id] == 0)
            {
                continue;
            }
            //A. Print product
                //1. Check print new page or not
            if (($this->y < 60))
            {
                $page_text = '--  Page ' . $this->pagecount . ' --';
                if ($this->pagecount == 1) {
                    $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                    // $this->_setFont($page,$this->generalConfig['font_style_subtitles'], ($this->_printing_format['$font_size_subtitles'] - 2), $this->generalConfig['font_family_subtitles'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_subtitles']);
                    $page->drawText($page_text, 250, ($this->y - 15), 'UTF-8');
                }
                $page = $this->newPage();
                $this->pagecount++;
                $page_text = '--  Page ' . $this->pagecount . ' --';
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                $page->drawText($page_text, 250, ($this->y + 15), 'UTF-8');
            }                        
            //2. Print product line: 
            //a. Print box behind product
            //b. Print product name, sku, total count
            $product_store_arr = explode(',', $this->product_list_arr_per_store[$product_id]);
            $product_store_arr_2 = array_unique($product_store_arr);
            
            $store_view_product_name = $this->_getConfig('name_store_view', 'storeview', false,'product_separated', $store_id);         
            $specific_store_id = $this->_getConfig('specific_store', '', false,'product_separated', $store_id);
            
                
            switch ($store_view_product_name) {
                    case 'itemname':
                        $product_model =$helper->getProduct($product_id);
                        $product_name = $this->product_name_from_item[$product_id];
                        break;
                    case 'default':
                        $product_model =$helper->getProduct($product_id);
                        $product_name = $product_model->getData('name');
                        break;
                    case 'storeview':
                        if(isset($product_store_arr_2[0]))
                            $product_model = $helper->getProductForStore($product_id,$product_store_arr_2[0]);
                        else
                            $product_model =$helper->getProduct($product_id);
                        $product_name = $product_model->getData('name');
                        break;
                    case 'specificstore':
                        $product_model = $helper->getProductForStore($product_id,$specific_store_id);
                        $product_name = $product_model->getData('name');
                        break;
                    default:
                        $product_model =$helper->getProduct($product_id);
                        $product_name = $product_model->getData('name');
                        break;
                }
                
            unset($product_store_arr);
            unset($product_store_arr_2);                        
            $page->setFillColor($this->_printing_format['grey_bkg_color']);
            $page->setLineColor($this->_printing_format['grey_bkg_color']);
            
            
            $num_lines = $this->checkMultiLineName($product_name);
            if($num_lines == 0) $num_lines ++;
            $grey_box_y1 = ($this->y - ($this->generalConfig['font_size_body'] * 1.1 + ($num_lines - 1) * ($this->generalConfig['font_size_body'] + 2)));
            $grey_box_y2 = ($this->y + ($this->generalConfig['font_size_body'] * 0.85));
            // New function for these lines.
            $page->drawRectangle(20, $grey_box_y1, $this->_printing_format['padded_right'], $grey_box_y2);
            $this->y -=5;
            $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
            foreach($this->_product_config as $k => $v)
            {
                if($v['show'] == 0)
                    continue;
                $print_data ='';
                if($v['code'] == 'total')
                {
                    $page->drawText($v['label'],$v['Xpos'] , $this->y, 'UTF-8');
                    $print_data = round($this->product_ordered[$product_id],$this->_printing_format['round_number']);
                    $page->drawText($print_data, $v['Xpos'] + 30,$this->y, 'UTF-8');
                    continue;
                }
                else
                    if($v['code'] == 'name')
                    {
                        $print_data = $product_name;
                    }
                    else
                        $print_data = $product_model->getData($v['code']);
                //Get product name for store view here
                //TODO check code = product name + check print wordwrap here.
                if(strlen($print_data) > 0)
                {
                    if($v['code'] == 'name'){
                        $max_chars_print = $this->getMaxCharsPrinting($print_data, 'name', 'nameX');
                        if(strlen($print_data) > $max_chars_print){
                            $before_print_product_name = $this->y;
                            $multiline_name = wordwrap($print_data, $max_chars_print, "\n");
                            $token = strtok($multiline_name, "\n");
                            while ($token != false) {
                                $page->drawText(trim($token), $v['Xpos'], $this->y, 'UTF-8');
                                $this->y -= ($this->generalConfig['font_size_body'] + 2);
                                $token = strtok("\n");
                            }
                            $after_print_product_name = $this->y + $this->generalConfig['font_size_body'] + 2;
                            $this->y = $before_print_product_name;
                        }
                        else
                            $page->drawText($print_data, $v['Xpos'], $this->y, 'UTF-8');
                    }
                    else
                        $page->drawText($print_data, $v['Xpos'], $this->y, 'UTF-8');
                }
            }
            if(isset($after_print_product_name))
                $this->y = $after_print_product_name;
            $this->y -=17;
            //B. Print order
            //1.Check to print new line or not
            // 2. Print order title bar          
            // 3. print each value.
            // $this->order_model_list

            if(isset($this->order_list_of_product_per_option[$product_id]))
            {
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                
                foreach($this->order_list_of_product_per_option[$product_id] as $opt => $order_list_option)
                { 
                    $this->y-=5;
                    //TODO print options multiline.
                    $opt_arr = explode("][", $opt);
                    $page->drawText('Options: ', 25, $this->y, 'UTF-8');
                    $this->y -=$this->generalConfig['font_size_body']*1.25;
                    foreach ($opt_arr  as $opt_each){
                        $opt_each = trim($opt_each, "]");
                        $opt_each = trim($opt_each, "[");
                        $page->drawText($opt_each , 35, $this->y, 'UTF-8');
                        $this->y -= $this->generalConfig['font_size_body']*1.2;
                    }
                    $page->setFillColor($this->_printing_format['white_bkg_color']);
                    $page->setLineColor($this->_printing_format['grey_bkg_color']);
                    $grey_box_y1 = ($this->y - ($this->generalConfig['font_size_body'] * 1.35));
                    $grey_box_y2 = ($this->y + ($this->generalConfig['font_size_body'] * 0.85));
                    $page->drawLine(20,$grey_box_y1+5,$this->_printing_format['padded_right'], $grey_box_y1+5);
                    $this->y -=5;
                    $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body']-2, $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                    foreach($this->_order_config as $k => $v)
                    {
                        if($v['show'] == 1)
                        $page->drawText($v['label'], $v['Xpos'], $this->y, 'UTF-8');
                    }
                    $this->y -=20;
                    $order_list_option_arr = explode(',', $order_list_option);
                                        $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                    foreach ($order_list_option_arr as $order_id_option)
                    {
                        if (($this->y < 60))
                        {
                            $page_text = '--  Page ' . $this->pagecount . ' --';
                            if ($this->pagecount == 1) {
                                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                                $page->drawText($page_text, 250, ($this->y - 15), 'UTF-8');
                            }
                            $page = $this->newPage();
                            $this->pagecount++;
                            $page_text = '--  Page ' . $this->pagecount . ' --';
                            $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                            $page->drawText($page_text, 250, ($this->y + 15), 'UTF-8');
                        }
                        $this->printOrder($page,$product_id,$order_id_option);
                    }

                }
            }
            else
            {
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                $page->setFillColor($this->_printing_format['white_bkg_color']);
                $page->setLineColor($this->_printing_format['grey_bkg_color']);
                $grey_box_y1 = ($this->y - ($this->generalConfig['font_size_body'] * 1.35));
                $grey_box_y2 = ($this->y + ($this->generalConfig['font_size_body'] * 0.85));
                $page->drawLine(20,$grey_box_y1+5,$this->_printing_format['padded_right'], $grey_box_y1+5);
                $this->y -=5;

                // 2. Print order title bar
                if (($this->y < 60))
                {
                    $page_text = '--  Page ' . $this->pagecount . ' --';
                    if ($this->pagecount == 1) {
                        $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                        $page->drawText($page_text, 250, ($this->y - 15), 'UTF-8');
                    }
                    $page = $this->newPage();
                    $this->pagecount++;
                    $page_text = '--  Page ' . $this->pagecount . ' --';
                    $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                    $page->drawText($page_text, 250, ($this->y + 15), 'UTF-8');
                }
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body']-2, $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                foreach($this->_order_config as $k => $v)
                {
                    if($v['show'] == 1)
                    $page->drawText($v['label'], $v['Xpos'], $this->y, 'UTF-8');
                }
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                $this->y -=20;

                foreach($this->order_list_of_product[$product_id] as $k => $orderId)
                {
                    //TODO 1. Check order new page here.Check to print new line or not
                    if (($this->y < 60))
                    {
                        $page_text = '--  Page ' . $this->pagecount . ' --';
                        if ($this->pagecount == 1) {
                            $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                            $page->drawText($page_text, 250, ($this->y - 15), 'UTF-8');
                        }
                        $page = $this->newPage();
                        $this->pagecount++;
                        $page_text = '--  Page ' . $this->pagecount . ' --';
                        $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
                        $page->drawText($page_text, 250, ($this->y + 15), 'UTF-8');
                    }
                    $this->printOrder($page,$product_id,$orderId,$store_id);
                }
            }
            $this->y -=10;
            unset($before_print_product_name);
            unset($after_print_product_name);
        }
        $this->_afterGetPdf();
        return $pdf;
    }
    
    protected function printHeader(&$page, $store_id) {
        $this->_setFont($page, $this->generalConfig['font_style_header'], $this->generalConfig['font_size_header'], $this->generalConfig['font_family_header'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_header']);
        $page->drawText($this->_helper->__($this->_printing_format['page_title']), 20, $this->y, 'UTF-8');
        $this->y -= $this->generalConfig['font_size_body'];
        $page->setFillColor($this->_printing_format['font_color_header_zend']);
        $page->setLineColor($this->_printing_format['font_color_header_zend']);
        $page->setLineWidth(0.5);
        $page->drawRectangle(17, $this->y, $this->_printing_format['padded_right'], ($this->y - 1));
        $this->y -= 20;
        //Print printed date
        if($this->_getConfig('pickpack_pickprint',1, false, 'product_separated', $store_id) == 1){
            $this->_setFont($page, 'regular', $this->generalConfig['font_size_body'] + 2, $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_subtitles']);
            $currentTimestamp = Mage::getModel('core/date')->timestamp(time()); //Magento's timestamp function makes a usage of timezone and converts it to timestamp
            $printed_date = date($this->_printing_format['date_format'], $currentTimestamp);
            $page->drawText('Date:    '.$printed_date, 20, $this->y, 'UTF-8');
            $this->y -= 20;
        }
    }

    protected function printLogo(&$page,$store_id, $show_top_logo_yn){
        /***************************PRINTING 1 HEADER LOGO *******************************/
        $minY_logo = $this->_printing_format['page_top'];
        if ($show_top_logo_yn == 1) {
            $sub_folder = 'logo_product_separated';
            $option_group = 'product_separated';
            /*************************** PRINT HEADER LOGO *******************************/

            $packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $option_group . '/product_separated_logo', $store_id);
            $helper = Mage::helper('pickpack');
            if ($packlogo_filename) {
                $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                if (is_file($packlogo_path)) {
                    $img_width = $this->_logo_maxdimensions[0];
                    $img_height = $this->_logo_maxdimensions[1];

                    $imageObj = $helper->getImageObj($packlogo_path);
                    $orig_img_width = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $img_width = $orig_img_width;
                    $img_height = $orig_img_height;

                    /*************************** RESIZE IMAGE BY "AUTO-RESIZE" VALUE *******************************/


                    if ($orig_img_width > $this->_logo_maxdimensions[0]) {
                        $img_height = ceil(($this->_logo_maxdimensions[0] / $orig_img_width) * $orig_img_height);
                        $img_width = $this->_logo_maxdimensions[0];
                    } //Fix for auto height --> Need it?
                    else

                        if ($orig_img_height > $this->_logo_maxdimensions[1]) {
                            $temp_var = $this->_logo_maxdimensions[1] / $orig_img_height;
                            //$img_height = ceil(($logo_maxdimensions[1] / $orig_img_height) * $orig_img_height);
                            $img_height = $this->_logo_maxdimensions[1];
                            $img_width = $temp_var * $orig_img_width;
                        }
                    // {
                    // $img_width = $orig_img_width;
                    // $img_height = $orig_img_height;
                    // }


                    $x1 = 27;

                    $y2 = ($this->_printing_format['page_top'] + 10);

                    $y1 = ($y2 - $img_height);

                    $x2 = ($x1 + $img_width);
                    $image_ext = '';
                    $temp_array_image = explode('.', $packlogo_path);
                    $image_ext = array_pop($temp_array_image);
                    if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
                        $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                        $page->drawImage($packlogo, $x1, $y1, $x2, $y2);
                        unset($packlogo);
                        unset($packlogo_filename);
                        unset($packlogo_path);
                    }
                    $minY_logo = $y1 - 20;
                }
            }

            /*************************** END PRINT HEADER LOGO ***************************/
        }
        return $minY_logo;
        //return $this->y;
    }

    protected function preloadProducts($storeId='') {
        $product_arr = explode(',', $this->product_list);
        $product_arr = array_unique($product_arr);

        $productCollection = Mage::getModel('catalog/product')->getCollection()
                            ->addAttributeToSelect('*')
                            ->setOrder('entity_id', 'desc')
                            ->addAttributeToFilter('entity_id', array('in' =>$product_arr));
        if($storeId!='')
        {
            $productCollection->setStoreId($storeId)
                              ->addStoreFilter($storeId);
        }       
        $productCollection->getSelect()->joinLeft(
            array('_inventory_table' => $productCollection->getTable('cataloginventory/stock_item')),
            '_inventory_table.product_id = e.entity_id',
            array('enable_qty_increments', 'qty_increments', 'qty', 'is_in_stock')
        );
        $this->_productsCollection = $productCollection;
        foreach($productCollection as $model)
         {
            $this->product_model_list_arr[$model->getId()] = $model;
         }
    }

    protected function preloadProductsPerStore($storeId='') {}

    protected function printOrder(&$page,$product_id,$orderId,$store_id=0) {
        
        $phone = $this->order_model_list[$orderId]->getBillingAddress()->getData('telephone');
        foreach($this->_order_config as $k => $v)
        {
            if($v['show'] == 0)
                continue;
            if($v['code'] == 'tickbox')
            {
                $tickbox_width = $this->_getConfig('tickbox_width',10, false, 'product_separated', $store_id);
                $page->setFillColor($this->_printing_format['white_bkg_color']);
                $page->setLineColor($this->_printing_format['grey_bkg_color']);
                $page->drawRectangle($v['Xpos'], $this->y  -$tickbox_width/4, $v['Xpos'] + $tickbox_width, $this->y +3*$tickbox_width/4);
                $this->_setFont($page, $this->generalConfig['font_style_body'], $this->generalConfig['font_size_body'], $this->generalConfig['font_family_body'], $this->generalConfig['non_standard_characters'], $this->generalConfig['font_color_body']);
            }
            else
            {
                switch ($v['code']) {
                    case 'order_id':
                        $print_data = $this->order_model_list[$orderId]->getData('increment_id');
                        break;
                    case 'created_at':
                        $date = new DateTime($this->order_model_list[$orderId]->getData('created_at'));
                        $print_data = $date->format($this->_printing_format['date_format']);
                        break;
                    case 'customer_name':
                        $print_data = $this->order_model_list[$orderId]->getCustomerName();
                        break;
                    case 'customer_email':
                        $print_data = $this->order_model_list[$orderId]->getCustomerEmail();
                        break;
                    case 'customer_phone':
                        $print_data = $phone;
                        break;
                    case 'product_ordered':
                        $print_data = round($this->product_ordered_per_order[$product_id][$orderId],$this->_printing_format['round_number']);
                        break;

                    default:
                        $print_data = 'test';
                        break;
                }
                if($v['code'] == 'customer_name'){
                    //$print_data = Mage::helper('pickpack/functions')->clean_method($print_data, 'pdf_more');
                    $max_chars_print = $this->getMaxCharsPrinting2($print_data, 'customer_name', 'customerX');
                    if(strlen($print_data) > $max_chars_print){
                        $before_print_customer_name = $this->y;
                        $multiline_name = wordwrap($print_data, $max_chars_print, "\n");
                        $token = strtok($multiline_name, "\n");
                        while ($token != false) {
                            $page->drawText(trim($token), $v['Xpos'], $this->y, 'UTF-8');
                            $this->y -= ($this->generalConfig['font_size_body'] + 2);
                            $token = strtok("\n");
                        }
                        $after_print_customer_name = $this->y + $this->generalConfig['font_size_body'] + 2;
                        $this->y = $before_print_customer_name;
                    }
                    else
                        $page->drawText($print_data, $v['Xpos'], $this->y, 'UTF-8');
                }
                else
                    $page->drawText($print_data, $v['Xpos'], $this->y, 'UTF-8');
                unset($print_data);
            }
        }
        if(isset($after_print_customer_name))
            $this->y = $after_print_customer_name;
        if(isset($tickbox_width) && ($tickbox_width > ($this->generalConfig['font_size_body']*1.2)))
            $this->y -= $tickbox_width;
        else
            $this->y -= $this->generalConfig['font_size_body']*1.2;
        unset($after_print_customer_name);
        unset($before_print_customer_name);
    }
    
    protected function getPrevNext2($haystack,$needle,$prevnext = 'next',$padded_right=560,$page_pad_right=-10) {
        $prev = $next = null;
        $pad = 25; // points to pad result, eg for '...' to be added on...
        //$flag_imageX = 0;
        $aKeys = array_keys($haystack);
        $key = '';
        if(($key = array_search('imagesX', $aKeys)) !== false) {
            unset($aKeys[$key]);
        }
        
        $k = array_search($needle,$aKeys);
        $size = count($aKeys);
        $pre_k = $next_k = null;
        if ($k !== false) {
            if ($k > 0)
            {
                if(isset($aKeys[$k-1]) && isset($haystack[$aKeys[$k-1]]) && $haystack[$aKeys[$k-1]])
                {
                    $prev = array($aKeys[$k-1] => $haystack[$aKeys[$k-1]]);
                    $pre_k = $aKeys[$k-1];
                }
                else
                {
                    if(isset($aKeys[$k-2]) && isset($haystack[$aKeys[$k-2]])){
                        $prev = array($aKeys[$k-2] => $haystack[$aKeys[$k-2]]);
                        $pre_k = $aKeys[$k-2];
                    }
                }
            }
            if ($k < $size)
            {
                if(isset($aKeys[$k+1]) && isset($haystack[$aKeys[$k+1]]) &&  $haystack[$aKeys[$k+1]])
                {
                    $next = array($aKeys[$k+1] => $haystack[$aKeys[$k+1]]);
                    $next_k = $aKeys[$k+1];
                }
                else
                {
                    if(isset($aKeys[$k+2]) && isset($haystack[$aKeys[$k+2]])){
                        $next = array($aKeys[$k+2] => $haystack[$aKeys[$k+2]]);
                        $next_k = $aKeys[$k+2]; 
                    }
                }
            }
        }
        $next_key = 0;
        $prev_key =0;
        if(isset($prev[$pre_k]) && ($prev[$pre_k] >=0))
            $prev_key =$prev[$pre_k];
        else
            $prev_key = 0;
            
        $max_right = $padded_right - $page_pad_right + $pad;
        if(isset($next[$next_k]) && ($next[$next_k] <= $max_right))
            $next_key =$next[$next_k];
        else
            $next_key = $padded_right;
        
        if($prevnext == 'next') return $next_key;
        elseif($prevnext == 'prev') return $prev_key;
        else return array($prev,$next);
    }
    
    protected function getMaxCharsPrinting($name, $column, $columnX){
        $next_col_to_product_x = getPrevNext2($this->_columns_xpos_array, $columnX, 'next');
        $max_name_length = $next_col_to_product_x - $this->_product_config[$column]['Xpos'];
        $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->generalConfig['font_size_body']));
        $char_width_name = $line_width_name / strlen($name);
        $max_chars_name = round($max_name_length / $char_width_name);
        return $max_chars_name;
    }
    
    protected function getMaxCharsPrinting2($name, $column, $columnX){
        $next_col_to_product_x = getPrevNext2($this->_columns_xpos_array_order, $columnX, 'next');
        $max_name_length = $next_col_to_product_x - $this->_order_config[$column]['Xpos'];
        $font_temp_shelf2 = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
        $line_width_name = $this->parseString($name, $font_temp_shelf2, ($this->generalConfig['font_size_body']));
        $char_width_name = $line_width_name / strlen($name);
        $max_chars_name = round($max_name_length / $char_width_name);
        return $max_chars_name;
    }
    
    protected function checkMultiLineName($name){
        //$name = htmlentities($name);
        $multiline_name = array();
        //$name = Mage::helper('pickpack/functions')->clean_method($name, 'pdf_more');
        $max_chars_print = $this->getMaxCharsPrinting($name, 'name', 'nameX');
        if(strlen($name) > $max_chars_print){
            $multiline_name = explode("\n", wordwrap($name, $max_chars_print, "\n"));
        }
        return count($multiline_name);
    }

    
}