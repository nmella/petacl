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
* File        Csvorders.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 
class Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices_Csvexportimport extends Moogento_Pickpack_Model_Sales_Order_Pdf_Invoices{

	public function getCsvexport($data){
		$csv_output = '';
		$field_quotes = '"';
		$separator = ",";
		$header_column = '';
		$header = "config_id" . "," . "scope" . "," . "scope_id" . "," . "path" . "," . "value";
		
		$columns = explode(",", $header);
		foreach ($columns as $key => $value) {
			$header_column .= $field_quotes . $value . $field_quotes . $separator;
		}
		$header_column .= "\n";
		$csv_output = $header_column;
		foreach ($data as $key => $row) {

			if (isset($row['value']) && !is_null($row['value'])){
				$value = $row['value'];
				$value = preg_replace( "/\r/", "&RNewLine;", $value );//break line of MAC osx9
				$value = preg_replace( "/\n/", "&NewLine;", $value );//break line of Windows Linux
				$value = preg_replace( "/\"/", "&quot;", $value );
				$value = preg_replace( "/\,/", "&comma;", $value );
				$value = preg_replace( "/ /", "&nbsp;", $value );//space
				$row['value'] = $value;
			}

			foreach ($columns as $key => $value) {
				$value = trim($value);
				$csv_output .= $field_quotes . $row[$value] . $field_quotes . $separator;
			}
			$csv_output .= "\n";
		}
		
		return $csv_output;
	}

	public function importConfigPickpack($data){

		$error_mes = '';

		try {
			$data_array = array();
			foreach ($data as $key => $value) {
				$value = trim($value, '"');
				$data_array = explode("\n", $value);
				if($data_array != "")
					break;
			}
			$header = array();
			$data_array_after = array();
			$header = explode(",", trim($data_array[0], '"'));
			for ($i=1; $i<count($data_array); $i++) {
				$array_temp = array();
				$data_array_after[$i] = array();
				$array_temp = explode(",", trim($data_array[$i], '"'));
				for ($k = 0; $k<(count($header) - 1); $k ++) {
					$data_array_after[$i][trim($header[$k], '"')] = isset($array_temp[$k]) ? trim($array_temp[$k], '"') : "";
				}
			}
		} catch (Exception $e) {
			$error_mes = "Error when read file csv";
		}

		if ($error_mes == '')
			try {
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				$tableName1 = $resource->getTableName('core_config_data');

				$delete_core_config_data = 'DELETE FROM '.$tableName1.' WHERE path like "%pickpack_option%" AND path != "pickpack_options/moodetails/license"';
				$writeConnection->query($delete_core_config_data);
			} catch (Exception $e) {
				$error_mes = "Error when clear old config";
			}


		if ($error_mes == '')
			try {
				$model_config = new Mage_Core_Model_Config();
				foreach ($data_array_after as $key => $value) {
					if (isset($value['value']) && !is_null($value['value'])){
						$str_value = $value['value'];
						$str_value = preg_replace( "/&RNewLine;/", "\r", $str_value );//break line of MAC osx9
						$str_value = preg_replace( "/&NewLine;/", "\n", $str_value );//break line of Windows Linux
						$str_value = preg_replace( "/&quot;/", "\"", $str_value );
						$str_value = preg_replace( "/&comma;/", ",", $str_value );
						$str_value = preg_replace( "/&nbsp;/", " ", $str_value );//space
						$value['value'] = $str_value;
					}
					$model_config->saveConfig($value["path"], $value["value"], $value["scope"], $value["scope_id"]);
				}
			} catch (Exception $e) {
				$error_mes = "Error when applied config from csv file";
			}

		return $error_mes;
	}
}