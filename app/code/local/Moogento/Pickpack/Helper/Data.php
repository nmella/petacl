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
* File        Data.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

class Moogento_Pickpack_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_productsCache = array();
    protected $_productsByStoreCache = array();
    protected $_orderCache = array();
    protected $_orderToShipmentCache = array();
    protected $_customerGroupCache = array();
    protected $_imageCache = array();
    protected $_imagePathCache = array();
    protected $_imageWidthHeightCache = array();
    protected $_simpleProductTypes = array(
        'simple',
        'virtual'
    );

    public function getFontPath() {
        return Mage::getBaseDir('skin').'/adminhtml/default/default/moogento/pickpack/fonts/';
    }
	
    public function getFontAddonPath() {
        return Mage::getBaseDir('skin').'/adminhtml/default/default/moogento/pickpack/fonts_addon/';
    }
	
	// In general folder : only opensans regular and extrabolditalic
    public function getFontGeneralPath() {
        return Mage::getBaseDir('skin').'/adminhtml/default/default/moogento/general/fonts/';
    }
	
	// pickPack only
    public function getFontCustomPath() {
        return Mage::getBaseDir('media').'/moogento/pickpack/custom_font/default/';
    }
	
    public function getPickpackImagesPath() {
        return Mage::getBaseDir('skin').'/adminhtml/default/default/moogento/pickpack/images/';
    }
	// gift_message_card.png

    public function sortMultiDimensional(&$array, $subKey, $subKey2, $sortorder_packing_bool=false, $sortorder_packing_secondary_bool=false) {
        $array1 = array();
        $array2 = array();
        foreach ($array as $key => $row) {
            $array1[$key]  = $row[$subKey];
            $array2[$key] = $row[$subKey2];
        }
        // Sort the data with volume descending, edition ascending
        // Add $data as the last parameter, to sort by the common key
        if($sortorder_packing_bool)
			$sortorder_packing_bool = SORT_ASC;
        else 
			$sortorder_packing_bool = SORT_DESC;

        if($sortorder_packing_secondary_bool) 
			$sortorder_packing_secondary_bool = SORT_ASC;
        else 
			$sortorder_packing_secondary_bool = SORT_DESC;
        array_multisort($array1,$sortorder_packing_bool ,$array2, $sortorder_packing_secondary_bool , $array);
    }

    public function checkStock($order) {
        $inStock = true;

        foreach ($order->getAllItems() as $orderItem) {

            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($orderItem->getProductId());
            if (!$stockItem->getId()) {
                $inStock = false;
                break;
            }

            if (in_array($orderItem->getProductType(), $this->_simpleProductTypes)) {
                if (!$stockItem->getIsInStock()) {
                    $inStock = false;
                    break;
                }

                if ($stockItem->getManageStock() && ($stockItem->getQty() < 1)) {
                    $inStock = false;
                    break;
                }
            } elseif (!$stockItem->getIsInStock()) {
                $inStock = false;
                break;
            }
        }

        return $inStock;
    }

    /**
     * Check if module is active
     *
     * @param string $moduleName    Namespace_Modulename
     * @param string|null $moduleVersion    minimum version of module
     * @return boolean  actived or not
     */

    public function isInstalled($moduleName,$moduleVersion = NULL) {
        if (Mage::getConfig()->getModuleConfig($moduleName)->is('active', 'true')) {
            if ((isset($moduleVersion)) && (version_compare(Mage::getConfig()->getModuleConfig($moduleName)->version, $moduleVersion) < 0))
                return false;
			return true;
        }
        return false;
    }

    /**
     * Resize Image proportionally and return the resized image url
     *
     * @param string $imageName         name of the image file
     * @param integer|null $width       resize width
     * @param integer|null $height      resize height
     * @param string|null $imagePath    directory path of the image present inside media directory
     * @return string               full url path of the image
     */


    public function resizeImage($imageName, $width = NULL, $height = NULL, $imagePath = NULL) {
        $imagePath = str_replace("/", DS, $imagePath);
        $imagePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $imageName;

        if ($width == NULL && $height == NULL) {
            $width = 100;
            $height = 100;
        }
        $resizePath = $width . 'x' . $height;
        $resizePathFull = Mage::getBaseDir('media') . DS . $imagePath . DS . $resizePath . DS . $imageName;

        if (file_exists($imagePathFull) && !file_exists($resizePathFull)) {
            $imageObj = new Varien_Image($imagePathFull);
            $imageObj->constrainOnly(TRUE);
            $imageObj->keepAspectRatio(TRUE);
            $imageObj->resize($width, $height);
            $imageObj->save($resizePathFull);
        }
        $imagePath = str_replace(DS, "/", $imagePath);
		
        return Mage::getBaseUrl("media") . $imagePath . "/" . $resizePath . "/" . $imageName;
    }
	
	public function getParentProId($product_id){
		if ($image_product = $this->getProduct($product_id)) {
			$image_path = $image_product->getImage();
			$image_parent_sku = $image_product->getSku();
			$has_real_image_set = ($image_path != null && $image_path != "no_selection" && $image_path != '');
			$image_product_id = $product_id;

			// if is child (not parent)
			if (($has_real_image_set !== true) && (is_object($image_product)) && ($image_product->getTypeId() == "simple")) {
				$parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);

				if(!$parent_ids)
					$parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product_id);

				if (is_array($parent_ids)) {
					foreach ($parent_ids as $key => $parent_id) {
						if ($image_product = $this->getProduct($parent_id)) {
							$image_path = $image_product->getImage();
							$image_parent_sku = $image_product->getSku();
							$image_product_id = $parent_id;
							$has_real_image_set = ($image_path != null && $image_path != "no_selection" && $image_path != ''
								&& (strpos($image_path, 'placeholder') === false));
						}
					}
				}
			}
		}
		return $image_product_id;
	}

	public function getSourceImageRes($product_images_source, $product_id){
		$image_product = is_object($product_id) ? $product_id : $this->getProduct($product_id);
		$product_images_source_res = $product_images_source;

		if($image_product){
			if ($product_images_source == 'gallery')
				$product_images_source_res = 'image';
			if (($product_images_source_res == 'thumbnail') && (!$image_product->getThumbnail() || ($image_product->getThumbnail() == 'no_selection'))) 
				$product_images_source_res = 'image';
			elseif (($product_images_source_res == 'small_image') && (!$image_product->getSmallImage() || ($image_product->getSmallImage() == 'no_selection'))) 
				$product_images_source_res = 'image';
			if (($product_images_source_res == 'image') && (!$image_product->getImage() || ($image_product->getImage() == 'no_selection')))
				$product_images_source_res = 'small_image';
			if (($product_images_source_res == 'small_image') && (!$image_product->getSmallImage() || ($image_product->getSmallImage() == 'no_selection'))) 
				$product_images_source_res = 'thumbnail';
		}
		return $product_images_source_res;
	}
	
	public function getWidthHeightImage($product_id, $product_images_source_res, $product_images_maxdimensions) {
        if(is_object($product_id)) {
            $image_product = $product_id;
            $product_id = $image_product->getId();
        } else
            $image_product = $this->getProduct($product_id);

        if (!isset($this->_imageWidthHeightCache[$product_images_source_res]))
            $this->_imageWidthHeightCache[$product_images_source_res] = array();

        if (isset($this->_imageWidthHeightCache[$product_images_source_res][$image_product->getId()]))
            return $this->_imageWidthHeightCache[$product_images_source_res][$image_product->getId()];

		$img_width = 0;
        $img_height = 0;
		$image_demension = array();
		
		if ($image_product->getData($product_images_source_res) != 'no_selection' && $image_product->getData($product_images_source_res) != ''){
            try{
			     $image_obj = Mage::helper('catalog/image')->init($image_product, $product_images_source_res);
            }
            catch(Exception $e){
                $baseDir = Mage::getSingleton('catalog/product_media_config')->getBaseMediaPath();
                //$size = getimagesize($baseDir . $image_product->getData($product_images_source_res));

                $messages = "product Id: " . $product_id . "\n";
                $messages = $messages . "URL image: " . $image_product->getData($product_images_source_res) . "\n";
                $messages = $messages . "Size image: " . filesize($baseDir . $image_product->getData($product_images_source_res)) . " bytes" . "\n";
                //$messages = $messages . "Type Image: " . $size['mime'];
                Mage::log($messages, null, 'moogento_pickpack.log');
            }
        }
		
		if (isset($image_obj)) {
			$img_width = $product_images_maxdimensions[0];
			$img_height = $product_images_maxdimensions[1];
			$orig_img_width = $image_obj->getOriginalWidth();
			$orig_img_height = $image_obj->getOriginalHeigh(); // getOriginalHeigh() = spell mistake

			if ($orig_img_width != $orig_img_height) {
				if ($orig_img_width > $orig_img_height)
					$img_height = ceil(($orig_img_height / $orig_img_width) * $product_images_maxdimensions[1]);
				elseif ($orig_img_height > $orig_img_width)
					$img_width = ceil(($orig_img_width / $orig_img_height) * $product_images_maxdimensions[0]);
			}
			$image_demension[0] = $img_width;
			$image_demension[1] = $img_height;
		}

        $this->_imageWidthHeightCache[$product_images_source_res][$image_product->getId()] = $image_demension;
		return $image_demension;
	}
	public function getImagePaths($product, $product_images_source, $product_images_maxdimensions) {
		$imagePaths = array();
		$resize_x = null;
        $resize_y = null;
		$img_width = 0;
		$img_height = 0;

        $image_product = is_object($product) ? $product : $this->getProduct($product);

        if (!isset($this->_imagePathCache[$product_images_source]))
            $this->_imagePathCache[$product_images_source] = array();

        if (isset($this->_imagePathCache[$product_images_source][$image_product->getId()]))
            return $this->_imagePathCache[$product_images_source][$image_product->getId()];

		$image_galleries = $image_product->getData('media_gallery');

        if(isset($image_galleries['images']) && count($image_galleries['images']) > 0) {

            $product_images_source_res = $this->getSourceImageRes($product_images_source, $product);
			$img_demension = $this->getWidthHeightImage($image_product, $product_images_source_res, $product_images_maxdimensions);
			if(is_array($img_demension) && count($img_demension) > 1) {
				$img_width = $img_demension[0];
				$img_height = $img_demension[1];
			}
			if (is_integer($img_width))
				$resize_x = ($img_width * 4);
			if (is_integer($img_height))
				$resize_y = ($img_height * 4);
			
			if ($product_images_source == 'gallery') {
				$gallery = $image_product->getMediaGalleryImages();
				// can get posiiton here
				$image_urls = array();
				foreach ($gallery as $image) {
					$imagePath_temp = Mage::helper('catalog/image')->init($image_product, 'image', $image->getFile())
						->constrainOnly(TRUE)
						->keepAspectRatio(TRUE)
						->keepFrame(FALSE)
						->resize($resize_x, $resize_y)
						->__toString();

					if (strpos($imagePath_temp, 'placeholder') === false)
                        $imagePaths[] = $imagePath_temp;
				}
			} else {
                try{
    				$imagePath_temp = Mage::helper('catalog/image')->init($image_product, $product_images_source_res)
    					->constrainOnly(TRUE)
    					->keepAspectRatio(TRUE)
    					->keepFrame(FALSE)
    					->resize($resize_x, $resize_y)
    					->__toString();
                }
                catch(Exception $e){
                    //var_dump($e->getMessage());
                }
				
				if (strpos($imagePath_temp, 'placeholder') === false)
                    $imagePaths[] = $imagePath_temp;
			}
		}
        $this->_imagePathCache[$product_images_source][$image_product->getId()] = $imagePaths;
		
		return $imagePaths;
	}
	
	public function checkTypeImageProduct($sku_image_paths, $image_ext){
        if ( !function_exists( 'exif_imagetype' ) ) {
            function exif_imagetype ( $filename ) {
                if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false )
                    return $type;

                return false;
            }
        }
        $type_true_false = 0;
        if(isset($sku_image_paths) && function_exists('exif_imagetype')){
			$image_ext = trim(strtolower($image_ext));
            switch(exif_imagetype($sku_image_paths)){
                case 2://jpg
                    if($image_ext == 'jpg' || $image_ext == 'jpeg')
                        $type_true_false = 1;
                    break;
                case 3: //png
                    if($image_ext == 'png')
                        $type_true_false = 1;
                    break;
                default :
                    $type_true_false = 1;
            }
        }
        return $type_true_false;
    }

    /**
     * @param int $productId
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($productId) {
        if (!isset($this->_productsCache[$productId]))
            $this->_productsCache[$productId] = Mage::getModel('catalog/product')->load($productId);

        return $this->_productsCache[$productId];
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return Mage_Catalog_Model_Product
     */
    public function getProductForStore($productId, $storeId) {
        if ($storeId == Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            return $this->getProduct($productId);

        if (!isset($this->_productsByStoreCache[$storeId]))
            $this->_productsByStoreCache[$storeId] = array();

        if (!isset($this->_productsByStoreCache[$storeId][$productId]))
            $this->_productsByStoreCache[$storeId][$productId] = Mage::getModel('catalog/product')->setStoreId($storeId)->load($productId);

        return $this->_productsByStoreCache[$storeId][$productId];
    }

    /**
     * @param int $orderId
     * @return Mage_Sales_Model_Order
     */
    public function getOrder($orderId) {
        if (!isset($this->_orderCache[$orderId]))
            $this->_orderCache[$orderId] = Mage::getModel('sales/order')->load($orderId);

        return $this->_orderCache[$orderId];
    }

    /**
     * @param int $shipmentId
     * @return Mage_Sales_Model_Order
     */
    public function getOrderByShipment($shipmentId) {
        if (!isset($this->_orderToShipmentCache[$shipmentId])) {
			$shipment_collection = Mage::getResourceModel('sales/order_shipment_collection');
			$shipment_collection->addAttributeToFilter('entity_id',$shipmentId);
			$shipment_collection->addAttributeToSelect('order_id');
			$shipment_model = $shipment_collection->getFirstItem();        
            $this->_orderToShipmentCache[$shipmentId] = $shipment_model->getData('order_id');
        }

        return $this->getOrder($this->_orderToShipmentCache[$shipmentId]);
    }

    /**
     * @param int $groupId
     * @return string
     */
    public function getCustomerGroupCode($groupId) {
        if (!isset($this->_customerGroupCache[$groupId])) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $tableName = $resource->getTableName('customer/customer_group');
            $query = 'SELECT customer_group_code FROM ' . $tableName . ' WHERE customer_group_id = ' . (int)$groupId;
            $this->_customerGroupCache[$groupId] = ucwords(strtolower($readConnection->fetchOne($query)));
        }

        return $this->_customerGroupCache[$groupId];
    }

    /**
     * @param string $path
     * @return Varien_Image
     */
    public function getImageObj($path) {
        if (!isset($this->_imageCache[$path]))
            $this->_imageCache[$path] = new Varien_Image($path);

        return $this->_imageCache[$path];
    }
    
    public function invoiceItems() {
    	$order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
		$items = $order->getItemsCollection();

		$qtys = array(); //this will be used for processing the invoice

		foreach($items as $item) {
			$qty_to_invoice = 123; //where x is the amount you wish to invoice
			//<!-- please note that if you don't want to invoice this product, set this value to 0 -->
			$qtys[$item->getId()] = $qty_to_invoice;
			//<!-- Note that the ->getId() method gets the item_id on the order, not the product_id -->
		}

		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
		//<!-- The rest is only required when handling a partial invoice as in this example-->
		$amount = $invoice->getGrandTotal();
		$invoice->register()->pay();
		$invoice->getOrder()->setIsInProcess(true);

		$history = $invoice->getOrder()->addStatusHistoryComment(
			'Partial amount of $' . $amount . ' captured automatically.', false
		);

		$history->setIsCustomerNotified(true);

		$order->save();

		Mage::getModel('core/resource_transaction')
			->addObject($invoice)
			->addObject($invoice->getOrder())
			->save();
		$invoice->save();
		$invoice->sendEmail(true, ''); //set this to false to not send the invoice via email
		
		
		/**/
		$order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
		$items = $order->getAllItems();

		$qtys = array(); //this will be used for processing the invoice

		foreach($items as $item) {
			if ($item->getSku() == 'SKU1')
				$qty_to_invoice = $item->getQtyOrdered(); // now gets order quantity of item
			else
				$qty_to_invoice = 0;

			$qtys[$item->getId()] = $qty_to_invoice;
			//<!-- Note that the ->getId() method gets the item_id on the order, not the product_id -->
		}

		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($qtys);
		
		
		if($order->canShip()) {
			$itemQty =  $order->getItemsCollection()->count();
			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty); // --> should be array here.
			$shipment = new Mage_Sales_Model_Order_Shipment_Api();
			$shipmentId = $shipment->create($orderId);
		}

    }

    
    public function updatePrintedTime($orderIds,$type='none') {
        $orderCollection = Mage::getModel('sales/order')
                                ->getCollection()
                                ->addAttributeToFilter('entity_id', array('in' =>($orderIds)))
                                ->addAttributeToSelect('entity_id')
                                ->addAttributeToSelect('increment_id')
                                ->setPageSize(count($orderIds))//count($orders)); 1000
                                ->setCurPage(1);
        $now = Mage::getModel('core/date')->timestamp(time());  
        $date =  date('Y-m-d', $now); 
        foreach($orderCollection as $order) {
            $printed_time =  Mage::getModel('pickpack/printedtime');
            $printed_time->setData('order_id',$order->getData('entity_id'));
            $printed_time->setData('order_increment_id',$order->getData('increment_id'));
            $printed_time->setData('type',$type);
            $printed_time->setData('date',$date);
            $printed_time->save();
        }
    }
    
    public function processRelatedOrders($orderIds) {
        $printed_orders = array();
        $processed_orders = array();
        foreach($orderIds as $single_order_id) {
            if(isset($printed_orders[$single_order_id]))
                continue;
            $orders = array();
            // const STATE_NEW             = 'new';
            // const STATE_PENDING_PAYMENT = 'pending_payment';
            // const STATE_PROCESSING      = 'processing';
            // const STATE_COMPLETE        = 'complete';
            // const STATE_CLOSED          = 'closed';
            // const STATE_CANCELED        = 'canceled';
            // const STATE_HOLDED          = 'holded';
            // const STATE_PAYMENT_REVIEW  = 'payment_review';
            $order_statuses = array(Mage_Sales_Model_Order::STATE_NEW, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,Mage_Sales_Model_Order::STATE_PROCESSING);
            $check_same_zipcode = 1;
            $check_same_shipping_address =1;

            $order_model = Mage::getModel('sales/order')->load($single_order_id);
            $store_id = $order_model['store_id'];

            $original_shipping_address_id = $order_model['shipping_address_id'];
            $original_shipping_address_collection = Mage::getModel('sales/order_address')->getCollection();
            $original_shipping_address_collection->addAttributeToFilter('entity_id', array('in' => array($original_shipping_address_id)));
            $original_shipping_address_raw = $original_shipping_address_collection->toArray();
            $original_shipping_address = reset($original_shipping_address_raw['items']);
           
		    if($check_same_shipping_address) {
                $original_shipping_address_id = $order_model['shipping_address_id'];
                $original_shipping_address_collection = Mage::getModel('sales/order_address')->getCollection();
                $original_shipping_address_collection->addAttributeToFilter('entity_id', array('in' => array($original_shipping_address_id)));
                $original_shipping_address_raw = $original_shipping_address_collection->toArray();
                $original_shipping_address = reset($original_shipping_address_raw['items']);

                $fields_that_count = array(
                    // 'region_id',
                    // 'region',
                    // 'postcode',
                    'street'
                );

                $similar_shippings_collection = Mage::getModel('sales/order_address')->getCollection();
                foreach($fields_that_count as $f) {
                    if($original_shipping_address[$f] === null)
                        $similar_shippings_collection->addAttributeToFilter($f, array('null' => ''));
					else
                        $similar_shippings_collection->addAttributeToFilter($f, trim($original_shipping_address[$f], " \r\n\t\0\x0B,"));
                }
                $similar_shippings_raw = $similar_shippings_collection->toArray();
                $similar_shippings = $similar_shippings_raw['items'];

                $similar_shippings_ids = array();
                foreach($similar_shippings as $s) {
                    $similar_shippings_ids[] = $s['entity_id'];
                }
				
                if(!in_array($original_shipping_address_id, $similar_shippings_ids))
                    $similar_shippings_ids[] = $original_shipping_address_id;

                $collection = Mage::getModel('sales/order')
                    ->getCollection()
                    ->addFieldToFilter('shipping_address_id', array('in' => $similar_shippings_ids))
                    ->addFieldToFilter('store_id', $store_id)
                    ->addFieldToFilter('state', array('in' => $order_statuses));
                // if($check_newer_orders) {
                //     $collection->addFieldToFilter('created_at', array('lteq' => $order['created_at']));
                // }
                $temp = $collection->toArray();
                $orders += $temp['items'];
            }
            //If there is 1 address
            if(count($orders) == 1) {
                $processed_orders[] = $single_order_id;
                continue;
            }

            $implode_arr = array();
            $implode_full_id_arr = array();
            foreach($orders as $o) {
                $implode_arr[]=  $o['entity_id'];
            }
            
            $order_list_combined = implode(",", $implode_arr);

            $order_collection = Mage::getModel('sales/order')
                                ->getCollection()
                                ->addFieldToFilter('entity_id', array('in' => $implode_arr))
                                ->addAttributeToSelect('entity_id')
                                ->addAttributeToSelect('increment_id')
                                ->addAttributeToSelect('status');
                            
            foreach($order_collection as $o_model) {
                $order_id = $o_model->getData('entity_id');
                $order_increment_id = $o_model->getData('increment_id');
                $implode_full_id_arr[]=$order_increment_id;
            }

            $order_list_combined_full = implode(",", $implode_full_id_arr);

            //Add combined list to order history
            foreach($order_collection as $o_model) {
                $order_id = $o_model->getData('entity_id');
                $order_increment_id = $o_model->getData('increment_id');
                
                $printed_orders[$order_id] = $order_id;
                $temp_order_list_combined = str_replace($order_increment_id,'',$order_list_combined_full);
                $temp_order_list_combined = str_replace(',,',',',$temp_order_list_combined);
                $temp_order_list_combined = ltrim($temp_order_list_combined,',');
                $temp_order_list_combined = rtrim($temp_order_list_combined,',');

                //Remove old rows of this order in db
                $order_printgroup = Mage::getModel('pickpack/printgroup')->getCollection()->addFieldToFilter('order_increment_id',$order_increment_id);
                if(count($order_printgroup) > 0) {
                    foreach($order_printgroup as $temp_order) {
                        $temp_order->delete();
                    }
                }

                //Save list to database
                $order_printgroup = Mage::getModel('pickpack/printgroup');
                $order_printgroup->setData('order_increment_id',$order_increment_id);
                $order_printgroup->setData('orderid',$order_id);
                $order_printgroup->setData('combined_list',$temp_order_list_combined);
                $order_printgroup->save();
                //Save to order history
                $o_model->addStatusToHistory($o_model->getStatus(),'Combined list: '.$temp_order_list_combined, false);
                $o_model->save();
                $processed_orders[] = $order_id;
            }
            // foreach($implode_arr as $order_id){
            //     // $new_order_model = Mage::getModel('sales/order')->load($order_id)
            //     $printed_orders[$order_id] = $order_id;
            //     $temp_order_list_combined = str_replace($order_id,'',$order_list_combined);
            //     $temp_order_list_combined = str_replace(',,',',',$temp_order_list_combined);
            //     $temp_order_list_combined = ltrim($temp_order_list_combined,',');
            //     $temp_order_list_combined = rtrim($temp_order_list_combined,',');
            //     $order_printgroup = Mage::getModel('pickpack/printgroup');
            //     $order_printgroup->setData('orderid',$order_id);
            //     $order_printgroup->setData('combined_list',$temp_order_list_combined);
            //     $order_printgroup->save();
            // }
        }

        return $processed_orders;
    }

    public function get3digitcountry($country_id) {
        $country_list = "AA-AAA|AD-AND|AE-ARE|AF-AFG|AG-ATG|AI-AIA|AL-ALB|AM-ARM|AN-ANT|AO-AGO|AQ-ATA|AR-ARG|AS-ASM|AT-AUT|AU-AUS|AW-ABW|AX-ALA|AZ-AZE|BA-BIH|BB-BRB|BD-BGD|BE-BEL|BF-BFA|BG-BGR|BH-BHR|BI-BDI|BJ-BEN|BL-BLM|BM-BMU|BN-BRN|BO-BOL|BR-BRA|BS-BHS|BT-BTN|BU-BUR|BV-BVT|BW-BWA|BY-BLR|BZ-BLZ|CA-CAN|CC-CCK|CD-COD|CF-CAF|CG-COG|CH-CHE|CI-CIV|CK-COK|CL-CHL|CM-CMR|CN-CHN|CO-COL|CR-CRI|CS-SCG|CU-CUB|CV-CPV|CX-CXR|CY-CYP|CZ-CZE|DD-DDR|DE-DEU|DJ-DJI|DK-DNK|DM-DMA|DO-DOM|DZ-DZA|EC-ECU|EE-EST|EG-EGY|EH-ESH|ER-ERI|ES-ESP|ET-ETH|FI-FIN|FJ-FJI|FK-FLK|FM-FSM|FO-FRO|FR-FRA|FX-FXX|GA-GAB|GB-GBR|GD-GRD|GE-GEO|GF-GUF|GG-GGY|GH-GHA|GI-GIB|GL-GRL|GM-GMB|GN-GIN|GP-GLP|GQ-GNQ|GR-GRC|GS-SGS|GT-GTM|GU-GUM|GW-GNB|GY-GUY|HK-HKG|HM-HMD|HN-HND|HR-HRV|HT-HTI|HU-HUN|ID-IDN|IE-IRL|IL-ISR|IM-IMN|IN-IND|IO-IOT|IQ-IRQ|IR-IRN|IS-ISL|IT-ITA|JE-JEY|JM-JAM|JO-JOR|JP-JPN|KE-KEN|KG-KGZ|KH-KHM|KI-KIR|KM-COM|KN-KNA|KP-PRK|KR-KOR|KW-KWT|KY-CYM|KZ-KAZ|LA-LAO|LB-LBN|LC-LCA|LI-LIE|LK-LKA|LR-LBR|LS-LSO|LT-LTU|LU-LUX|LV-LVA|LY-LBY|MA-MAR|MC-MCO|MD-MDA|ME-MNE|MG-MDG|MF-MAF|MH-MHL|MK-MKD|ML-MLI|MM-MMR|MN-MNG|MO-MAC|MP-MNP|MQ-MTQ|MR-MRT|MS-MSR|MT-MLT|MU-MUS|MV-MDV|MW-MWI|MX-MEX|MY-MYS|MZ-MOZ|NA-NAM|NC-NCL|NE-NER|NF-NFK|NG-NGA|NI-NIC|NL-NLD|NO-NOR|NP-NPL|NR-NRU|NT-NTZ|NU-NIU|NZ-NZL|OM-OMN|PA-PAN|PE-PER|PF-PYF|PG-PNG|PH-PHL|PK-PAK|PL-POL|PM-SPM|PN-PCN|PR-PRI|PS-PSE|PT-PRT|PW-PLW|PY-PRY|QA-QAT|QM-QMM|QN-QNN|QO-QOO|QP-QPP|QQ-QQQ|QR-QRR|QS-QSS|QT-QTT|QU-QUU|QV-QVV|QW-QWW|QX-QXX|QY-QYY|QZ-QZZ|RE-REU|RO-ROU|RS-SRB|RU-RUS|RW-RWA|SA-SAU|SB-SLB|SC-SYC|SD-SDN|SE-SWE|SG-SGP|SH-SHN|SI-SVN|SJ-SJM|SK-SVK|SL-SLE|SM-SMR|SN-SEN|SO-SOM|SR-SUR|ST-STP|SU-SUN|SV-SLV|SY-SYR|SZ-SWZ|TC-TCA|TD-TCD|TF-ATF|TG-TGO|TH-THA|TJ-TJK|TK-TKL|TL-TLS|TM-TKM|TN-TUN|TO-TON|TP-TMP|TR-TUR|TT-TTO|TV-TUV|TW-TWN|TZ-TZA|UA-UKR|UG-UGA|UM-UMI|US-USA|UY-URY|UZ-UZB|VA-VAT|VC-VCT|VE-VEN|VG-VGB|VI-VIR|VN-VNM|VU-VUT|WF-WLF|WS-WSM|XA-XAA|XB-XBB|XC-XCC|XD-XDD|XE-XEE|XF-XFF|XG-XGG|XH-XHH|XI-XII|XJ-XJJ|XK-XKK|XL-XLL|XM-XMM|XN-XNN|XO-XOO|XP-XPP|XQ-XQQ|XR-XRR|XS-XSS|XT-XTT|XU-XUU|XV-XVV|XW-XWW|XX-XXX|XY-XYY|XZ-XZZ|YD-YMD|YE-YEM|YT-MYT|YU-YUG|ZA-ZAF|ZM-ZMB|ZR-ZAR|ZW-ZWE|ZZ-ZZZ";
        $temp_country_arr = explode('|', $country_list);
        $country_arr = array();
        foreach($temp_country_arr as $row) {
            $temp_arr = explode('-',$row);
            $country_arr[$temp_arr[0]]=$temp_arr[1];
            if($country_id == $temp_arr[0])
                return $temp_arr[1];
        }
        if(isset($country_arr[$country_id]))
            return $country_arr[$country_id];
        else
            return 'Invalid country id';
    }

    public function getConnectorLabelTmpFile($label) {
        $tmp = Mage::getBaseDir('tmp');
        $tmpFile = $tmp . DS . microtime() . '.png';
        file_put_contents ( $tmpFile ,$label );
        //$height = $imageObj->getPixelHeight();
        //$width = $imageObj->getPixelWidth();

        return $tmpFile;
    }

    public function getProductAttributeValue($product, $attribute_code, $preprocess = true) {
        $return_value ='';
        try {
            if (is_object($product) && !is_null($product) && $attributeValue = $product->getData($attribute_code)) {
                $attribute = $product->getResource()->getAttribute($attribute_code);
                if ($attribute->usesSource())
                    $return_value = $product->getAttributeText($attribute_code, $attributeValue);
				else
                    $return_value = $attributeValue;
            }

            return $return_value;
        }
        catch(Exception $e) {
            Mage::logException($e);
            return '';
        }
    }

    public function getOrderAttributeValue($order, $wonder, $storeId) {
        $order_attribute_value = $order->getData("declaration_percentage");
        $prices_yn = Mage::helper('pickpack/config')->getConfig('prices_yn', 0, false, $wonder, $storeId);
        $multi_prices_yn = Mage::helper('pickpack/config')->getConfig('multi_prices_yn', 0, false, $wonder, $storeId);
        if (Mage::helper('pickpack')->isInstalled('Amasty_Orderattr')) {
            if($prices_yn && $multi_prices_yn == 1) {
                $attributeCode = Mage::helper('pickpack/config')->getConfig('multiplier_attribute', '', false, $wonder, $storeId);
                $orderAttributes = Mage::getModel('amorderattr/attribute')->load($order->getId(), 'order_id');
                $attribute = Mage::getModel('eav/entity_attribute')->loadByCode("order", $attributeCode);
                try {
                    $options = $attribute->getSource()->getAllOptions(true, true);
                }
                catch (Exception $e) {
                };
                $value = '';
                if(isset($options) && is_array($options)) {
                    foreach ($options as $option) {
                        if ($option['value'] == $orderAttributes->getData($attributeCode)){
                            $value = $option['label'];
                            break;
                        }
                    }
                }
                $order_attribute_value = $value;
            }
        }

        return $order_attribute_value;
    }

    public function isMageEnterprise() {
        if (version_compare(Mage::getVersion(), '1.7.0.0') >= 0) 
			return Mage::getEdition() == Mage::EDITION_ENTERPRISE ? true : false;
        else
			return Mage::getConfig ()->getModuleConfig ( 'Enterprise_Enterprise' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_AdminGws' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Checkout' ) && Mage::getConfig ()->getModuleConfig ( 'Enterprise_Customer' );
    }
}
