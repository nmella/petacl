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
* File        php-export-data.class.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

// php-export-data by Eli Dickinson, http://github.com/elidickinson/php-export-data

/**
 * ExportData is the base class for exporters to specific file formats. See other
 * classes below.
 */
abstract class ExportData {
	protected $exportTo; // Set in constructor to one of 'browser', 'file', 'string'
	protected $stringData; // stringData so far, used if export string mode
	protected $tempFile; // handle to temp file (for export file mode)
	protected $tempFilename; // temp file name and path (for export file mode)

	public $filename; // file mode: the output file name; browser mode: file name for download; string mode: not used

	public function __construct($exportTo = "browser", $filename = "exportdata") {
		if(!in_array($exportTo, array('browser','file','string') )) {
			throw new Exception("$exportTo is not a valid ExportData export type");
		}
		$this->exportTo = $exportTo;
		$this->filename = $filename;
	}
	
	public function initialize() {
		
		switch($this->exportTo) {
			case 'browser':
				$this->sendHttpHeaders();
				break;
			case 'string':
				$this->stringData = '';
				break;
			case 'file':
				$this->tempFilename = tempnam(sys_get_temp_dir(), 'exportdata');
				$this->tempFile = fopen($this->tempFilename, "w");
				break;
		}
		
		$this->write($this->generateHeader());
	}
	
	public function addRow($row) {
		$this->write($this->generateRow($row));
	}
	
	public function finalize() {
		
		$this->write($this->generateFooter());
		
		switch($this->exportTo) {
			case 'browser':
				flush();
				break;
			case 'string':
				// do nothing
				break;
			case 'file':
				// close temp file and move it to correct location
				fclose($this->tempFile);
				rename($this->tempFilename, $this->filename);
				break;
		}
	}
	
	public function getString() {
		return $this->stringData;
	}
	
	abstract public function sendHttpHeaders();
	
	protected function write($data) {
		switch($this->exportTo) {
			case 'browser':
				echo $data;
				break;
			case 'string':
				$this->stringData .= $data;
				break;
			case 'file':
				fwrite($this->tempFile, $data);
				break;
		}
	}
	
	protected function generateHeader() {
		// can be overridden by subclass to return any data that goes at the top of the exported file
	}
	
	protected function generateFooter() {
		// can be overridden by subclass to return any data that goes at the bottom of the exported file		
	}
	
	// In subclasses generateRow will take $row array and return string of it formatted for export type
	abstract protected function generateRow($row);
	
}

/**
 * ExportDataTSV - Exports to TSV (tab separated value) format.
 */
class ExportDataTSV extends ExportData {
	
	function generateRow($row) {
		foreach ($row as $key => $value) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[$key] = '"'. str_replace('"', '\"', $value) .'"';
		}
		return implode("\t", $row) . "\n";
	}
	
	function sendHttpHeaders() {
		header("Content-type: text/tab-separated-values");
    	header("Content-Disposition: attachment; filename=".basename($this->filename));
	}
}

/**
 * ExportDataCSV - Exports to CSV (comma separated value) format.
 */
class ExportDataCSV extends ExportData {
	
	function generateRow($row) {
		foreach ($row as $key => $value) {
			// Escape inner quotes and wrap all contents in new quotes.
			// Note that we are using \" to escape double quote not ""
			$row[$key] = '"'. str_replace('"', '\"', $value) .'"';
		}
		return implode(",", $row) . "\n";
	}
	
	function sendHttpHeaders() {
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=".basename($this->filename));
	}
}


/**
 * ExportDataExcel exports data into an XML format  (spreadsheetML) that can be 
 * read by MS Excel 2003 and newer as well as OpenOffice
 * 
 * Creates a workbook with a single worksheet (title specified by
 * $title).
 * 
 * Note that using .XML is the "correct" file extension for these files, but it
 * generally isn't associated with Excel. Using .XLS is tempting, but Excel 2007 will
 * throw a scary warning that the extension doesn't match the file type.
 * 
 * Based on Excel XML code from Excel_XML (http://github.com/oliverschwarz/php-excel)
 *  by Oliver Schwarz
 */
class ExportDataExcel extends ExportData {
	
	const XmlHeader = "<?xml version=\"1.0\" encoding=\"%s\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
	const XmlFooter = "</Workbook>";
	
	public $encoding = 'UTF-8'; // encoding type to specify in file. 
	// Note that you're on your own for making sure your data is actually encoded to this encoding
	
	public $title = 'Shipping Manifest'; // title for Worksheet 
	
	function generateHeader() {
		
		// workbook header
		$output = stripslashes(sprintf(self::XmlHeader, $this->encoding)) . "\n";
		
		// Set up styles
		$output .= "<Styles>\n";
		$output .= "<Style ss:ID=\"sDT\"><NumberFormat ss:Format=\"Short Date\"/></Style>\n";
		$output .= "<Style ss:ID=\"b\"><Font ss:Bold=\"1\" /></Style>\n";
		$output .= "<Style ss:ID=\"u\"><Font ss:Underline=\"Single\" /></Style>\n";
		$output .= "<Style ss:ID=\"i\"><Font  ss:Italic=\"1\" /></Style>\n";
		$output .= "<Style ss:ID=\"ub\"><Font ss:Bold=\"1\" ss:Underline=\"Single\"/></Style>\n";
		$output .= "<Style ss:ID=\"ubi\"><Font ss:Bold=\"1\" ss:Italic=\"1\" ss:Underline=\"Single\"/></Style>\n";
		$output .= "<Style ss:ID=\"h1\"><Font ss:Bold=\"1\" ss:Size=\"16\"/></Style>\n";
		$output .= "<Style ss:ID=\"h2\"><Font ss:Bold=\"1\" ss:Size=\"12\"/></Style>\n";
		$output .= "<Style ss:ID=\"h1-i\"><Font ss:Bold=\"1\" ss:Italic=\"1\" ss:Size=\"16\"/></Style>\n";
		$output .= "<Style ss:ID=\"h2-i\"><Font ss:Bold=\"1\" ss:Italic=\"1\" ss:Size=\"12\"/></Style>\n";
		$output .= "<Style ss:ID=\"b-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Bold=\"1\" /></Style>\n";
		$output .= "<Style ss:ID=\"u-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Underline=\"Single\" /></Style>\n";
		$output .= "<Style ss:ID=\"i-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font  ss:Italic=\"1\" /></Style>\n";
		$output .= "<Style ss:ID=\"ub-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Bold=\"1\" ss:Underline=\"Single\"/></Style>\n";
		$output .= "<Style ss:ID=\"ubi-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Bold=\"1\" ss:Italic=\"1\" ss:Underline=\"Single\"/></Style>\n";
		$output .= "<Style ss:ID=\"h1-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Bold=\"1\" ss:Size=\"16\"/></Style>\n";
		$output .= "<Style ss:ID=\"h2-rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/><Font ss:Bold=\"1\" ss:Size=\"12\"/></Style>\n";
		$output .= "<Style ss:ID=\"right\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/></Style>\n";
		$output .= "<Style ss:ID=\"rt\"><Alignment ss:Horizontal=\"Right\" ss:Indent=\"0\"/></Style>\n";
		$output .= "<Style ss:ID=\"lt\"><Alignment ss:Horizontal=\"Left\" ss:Indent=\"0\"/></Style>\n";
		$output .= "<Style ss:ID=\"left\"><Alignment ss:Horizontal=\"Left\" ss:Indent=\"0\"/></Style>\n";
		
		$output .= "</Styles>\n";

		// worksheet header
		$output .= sprintf("<Worksheet ss:Name=\"%s\">\n    <Table>\n", htmlentities($this->title));
		
		return $output;
	}
	
	function generateFooter() {
		$output = '';
		
		// worksheet footer
		$output .= "    </Table>\n</Worksheet>\n";
		
		// workbook footer
		$output .= self::XmlFooter;
		
		return $output;
	}
	
	function generateRow($row) {
		$output = '';
		$output .= "<Row>";
		foreach ($row as $k => $v) {
			$output .= $this->generateCell($v);
		}
		$output .= "</Row>\n";	
		$output = str_replace(array("\r</Data>","\n</Data>"),'</Data>',$output);
	
		return $output;
	}
	
	private function generateCell($item) {
		$output 	= '';
		$style 		= '';
		$merge_txt 	= '';
		
		if(preg_match('~\{b\}~',$item)) {
			$style = 'b';
			$item = str_replace('{b}','',$item);
		}
		elseif(preg_match('~\{u\}~',$item)) {
			$style = 'u';
			$item = str_replace('{u}','',$item);
		}
		elseif(preg_match('~\{i\}~',$item)) {
			$style = 'i';
			$item = str_replace('{i}','',$item);
		}
		elseif(preg_match('~\{ub\}~',$item)) {
			$style = 'ub';
			$item = str_replace('{ub}','',$item);
		}
		elseif(preg_match('~\{ubi\}~',$item)) {
			$style = 'ubi';
			$item = str_replace('{ubi}','',$item);
		}
		elseif(preg_match('~\{b-rt\}~',$item)) {
			$style = 'b-rt';
			$item = str_replace('{b-rt}','',$item);
		}
		elseif(preg_match('~\{b-lt\}~',$item)) {
			$style = 'b-lt';
			$item = str_replace('{b-lt}','',$item);
		}
		elseif(preg_match('~\{u-rt\}~',$item)) {
			$style = 'u-rt';
			$item = str_replace('{u-rt}','',$item);
		}
		elseif(preg_match('~\{i-rt\}~',$item)) {
			$style = 'i-rt';
			$item = str_replace('{i-rt}','',$item);
		}
		elseif(preg_match('~\{ub-rt\}~',$item)) {
			$style = 'ub-rt';
			$item = str_replace('{ub-rt}','',$item);
		}
		elseif(preg_match('~\{ubi-rt\}~',$item)) {
			$style = 'ubi-rt';
			$item = str_replace('{ubi-rt}','',$item);
		}
		
		if(preg_match('~\{h1\}~',$item)) {
			$style = 'h1';
			$item = str_replace('{h1}','',$item);
		}
		elseif(preg_match('~\{h2\}~',$item)) {
			$style = 'h2';
			$item = str_replace('{h2}','',$item);
		}
		elseif(preg_match('~\{h1-rt\}~',$item)) {
			$style = 'h1-rt';
			$item = str_replace('{h1-rt}','',$item);
		}
		elseif(preg_match('~\{h1-i\}~',$item)) {
			$style = 'h1-i';
			$item = str_replace('{h1-i}','',$item);
		}
		elseif(preg_match('~\{h2-rt\}~',$item)) {
			$style = 'h2-rt';
			$item = str_replace('{h2-rt}','',$item);
		}
		elseif(preg_match('~\{h2-i\}~',$item)) {
			$style = 'h2-i';
			$item = str_replace('{h2-i}','',$item);
		}
		
		if(preg_match('~\{merge\}~',$item)) {
			$item = str_replace('{merge}','',$item);
			$merge_txt = ' ss:MergeAcross="1" ';
		}
		elseif(preg_match('~\{merge2\}~',$item)) {
			$item = str_replace('{merge2}','',$item);
			$merge_txt = ' ss:MergeAcross="2" ';
		}
		elseif(preg_match('~\{merge3\}~',$item)) {
			$item = str_replace('{merge3}','',$item);
			$merge_txt = ' ss:MergeAcross="3" ';
		}
		
		if(preg_match('~\{right\}~',$item)) {
			$style = 'right';
			$item = str_replace('{right}','',$item);
		}
		
		if(preg_match('~\{rt\}~',$item)) {
			$style = 'right';
			$item = str_replace('{rt}','',$item);
		}
		
		if(preg_match('~\{left\}~',$item)) {
			$style = 'left';
			$item = str_replace('{left}','',$item);
		}
		
		if(preg_match('~\{lt\}~',$item)) {
			$style = 'left';
			$item = str_replace('{lt}','',$item);
		}
		
		
		if(preg_match("/^[0-9]{1,11}$/",$item)) {
			$type = 'Number';
		}
		// sniff for valid dates should start with something like 2010-07-14 or 7/14/2010 etc..
		elseif(preg_match("/^(\d{2}|\d{4})[\\\-]\d{1,2}[\\\-](\d{2}|\d{4})([^d].+)?$/",$item) &&
					($timestamp = strtotime($item)) &&
					($timestamp > 0) &&
					($timestamp < strtotime('+500 years'))) {
			$type = 'DateTime';
			$item = strftime("%Y-%m-%dT%H:%M:%S",$timestamp);
			$style = 'sDT'; // defined in header; tells excel to format date for display
		}
		else {
			$type = 'String';
		}
				
		$item = str_replace(array('&#039;',"\n"), array('&apos;',''), htmlspecialchars($item, ENT_QUOTES));
		$output .= "            ";
		$output .= $style ? "<Cell ".$merge_txt."ss:StyleID=\"$style\">" : "<Cell".$merge_txt.">";
		$output .= '<Data ss:Type="'.$type.'">'.$item.'</Data>';//, $type, $item);
		$output .= "</Cell>\n";

		return $output;
	}
	
	function sendHttpHeaders() {
		header('Content-Disposition: attachment; filename="'.basename($this->filename).'"');
		header('Content-Type: text/xml; charset=' . $this->encoding);		
	}
	
}
