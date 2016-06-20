<?php
class FME_Percentagepricing_Block_Product_Listcategories extends Mage_Catalog_Block_Product_List{
  
         private $_itemPerPage;
         private $_curPage = 1;
//same function from Mage_Catalog_Block_Product_List


protected function _getProductCollection(){
 

 $cpBlock = $this->getLayout()->getBlockSingleton('Mage_Catalog_Block_Product_List_Toolbar');
//echo //getCurrentMode();

if( $_REQUEST['mode']!='grid' ) {

      
       $this->_itemPerPage = Mage::getStoreConfig('catalog/frontend/list_per_page_values');
  
   
}
else {
    $this->_itemPerPage = Mage::getStoreConfig('catalog/frontend/grid_per_page');
} 

if($_REQUEST['limit']!='')
{
 
  $this->_itemPerPage = $_REQUEST['limit'];
 
}
if( $_REQUEST['mode']=='grid' && $_REQUEST['limit']!='') {
 

  if(!in_array($this->_itemPerPage,explode(",",Mage::getStoreConfig('catalog/frontend/grid_per_page_values'))))
     {
      
        $new_size =  explode(",",Mage::getStoreConfig('catalog/frontend/grid_per_page_values'));
      $sizes = $new_size[0];
      $this->_itemPerPage =  $sizes; 
     }

}else
{
 if(!in_array($this->_itemPerPage,explode(",",Mage::getStoreConfig('catalog/frontend/list_per_page_values'))))
     {
      
        $new_size =  explode(",",Mage::getStoreConfig('catalog/frontend/list_per_page_values'));
      $sizes = $new_size[0];
      $this->_itemPerPage =  $cpBlock->getLimit(); 
     }

}

if( $_REQUEST['mode']!='grid' && $_REQUEST['limit']=='') {
 
  
      $this->_itemPerPage =  $cpBlock->getLimit(); 
     

}

$page_id =  $this->getRequest()->getParam('id');

$result = Mage::helper('percentagepricing')->getcate_pages($page_id);



$this->_productCollection = Mage::getModel('catalog/product')->getCollection();
$this->_productCollection->addAttributeToSelect('*');
if(count($result) > 0)
{
foreach($result as $key => $val)
{
 if($key == 'category_ids')
 {
  $cate = $val;
   }else
   {
 $val = explode(',',$val);
  $this->_productCollection->addAttributeToFilter($key, array('in' => $val));
   }
 // $this->_productCollection->addFieldToFilter($key,$val);
 
}
//$newcates = '';
//
//if(count($cate) > 0)
//{
// $newcates = $this->getCategories().",".$cate; 
// 
//}else
//{
// $newcates = $this->getCategories();
//}

if(count($cate) > 0)
$this->_productCollection->addCategoriesFilter($cate);

}
$this->_productCollection->addAttributeToSort($_REQUEST['order'],$_REQUEST['dir']);
//echo $this->_productCollection->getSelect()->__toString();
$page = $this->getRequest()->getParam('p');
                    if($page) $this->_curPage = $page;
                  
                    $this->_productCollection->setCurPage($this->_curPage);
                   $this->_productCollection->setPageSize($this->_itemPerPage);
//$this->_productCollection->setPageSize($_REQUEST['limit']);
//echo $this->_productCollection->getSelect()->__toString();
return $this->_productCollection;

}
 public function getPagerHtml($collection = 'null')
            {   
                $html = false;
                if($collection == 'null') return;
                if($collection->count() > $this->_itemPerPage)
                {
                    $curPage = $this->getRequest()->getParam('p');
                    $pager = (int)($collection->count() / $this->_itemPerPage);
                    $count = ($collection->count() % $this->_itemPerPage == 0) ? $pager : $pager + 1 ;
                    $url = $this->getPagerUrl();
                    $start = 1;
                    $end = $this->_pageFrame;
                   
                    $html .= '<ol>';
                    if(isset($curPage) && $curPage != 1){
                        $start = $curPage - 1;                                       
                        $end = $start + $this->_pageFrame;
                    }else{
                        $end = $start + $this->_pageFrame;
                    }
                    if($end > $count){
                        $start = $count - ($this->_pageFrame-1);
                    }else{
                        $count = $end-1;
                    }
                   
                    for($i = $start; $i<=$count; $i++)
                    {
                        if($i >= 1){
                            if($curPage){
                                $html .= ($curPage == $i) ? '<li class="current">'. $i .'</li>' : '<li><a href="'.$url.'&p='.$i.'">'. $i .'</a></li>';
                            }else{
                                $html .= ($i == 1) ? '<li class="current">'. $i .'</li>' : '<li><a href="'.$url.'&p='.$i.'">'. $i .'</a></li>';
                            }
                        }
                       
                    }
                   
                    $html .= '</ol>';
                }
               
                return $html;
            }
           
            public function getPagerUrl()                            // You need to change this function as per your url.
            {
                $cur_url = mage::helper('core/url')->getCurrentUrl();
                $new_url = preg_replace('/\&p=.*/', '', $cur_url);
               
                return $new_url;
            }
            
     
}