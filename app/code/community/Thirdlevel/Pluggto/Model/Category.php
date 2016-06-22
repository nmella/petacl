<?php

class Thirdlevel_Pluggto_Model_Category extends Mage_Core_Model_Abstract
{

    public $rootCategory;


    public function _construct(){

        $this->_init("pluggto/category");
    }

    public function getCategoryModel(){

       return Mage::getModel('catalog/category');
    }


    // find base category
    public function getBaseRoot(){


            if ($this->rootCategory != null && $this->rootCategory != '' ){
                return $this->rootCategory;
            }

            $root = $this->getStoreCategoryByName(Mage::app()->getStore()->getName());

            if($root == null){
                $this->rootCategory = $this->CreateBaseRoot();
                return $this->rootCategory;
            } else {
                $this->rootCategory = $root;
                return $this->rootCategory;
            }

    }


    // create base category
    public function CreateBaseRoot(){

        $category = $this->getCategoryModel();
        $category->setStoreId(Mage::app()->getStore()->getId());

        $cat['name'] = Mage::app()->getStore()->getName();
        $cat['path'] = '1'; //parent relationship..
        $cat['is_active'] = 0;
        $cat['is_anchor'] = 0; //for layered navigation
        $category->addData($cat);
        $rootcat = $category->save();
        return $rootcat->getEntityId();

    }

    public function getCategoryByNameOrNew($category){

        $cat = $this->getStoreCategoryByName($category['name']);

        if($cat){
            return $cat;
        }


        $allpaths = explode('>',$category['name']);

        $pathsArray = array(1);

        foreach ($allpaths as $key => $path){

            if($key==0){
                $id = $this->createCategory($path,'1');
            } else {
                $id = $this->createCategory($path,implode('/',$pathsArray));
            }

            $pathsArray[] = $id;
        }

        return $pathsArray;

    }

    public function createCategory($name,$path){

        $id = $this->getStoreCategoryByName($name);
        if($id){
            return $id;
        }

        $categoryModel = Mage::getModel('catalog/category');
        $categoryModel->setPath($path);
        $categoryModel->setName($name);
        $categoryModel->setStoreId(Mage::app()->getStore()->getId());
        $categoryModel->setIsActive(1);
        $categoryModel->save();

        return $categoryModel->getEntityId();

    }


    // find if category id is already register at store, if yes
    public function getStoreCategoryByName($category){


        $storecat =  Mage::getModel('catalog/category')->getCollection()->addFieldToFilter('name',$category)->getFirstItem();
        $id = $storecat->getEntityId();

        if($id){
            return $storecat->getEntityId();
        } else {
            return $id;
        }
    }

    public function getCategoryById($id){
        $categoryModel = $this->getCategoryModel();
        return $categoryModel->load($id);
    }

	
}
