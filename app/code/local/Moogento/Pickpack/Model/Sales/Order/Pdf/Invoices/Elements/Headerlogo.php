<?php
/**
 *
 * Date: 09.12.15
 * Time: 12:23
 */

class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Headerlogo extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Elements_Abstract
{
    public $generalConfig = array();
    public $packingsheetConfig = array();

    const FULL_WIDTH_LOGO_DEFAULT_POSITION = "0,0";

    public function getLogoPosition() {
        if(!$this->hasData('logo_position')) {
            $this->setData('logo_position', $this->_getConfig('pickpack_logo_position', 'left', false, $this->getWonder(), $this->getStoreId()));
        }
        return $this->getData('logo_position');
    }

    public function calculateMaxPrintSize() {
        $pageConfig = $this->getPageConfig();
        $pagePad = $this->packingsheetConfig['page_pad'];
        $maxPrintSize['width'] = $pageConfig['full_page_width'] - (2 * $pagePad[0]);
        $maxPrintSize['height'] = $pageConfig['full_page_height'] - $pagePad[1] - $pagePad[2];

        return $maxPrintSize;
    }

    public function showLogo() {
        $storeId = $this->getStoreId();
        $pageConfig = $this->getPageConfig();
        $page = $this->getPage();
        $wonder = $this->getWonder();
        $letterhead_yn = $this->_getConfig('letterhead', 1, false, $wonder, $storeId);
        if($letterhead_yn == 0)
            $show_top_logo_yn = 0;
        else
            $show_top_logo_yn = $this->_getConfig('pickpack_packlogo', 0, false, $wonder, $storeId);

        $suffix_group = '/pack_logo';
        if ($wonder == 'wonder')
            $sub_folder = 'logo_pack';
        else
            $sub_folder = 'logo_invoice';

        $image_simple = new SimpleImage();
        $logoMaxDimensions = explode(',', $pageConfig['logo_maxdimensions']);


        /***************************PRINTING 1 HEADER LOGO *******************************/
        if ($show_top_logo_yn == 1) {
            /*************************** PRINT HEADER LOGO *******************************/
            $packlogo_filename = Mage::getStoreConfig('pickpack_options/' . $wonder . $suffix_group, $storeId);
            $helper = Mage::helper('pickpack');
            if ($packlogo_filename) {
                $packlogo_path = Mage::getBaseDir('media') . '/moogento/pickpack/' . $sub_folder . '/' . $packlogo_filename;
                if (is_file($packlogo_path)) {
                    $imageObj = $helper->getImageObj($packlogo_path);
                    $orig_img_width = $imageObj->getOriginalWidth();
                    $orig_img_height = $imageObj->getOriginalHeight();

                    $image_ext = '';
                    $temp_array_image = explode('.', $packlogo_path);
                    $option_group_folder = str_replace('/','',$wonder);
                    $suffix_group_folder = str_replace('/','',$suffix_group);

                    $image_ext = array_pop($temp_array_image);
                    if (($image_ext == 'jpg') || ($image_ext == 'jpeg') || ($image_ext == 'png')) {
                        if(isset($image_simple)) {
                            $final_image_path2 = $packlogo_path;
                            $image_source = $final_image_path2;
                            $io = new Varien_Io_File();
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage');
                            $io->checkAndCreateFolder(Mage::getBaseDir('var').DS.'moogento'.DS.'pickpack'.DS.'tempimage'.DS.$option_group_folder.DS.$suffix_group_folder.DS.'default');
                            $ext = substr($image_source, strrpos($image_source, '.') + 1);
                            $image_source = $final_image_path2;
                            $packlogo_filename = str_replace($ext,'jpeg', $packlogo_filename);
                            $image_target= Mage::getBaseDir('var') . '/moogento/pickpack/tempimage/'.$option_group_folder.'/'. $suffix_group_folder.'/'.$packlogo_filename;
                            if (!file_exists(dirname($image_target))) {
                                mkdir(dirname($image_target), 0777, true);
                            }

                            $image_max_print_size = $this->calculateMaxPrintSize();

                            /*************************** CACULATE RESIZE IMAGE TO FIT WITH PAGE *******************************/

                            if ($orig_img_width > ($image_max_print_size['width'] * 300 / 70)) {
                                $resize_height = round( $orig_img_height * ( $image_max_print_size['width']  * 300 / 70 ) / $orig_img_width);
                                $resize_width = $image_max_print_size['width'];

                                //save resize image
                                $image_simple->load($image_source);
                                $image_simple->resize($resize_width,$resize_height);
                                $image_simple->save($image_target);
                                $packlogo_path = $image_target;
                                $img_print_size_width = round($resize_width * 70 / 300);
                                $img_print_size_height = round($resize_height * 70 / 300);
                            }else{
                                $img_print_size_width = round($orig_img_width * 70 / 300);
                                $img_print_size_height = round($orig_img_height * 70 / 300);
                            }

                            $print_position = $this->caculatePrintPosition($img_print_size_width,$img_print_size_height);
                        }
                        $packlogo = Zend_Pdf_Image::imageWithPath($packlogo_path);
                        $page->drawImage($packlogo, $print_position['x1'], $print_position['y1'], $print_position['x2'], $print_position['y2']);

                        unset($packlogo);
                        unset($packlogo_filename);
                        unset($packlogo_path);
                    }
                }
            }

            $this->setX1($print_position['x1']);
            $this->setY1($print_position['y1'] - 20);
            $this->setX2($print_position['x2']);
            $this->setY2($print_position['y2']);

            /*************************** END PRINT HEADER LOGO ***************************/
        }
    }

    protected function caculatePrintPosition($img_print_width,$img_print_height){
        $pageConfig = $this->getPageConfig();
        $print_position = array();
        if ($this->packingsheetConfig['pickpack_logo_position'] == 'left'){
            $print_position['x1'] = $pageConfig['padded_left'] + $this->packingsheetConfig['page_logo_nudge'][0];
            $print_position['y1'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1] - $img_print_height;
            $print_position['x2'] = $pageConfig['padded_left'] + $this->packingsheetConfig['page_logo_nudge'][0] + $img_print_width;
            $print_position['y2'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1];
        }elseif ($this->packingsheetConfig['pickpack_logo_position'] == 'right'){
            $print_position['x1'] = $pageConfig['padded_right'] + $this->packingsheetConfig['page_logo_nudge'][0] - $img_print_width;
            $print_position['y1'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1] - $img_print_height;
            $print_position['x2'] = $pageConfig['padded_right'] + $this->packingsheetConfig['page_logo_nudge'][0];
            $print_position['y2'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1];
        }elseif ($this->packingsheetConfig['pickpack_logo_position'] == 'fullwidth'){
            $print_position['x1'] = $pageConfig['padded_left'] + $this->packingsheetConfig['page_logo_nudge'][0];
            $print_position['y1'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1] - $img_print_height;
            $print_position['x2'] = $pageConfig['padded_right'] + $this->packingsheetConfig['page_logo_nudge'][0];
            $print_position['y2'] = $pageConfig['page_top'] + $this->packingsheetConfig['page_logo_nudge'][1];
        }
        return $print_position;
    }
}