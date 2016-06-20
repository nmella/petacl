<?php
/**
 * 
 * Date: 20.12.15
 * Time: 11:10
 */

class Moogento_Pickpack_Helper_Gift extends Mage_Core_Helper_Abstract
{
    protected $_giftWrapInfoCache = array();

    public function getOrderGiftMessage($gift_message_id, $gift_message_yn, $gift_message_item, $giftWrap_info, $gift_message_array = array()) {
        // Add order gift message with gift wrap info
        $gift_message_info = array();
        $gift_message = '';
        $gift_sender = '';
        $gift_recipient = '';
       
	    if ($gift_message_yn != 'no' && !is_null($gift_message_id)) {
            // normal gift message
            $gift_message_item->load((int)$gift_message_id);
            $gift_sender = $gift_message_item->getData('sender');
            $gift_recipient = $gift_message_item->getData('recipient');
            $gift_message = $gift_message_item->getData('message');
        }

        if (isset($giftWrap_info['message']) && $giftWrap_info['message'] != NULL) {
            if ($gift_message != '')
				$gift_message .= "\n";
            $gift_message .= $giftWrap_info['message'];
        }

        // add product gift message and history ebay note to order message
        $gift_message_combined = '';
        if(isset($gift_message_array['notes'])) {
            foreach ($gift_message_array['notes'] as $k => $v) {
                $gift_message.='\n'.$v;
            }
		}

        if(isset($gift_message_array['items'])) {
            foreach ($gift_message_array['items'] as $item_key => $item_message) {
                if(isset($item_message['printed'])) {
                    if($item_message['printed'] == 0) {   
						if(is_array($item_message['message-content'])) {
	                        foreach($item_message['message-content'] as $k2=>$v2) {
	                            $gift_message.="\n".$v2;
							}
						}
                    }
                }
            }
		}
		
        $gift_message_info[0] = $gift_message;
        $gift_message_info[1] = $gift_sender;
        $gift_message_info[2] = $gift_recipient;
        return $gift_message_info;
    }

    public function getGiftWrapInfo($order, $wonder) {
       
	    if (isset($this->_giftWrapInfoCache[$order->getId()]))
            return $this->_giftWrapInfoCache[$order->getId()];

        /*************************** GIFTWRAP MESSAGE*******************************/
        $giftWrap_info = array(
            'wrapping_paper' => null,
            'message' => null,
            'wrapping_image' => null,
            'giftcard_image' => null,
            'giftcard_name' => null,
            'per_item' => array(),
        );

        $giftwrap_style_yn = Mage::helper('pickpack/config')->getConfig('gift_wrap_style_yn', 'yesshipping', false, $wonder, $order->getStore()->getId());

        if (Mage::helper('pickpack')->isInstalled('Magestore_Giftwrap')) {
            $giftWrapCollection = Mage::getModel('giftwrap/selection')->getCollection()
                ->addFieldToFilter('order_id', $order->getId());
            $giftWrap_info['per_item'] = array();
            
			foreach ($giftWrapCollection as $selection) {
                $giftWrap_info['message'] .= "\n" . $selection->getData('message');
                $style_gift = Mage::getModel('giftwrap/giftwrap')->load($selection->getData('style_id'));
                $giftCard = Mage::getModel('giftwrap/giftcard')->load($selection->getData('giftcard_id'));
                
				if ($style_gift->getImage())
                    $giftWrap_info['wrapping_image'] = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/giftwrap/' . $style_gift->getImage();
            
                if ($selection->getData('giftcard_id')) {
                    if ($giftCard->getImage())
                        $giftWrap_info['giftcard_image'] = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/giftwrap/giftcard/' . $giftCard->getImage();
                    $giftWrap_info['giftcard_name'] = $giftCard->getName();
                }

                if ($giftwrap_style_yn == 'yesbox')
                    $giftWrap_info['wrapping_paper'] .= $style_gift->getData('title');
                else {
                    if ($giftwrap_style_yn == 'yesshipping')
                        $giftWrap_info['style'] .= $style_gift->getData('title');
                }

                $items = Mage::getModel('giftwrap/selectionitem')->getCollection()
                    ->addFieldToFilter('selection_id', $selection->getId());
                foreach ($items as $itemData) {
                    $orderItem = Mage::getResourceModel('sales/order_item_collection')->addFieldToFilter('quote_item_id', $itemData->getItemId())->getFirstItem();
                    $giftWrap_info['per_item'][$orderItem->getId()] = array(
                        'message' => $selection->getData('message'),
                        'wrapping_image' => $style_gift->getImage() ? Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/giftwrap/' . $style_gift->getImage() : '',
                        'wrapping_paper' => $style_gift->getData('title'),
                    );
                   
				    if ($selection->getData('giftcard_id')) {
                        $giftWrap_info['per_item'][$orderItem->getId()]['giftcard_image'] = $giftCard->getImage() ?  Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . '/giftwrap/giftcard/' . $giftCard->getImage() : '';
                        $giftWrap_info['per_item'][$orderItem->getId()]['giftcard_name'] = $giftCard->getName();
                    }
                }
            }
        }
        elseif (Mage::helper('pickpack')->isInstalled('Xmage_GiftWrap') && (Mage::getModel('giftwrap/order'))) {
            $orderId = $order->getId();
            $giftWrapInfos = Mage::getModel('giftwrap/order')->getCollection()->addFieldToFilter('order_id', $orderId);
            foreach ($giftWrapInfos as $info) {
                $giftWrap_info['message'] .= $info->getData('message');
               
			    if (isset($giftWrap_info['wrapping_paper']))
					$giftWrap_info['wrapping_paper'] .= ' | ';
                $giftWrap_info['wrapping_paper'] .= trim(str_ireplace(array('xmage_giftwrap/', '.jpg', '.jpeg', '.gif', '.png'), '', $info->getData('giftbox_image')));
            }
        }

        $this->_giftWrapInfoCache[$order->getId()] = $giftWrap_info;
        return $giftWrap_info;
    }

    public function createMsgArray($gift_message) {
        $gift_message = wordwrap($gift_message, 96, "\n", false);
        $gift_msg_array = array();
        // wordwrap characters
        $token = strtok($gift_message, "\n");
        // $y = 740;
        $msg_line_count = 2.5;
        while ($token != false) {
            $gift_msg_array[] = $token;
            $msg_line_count++;
            $token = strtok("\n");
        }
        return $gift_msg_array;
    }
}
