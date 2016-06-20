<?php

/**
 * @author     Kristof Ringleff
 * @package    Fooman_OrderManager
 * @copyright  Copyright (c) 2009 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_ShippingAddress
    extends Fooman_OrderManager_Block_Adminhtml_Widget_Grid_Column_Renderer_AbstractAddress
{
    protected $_address = 'shipping_address';
    protected $_addressDescription = 'Shipping Address';
    protected $fieldPrefix = 'shipping_';
}
