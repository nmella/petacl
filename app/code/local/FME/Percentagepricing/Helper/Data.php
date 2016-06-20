<?php

class FME_Percentagepricing_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getcondition_rule($identifier = '')
    {
        $resource = Mage::getSingleton('core/resource');
        $read= $resource->getConnection('core_read');
        $addproducttocmsTable = $resource->getTableName('percentagepricing');	
        $qry = "select percentagepricing_rule FROM ".$addproducttocmsTable." where  percentagepricing_id ='".$identifier."'"; 
        $rest = $read->fetchRow($qry);
        return $rest['percentagepricing_rule'];
    }
    /*
    public function  getcateids_byname($identifier = '')
    {
        $resource = Mage::getSingleton('core/resource');
        $read= $resource->getConnection('core_read');
        $addproducttocmsTable = $resource->getTableName('addproducttocms');	
        $qry = "select categories FROM ".$addproducttocmsTable." where  identifier ='".$identifier."'"; 
        $rest = $read->fetchRow($qry);
        return $rest['categories'];
    }
    */
  
    public function getcate_pages($page_id)
    {
      
        $resource = Mage::getSingleton('core/resource');
		$addproducttocmsTable = $resource->getTableName('percentagepricing');
        $rows = array();
        $rule = Mage::helper('percentagepricing')->getcondition_rule($page_id);
        $conditions = unserialize($rule);

        $unserialized_conditions_compact = array();
        foreach($conditions as $key => $value)
        {
            $unserialized_conditions_compact[] = compact('key', 'value');
        }

        foreach($unserialized_conditions_compact['0']['value']['css'] as $new_page)
        {
            $row['attribute'][] = $new_page['attribute'];
            $row['value'][] = $new_page['value'];
        }

 
        $result = array();
        foreach ($row['attribute'] as $i => $key)
        {
            $value = str_replace(' ', '', $row['value'][$i]);
            if (isset($result[$key]))
                $result[$key] = implode(',', array_unique(array_merge(explode(',', $result[$key]), explode(',', $value))));
            else
                $result[$key] = $value;
        }
        return $result;
    }
    public function convertFlatToRecursive(array $rule, $keys)
	{
        $arr = array();
        foreach ($rule as $key => $value) {
            if (in_array($key, $keys) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = & $arr;
                    for ($i = 0, $l = sizeof($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = array();
                        }
                        $node = & $node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            }
			else {
                if (in_array($key, array('from_date', 'to_date')) && $value) {
                    $value = Mage::app()->getLocale()->date(
                            $value, Varien_Date::DATE_INTERNAL_FORMAT, null, false
                    );
                }
            }
        }

        return $arr;
    }
    
    public function updateChild($array, $from, $to)
	{
        foreach ($array as $k => $rule) {
            foreach ($rule as $name => $param) {
                if ($name == 'type' && $param == $from)
                    $array[$k][$name] = $to;
            }
        }
        return $array;
    }
    
    

	public function checkVersion($version, $operator = '>=')
	{
        return version_compare(Mage::getVersion(), $version, $operator);
    }
    

}