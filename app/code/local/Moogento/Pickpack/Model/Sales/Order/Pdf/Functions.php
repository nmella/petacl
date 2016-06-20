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
* File        Functions.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 

function clean_method($dirty,$method='text') {

	$clean = '';
	if(is_object($dirty)) $dirty = strval($dirty);
	$dirty = strip_tags($dirty);

	if($method == 'shipping')
	{
		$shipping_method = trim(str_ireplace(array('Select Delivery Method','Method','Select postage','Select Shipping','Shipping Option', '- '),'',$dirty));
		$shipping_method = trim(str_ireplace('Local Delivery','~localdel~',$shipping_method));
		$shipping_method = trim(str_ireplace('Delivery','',$shipping_method));

		$shipping_method = trim(str_ireplace(
		array("\n","\r","\t",'  ','((','))','Free Shipping Free','Free shipping? Free','Free Economy Free Economy','Free Economy? Free Economy',
            	'Paypal Express Checkout','Pick up Pick up','Pick up at our ','Normal ','Registered',' Postage','Postage','Royal Mail','Mail','RM ',
            	'Store Pickup Store Pickup','Cash on Delivery','Courier Service','Delivery Charge','International','First','Second','2nd Class 2nd Class',
            	'1st Class 1st Class','View a UPS Ground Shipping Map Here',' rate:','Australia Post','Standard','working days with tracking','You qualify for',
            	'free ground shipping Free','United States Postal Service','United Parcel Service','UPS UPS','UPS (UPS)','Fed Ex','Federal Express','Available Shipping Options',
				'shipping','choose a day','Choose a Delivery Day','Choose a Shipping Day','Please choose a shipping','Please choose',
				' a ','Select Your','  ','and Packaging','~localdel~'),
		array(' ',' ',' ',' ','(',')','Free Shipping','Free Shipping','Free Economy','Free Economy',
            	'Paypal Express','Pick up','Pickup@','',"Reg'd",',','','RM','','Royal Mail ',
            	'Store Pickup','COD','Courier','Charge','Int\'l','1st','2nd','2nd Class',
            	'1st Class','','','AusPost','Std','working days','',
            	'free ground shipping','USPS','UPS','UPS','UPS','FedEx','FedEx','','','','','','','','','',' ','','Local Delivery'),
		$shipping_method));

		$shipping_method = preg_replace('~Charge:$~i','',$shipping_method);
		$shipping_method = preg_replace('~^\s*~','',$shipping_method);
		//$shipping_method = trim(preg_replace('~delivery~i','',$shipping_method));
		$shipping_method = trim(preg_replace('~^shipping~i','',$shipping_method));
		$shipping_method = preg_replace('~^\-~','',$shipping_method);
		$shipping_method = preg_replace('~^\:~','',$shipping_method);
		$shipping_method = trim(preg_replace('~^via\s~i','',$shipping_method));
		//$shipping_method = trim(preg_replace('~\((.*)$~','',$shipping_method));
		$shipping_method = preg_replace('~,$~i','',$shipping_method);

		// if same name has been configured as title and method, this will trim out the repetition
		$shipping_method_length = strlen($shipping_method);
		$s_a = trim(substr($shipping_method,0,(round($shipping_method_length/2))));
		$s_b = trim(substr($shipping_method,(round($shipping_method_length/2)),$shipping_method_length));

		if($s_a == $s_b) $shipping_method = $s_a;

		$clean = $shipping_method;
	}
	elseif(($method == 'payment') || ($method == 'payment-full'))
	{

		/* Payment */
		$paymentInfo = $dirty;

		$payment = explode('{{pdf_row_separator}}', $paymentInfo);
		foreach ($payment as $key=>$value)
		{
			if (strip_tags(trim($value))=='')
			{
				unset($payment[$key]);
			}
		}
		reset($payment);

		$payment_test = implode(',',$payment);
		$payment_test = trim(str_ireplace(
		array("\n","\r","\t",'  ',', credit card type:','credit card type:','- Account info','PayPal Secured Payment','credit card number','#: ','Credit Card','American Express','Master Card',',','(Authorize.net)','Cash on Delivery','Number','Credit / Debit CardCC','Credit / Debit Card','CardCC','payment gateway by','n/a','cccc','cc cc','****','****','***','***','Card: Visa','&rArr;','&amp;','sup3;','rArr','  ','#: ','Purchase Order Purchase Order','Payment:','Payment Visa','Visa#','Payment Mastercard','Mastercard#','MasterCard','COD COD','Reference','Name:','(saved):','Name on the card:','Pay with Paypal','#Wiped','Expiration','Date','CC, Type:','CC, Type','Pay by','Exp date','Exp. date','DebitCC','CC, Type MCCC','Type MCCC','MCCC','Type','  '),
		array(' ',' ',' ',' ',':',':','','Paypal','#','#','CC','Amex','MC','','','COD','#','CC','Card','CC','','CC','CC','CC','**','**','**','**','Visa','','','','','','#','Purchase Order','','Visa','Visa#','MC','MC#','MC','COD','Ref.','','','','Paypal','','Exp','','','','','Exp','Exp','Debit','MC','MC','MC','',''),$payment_test));

		$payment_test = trim(str_ireplace(
		array('Credit or Debit Card'),
		array('Card'),$payment_test));
		$payment_test = preg_replace('~^\s*~','',$payment_test);
		$payment_test = trim(preg_replace('~^:~','',$payment_test));
		$payment_test = preg_replace('~Paypal(.*)$~i','Paypal',$payment_test);
		$payment_test = preg_replace('~Account(.*)$~i','Account',$payment_test);
		$payment_test = preg_replace('~Processed Amount(.*)$~i','',$payment_test);
		$payment_test = preg_replace('~Payer Email(.*)$~i','',$payment_test);
        $payment_test = preg_replace('~Order was placed(.*)$~i','',$payment_test);		
		$payment_test = preg_replace('~Charge:$~i','',$payment_test);
		if($method != 'payment-full')
		{
			$payment_test = preg_replace('~Expiration(.*)$~i','',$payment_test);
			$payment_test = str_ireplace('Name on the Card','Name',$payment_test);
		}
		else
		{
			$payment_test = str_ireplace('Expiration','|Expiration',$payment_test);
			$payment_test = str_ireplace('Name on the Card','|Name on the Card',$payment_test);
		}
		$payment_test = preg_replace('~^\-~','',$payment_test);
		$payment_test = preg_replace('~Check / Money order(.*)$~i','Check / Money order',$payment_test);
		$payment_test = preg_replace('~Cheque / Money order(.*)$~i','Cheque / Money order',$payment_test);
		$payment_test = preg_replace('~Make cheque payable(.*)$~i','',$payment_test);
		$payment_test = str_ireplace(
		array('CardCC','CC Type','MasterCardCC','MasterCC',': MC',': Visa','Payment Visa','Payment MC','CCAmex','AmexCC','Type: Amex','CC Exp.','CC (Sage Pay)CC'),
		array('CC','CC, Type','MC','MC',' MC',' Visa','Visa','MC','Amex','Amex','Amex','Exp.','(Sage Pay)'),$payment_test);
		$payment_test = preg_replace('~:$~','',$payment_test);

		if($method != 'payment-full')
		{
			preg_match('~\b(?:\d[ -]*?){13,16}\b~',$payment_test,$cc_matches);
			if(isset($cc_matches[0]))
			{
				//$replacement_cc = str_pad(substr($cc_matches[0], -4), strlen($cc_matches[0]), '*', STR_PAD_LEFT);
				$replacement_cc = str_pad(substr($cc_matches[0], -4), 8, '*', STR_PAD_LEFT);
				$payment_test = str_replace($cc_matches[0],$replacement_cc,$payment_test);
			}
		}

		$payment_test = trim($payment_test);

		// if same name has been configured as title and method, this will trim out the repetition
		$payment_method_length = strlen($payment_test);
		$p_a = trim(substr($payment_test,0,(round($payment_method_length/2))));
		$p_b = trim(substr($payment_test,(round($payment_method_length/2)),$payment_method_length));

		if($p_a == $p_b && (strlen($p_a) > 4)) $payment_test = $p_a;

		// CC Visa #****2889
		$payment_test = preg_replace('~^(.*)Visa~i','Visa',$payment_test);

		$clean = $payment_test;
	}
	elseif($method == 'text')
	{
		$clean = str_replace(array("\n","\r",'<br/>','<br>','<br />'),'~',$dirty);//,'\n','\r'
		$clean = strip_tags($clean);
		//$clean = trim(preg_replace("/[^A-Za-z~0-9\'\"\.\,\-\\\ ]/", '', $clean));
		$clean = utf8_encode($clean);
		$clean = str_replace('~',"\n",$clean);
	}
	else
	{
		// currently only order notes comes through here
		$clean = str_replace('M2E Pro Notes:','',$clean);
	}

	return $clean;
}

function split_words($string, $split_regex='/\s/', $max = 1)
{
	$replace_word = str_replace(array('/','\\'),'',$split_regex);
	$words = preg_split($split_regex, $string);
	$lines = array();
	$line = '';

	foreach ($words as $k => $word) {
		$length = strlen($line . $replace_word . $word);
		if ($length <= $max) {
			$line .= $replace_word . $word;
			$line = str_replace($replace_word.$replace_word,$replace_word,$line);
		} else if ($length > $max) {
			if (!empty($line)) $lines[] = trim($line);
			$line = $replace_word . $word;
		} else {
			$lines[] = trim($line) . $replace_word . $word . $replace_word;
			$line = '';
		}
	}
	$lines[] = ($line = trim($line)) ? $line : $word;

	return $lines;
}

function search($array, $key='', $value='')
{
	$results = array();
	if(!$value) $value = '';

	if (is_array($array))
	{

		if (isset($array[$key]) && ($array[$key] == $value))
		{
			$results[] = $array;
		}
		foreach ($array as $subarray)
		{
			$results = array_merge($results, search($subarray, $key, $value));
		}
	}


	return $results;
}

function search2($array, $key='', $value='')
{
	$results = array();
	if(!$value)
		$value = '';

	if (is_array($array)) {
		if (isset($array[$key]) && ($array[$key] == $value))
			$results[] = $array;
		foreach ($array as $subarray) {
			$results = array_merge($results, search($subarray, $key, $value));
		}
	}
	return $results;
}

function unicode_strtoupper($str){
	mb_internal_encoding("UTF-8");
	if(!mb_check_encoding($str, 'UTF-8')
	    || !($str === mb_convert_encoding(mb_convert_encoding($str, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32')))
		    $str = mb_convert_encoding($str, 'UTF-8'); 
	
	return mb_convert_case($str, MB_CASE_UPPER, "UTF-8");
}

if (!function_exists('mb_strlen')) {
	function mb_strlen($str) {
		return strlen(iconv("UTF-8","cp1251", $str));
	}
}

if (!function_exists('mb_substr')) {
	function mb_substr($data,$start,$length = null, $encoding = null) {
		return substr($data,$start,$length);
	}
}

if (!function_exists('mb_strtoupper')) {
	function mb_strtoupper($str) {
			return strtoupper($str);
	}
}

if (!function_exists('mb_convert_case')) {
	function mb_convert_case($str, $mode='MB_CASE_UPPER', $encoding='UTF-8') {
       /*
	   * flag options:
	   * MB_CASE_UPPER, MB_CASE_LOWER, or MB_CASE_TITLE
	   MB_CASE_UPPER = LIKE THIS
	   MB_CASE_TITLE = Like This
        */
	   if($mode=='MB_CASE_UPPER')
		   $str = strtoupper($str);
	   elseif($mode=='MB_CASE_TITLE')
		   $str = ucwords($str);
	   elseif($mode=='MB_CASE_LOWER')
		   $str = strtolower($str);
       return $string;
	}
}

if(!function_exists('sksort'))
{
	function sksort(&$array, $subkey, $sort_ascending=false)
	{
		$temp_array = array();
		if (count($array)) $temp_array[key($array)] = array_shift($array);

		foreach($array as $key => $val)
		{
			$offset = 0;
			$found = false;
			foreach($temp_array as $tmp_key => $tmp_val)
			{
				if(!$found && isset($val[$subkey]) && isset($tmp_val[$subkey]) && strtolower($val[$subkey]) > strtolower($tmp_val[$subkey]))
				{
					$temp_array = array_merge(    (array)array_slice($temp_array,0,$offset),
					array($key => $val),
					array_slice($temp_array,$offset)
					);
					$found = true;
				}
				$offset++;
			}
			if(!$found) $temp_array = array_merge($temp_array, array($key => $val));
		}

		if ($sort_ascending) $array = array_reverse($temp_array);
		else $array = $temp_array;
	}
}

function utf8_wordwrap($str, $width, $break, $cut = false) {
	if (!$cut) {
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.',}\b#U';
	} else {
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){'.$width.'}#';
	}
	if (function_exists('mb_strlen')) {
		$str_len = mb_strlen($str,'UTF-8');
	} else {
		$str_len = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $str, $var_empty);
	}
	$while_what = ceil($str_len / $width);
	$i = 1;
	$return = '';
	while ($i < $while_what) {
		preg_match($regexp, $str,$matches);
		$string = $matches[0];
		$return .= $string.$break;
		$str = substr($str, strlen($string));
		$i++;
	}
	return $return.$str;
}


function mb_wordwrap_array($string, $width)
{
	if (($len = mb_strlen($string, 'UTF-8')) <= $width)
	{
		return array($string);
	}

	$return = array();
	$last_space = FALSE;
	$i = 0;

	do
	{
		if (mb_substr($string, $i, 1, 'UTF-8') == ' ')
		{
			$last_space = $i;
		}

		if ($i > $width)
		{
			$last_space = ($last_space == 0) ? $width : $last_space;

			$return[] = trim(mb_substr($string, 0, $last_space, 'UTF-8'));
			$string = mb_substr($string, $last_space, $len, 'UTF-8');
			$len = mb_strlen($string, 'UTF-8');
			$i = 0;
		}

		$i++;
	}
	while ($i < $len);

	$return[] = trim($string);

	return $return;
}

function checkColor($colorName)
{
	$colorName = trim($colorName);

	if(preg_match('/^#[0-9a-f]{3}([0-9a-f]{3})?$/i', $colorName)) //hex color is valid
	{
		return '#'.strtoupper($colorName);
	}
	elseif(preg_match('/^[0-9a-f]{3}([0-9a-f]{3})?$/i', $colorName)) //hex color is valid
	{
		return '#'.strtoupper($colorName);
	}
	else
	{
		$colorName = str_ireplace('Grey','Gray',$colorName);
		$html_color_names = 'aliceblue,lightpink,antiquewhite,lightsalmon,aqua,lightseagreen,aquamarine,lightskyblue,azure,lightslategray,beige,lightsteelblue,bisque,lightyellow,black,lime,blanchedalmond,limegreen,blue,linen,blueviolet,magenta,brown,maroon,burleywood,mediumaquamarine,burlywood,mediumblue,cadetblue,mediumorchid,chartreuse,mediumpurple,chocolate,mediumseagreen,coral,mediumslateblue,cornflowerblue,mediumspringgreen,cornsilk,mediumturquoise,crimson,mediumvioletred,cyan,midnightblue,darkblue,mintcream,darkcyan,mistyrose,darkgoldenrod,moccasin,darkgray,navajowhite,darkgreen,navy,darkkhaki,oldlace,darkmagenta,olive,darkolivegreen,olivedrab,darkorange,orange,darkorchid,orangered,darkred,orchid,darksalmon,palegoldenrod,darkseagreen,palegreen,darkslateblue,paleturquoise,darkslategray,palevioletred,darkturquoise,papayawhip,darkviolet,peachpuff,deeppink,peru,deepskyblue,pink,dimgray,plum,dodgerblue,powderblue,firebrick,purple,floralwhite,red,forestgreen,rosybrown,fuchsia,royalblue,gainsboro,saddlebrown,ghostwhite,salmon,gold,sandybrown,goldenrod,seagreen,gray,seashell,green,sienna,greenyellow,silver,honeydew,skyblue,hotpink,slateblue,indianred,slategray,indigo,snow,ivory,springgreen,khaki,steelblue,lavender,tan,lavenderblush,teal,lawngreen,thistle,lemonchiffon,tomato,lightblue,turquoise,lightcoral,violet,lightcyan,wheat,lightgoldenrodyellow,white,lightgreen,whitesmoke,lightgrey,yellow,yellowgreen';
		$html_color_array = explode(',',$html_color_names);
		if(in_array(strtolower($colorName),$html_color_array)) return $colorName;
		else return '#333333';//false;
	}
}

function widthForStringUsingFontSize($string, $font, $font_size, $fontStyle = 'regular', $non_standard_characters = 0)
{
	$string = trim($string);
	if($string == '') return '';
	if(!$font_size) $font_size = $this->_getConfig('font_size_body', 12, false,'general');
	if(!$font || $font == 'helvetica') $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
	$drawingString = iconv('UTF-8', 'UTF-16BE//IGNORE', $string);
	$characters = array();
	for ($i = 0; $i < strlen($drawingString); $i++) {
		$characters[] = (ord($drawingString[$i++]) << 8 ) | ord($drawingString[$i]);
	}
	if(!is_object($font))
	{
        $font = Mage::helper('pickpack/font')->getFontName2($font, $fontStyle, $non_standard_characters);
	}
	$glyphs = $font->glyphNumbersForCharacters($characters);
	$widths = $font->widthsForGlyphs($glyphs);
	$stringWidth = ( (array_sum($widths) / $font->getUnitsPerEm()) * $font_size );

	
	$stringWidth = ceil($stringWidth);
	return $stringWidth;
}

function stringBreak($string, $max_length, $font_size, $font='helvetica', $fontStyle = 'regular', $non_standard_characters = 0)
{
	$string = trim($string);
	if($string == '') return 75;
	$test_string = '';
	$test_string = preg_replace("/[^a-zA-Z0-9\s]/", '', $string); // this is to deal with hidden or weird characters
	if(!$font_size) $font_size = $this->_getConfig('font_size_body', 12, false,'general');
	$font_size += 2;
	if(!$font) $font = 'helvetica';// $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);

	$string_width = widthForStringUsingFontSize($test_string, $font, $font_size, $fontStyle, $non_standard_characters);

	$pt_per_char = ($string_width/strlen($test_string));
	$character_breakpoint   = floor($max_length/$pt_per_char);
	$character_breakpoint -= 5;

	return $character_breakpoint;
}

function pre($array){
	echo '<pre>';
	if(is_array($array)) print_r($array);
	else echo $array;
	echo '</pre>';
}

function getPrevNext($haystack,$needle,$prevnext = 'next') {
	$prev = $next = null;
	$pad = 25; // points to pad result, eg for '...' to be added on...

	$aKeys = array_keys($haystack);
	$key = '';
	if(($key = array_search('imagesX', $aKeys)) !== false) {
	    unset($aKeys[$key]);
	}
	$k = array_search($needle,$aKeys);
	if ($k !== false) {
		if ($k > 0)
		$prev = array($aKeys[$k-1] => $haystack[$aKeys[$k-1]]);
		if ($k < count($aKeys)-1)
		$next = array($aKeys[$k+1] => $haystack[$aKeys[$k+1]]);
	}
	$next_key = 0;
	$next_key = ($next[$aKeys[$k+1]]-$pad);
	if(!isset($next_key) || $next_key == 0) $next_key = 560;
	$prev_key = 0;
	$prev_key = ($prev[$aKeys[$k-1]]+$pad);
	if($prevnext == 'next') return $next_key;
	elseif($prevnext == 'prev') return $prev_key;
	else return array($prev,$next);
}

function getPrevNext2($haystack,$needle,$prevnext = 'next',$padded_right=560,$page_pad_right=-10) {
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
		$next_key = $padded_right - $page_pad_right + $pad;
	
	if($prevnext == 'next') return $next_key;
	elseif($prevnext == 'prev') return $prev_key;
	else return array($prev,$next);
}

function str_trim($string, $method = 'WORDS', $length = 25, $pattern = '...')
{
	if(!is_numeric($length))
	{
		$length = 25;
	}

	if(strlen($string) <= $length)
	{
		return $string;
	}
	else
	{
		switch($method)
		{
            case 'CHARS':
                if (function_exists('mb_strcut')){
                    return mb_strcut($string, 0, $length, 'UTF-8') . $pattern;
                }else{
                    return substr($string, 0 , $length) . $pattern;
                }
                break;
            case 'WORDS':
				if (strstr($string, ' ') == false)
				{
					return str_trim($string, 'CHARS', $length, $pattern);
				}

				$count = 0;
				$truncated = '';
				$word = explode(" ", $string);
					
				foreach($word AS $single)
				{
					if($count < $length)
					{
						if(($count + strlen($single)) <= $length)
						{
							$truncated .= $single . ' ';
							$count = $count + strlen($single);
							$count++;
						}
						else if(($count + strlen($single)) >= $length)
						{
							break;
						}
					}
				}
					
				return rtrim($truncated) . $pattern;
				break;
		}
	}
}

function strstr_after($haystack, $needle, $case_insensitive = false) {
	$strpos = ($case_insensitive) ? 'stripos' : 'strpos';
	$pos = $strpos($haystack, $needle);
	if (is_int($pos)) {
		return substr($haystack, $pos + strlen($needle));
	}
	// Most likely false or null
	return $pos;
}


if(!function_exists('mb_detect_encoding')) { 
function mb_detect_encoding($string, $enc=null) { 
    
    static $list = array('utf-8', 'iso-8859-1', 'windows-1251');
    
    foreach ($list as $item) {
        $sample = iconv($item, $item, $string);
        if (md5($sample) == md5($string)) { 
            if ($enc == $item) { return true; }    else { return $item; } 
        }
    }
    return null;
}
}

if(!function_exists('mb_convert_encoding')) { 
function mb_convert_encoding($string, $target_encoding, $source_encoding) { 
    $string = iconv($source_encoding, $target_encoding, $string); 
    return $string; 
}
}

function clean_for_pdf($content) { 
    if(!mb_detect_encoding($content, 'UTF-8') 
        OR !($content === mb_convert_encoding(mb_convert_encoding($content, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) { 

        $content = mb_convert_encoding($content, 'UTF-8'); 
        // 
        // if (mb_check_encoding($content, 'UTF-8')) { 
        //     // log('Converted to UTF-8'); 
        // } else { 
        //     // log('Could not converted to UTF-8'); 
        // } 
    } 
    return $content; 
} 

function getProductAttributeOptions($product_id,$attributeName)
{
	$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);
	$product         = Mage::getModel('catalog/product');
	$collection     = Mage::getResourceModel('eav/entity_attribute_collection')
	->setEntityTypeFilter($product->getResource()->getTypeId())
	->addFieldToFilter('attribute_code', $attributeName);
	$attribute         = $collection->getFirstItem()->setEntity($product->getResource());
	$attributeOptions = $attribute->getSource()->getAllOptions(false);
	return $attributeOptions;
}

function getProductAttribute_dev($product_id,$attributeName)
{
	///////////////
	$attributeValue = Mage::getModel('catalog/product')->load($product_id)->getData($attributeName);
	$product         = Mage::getModel('catalog/product');
	$collection     = Mage::getResourceModel('eav/entity_attribute_collection')
	->setEntityTypeFilter($product->getResource()->getTypeId())
	->addFieldToFilter('attribute_code', $attributeName);
	$attribute         = $collection->getFirstItem()->setEntity($product->getResource());
	$attributeOptions = $attribute->getSource()->getAllOptions(false);
	return $attributeOptions;
	
}

if(!function_exists('split_line_comma')){
    function split_line_comma($data) {
      if( $data != '')  {
         $chars = array('\n','\r',',');
         $str = str_replace($chars, '', $data);
         return $str;    
      }
    }
}

if(!function_exists('get_coupon_label')){    
    function get_coupon_label($rule_id,$store_id = 0) {
        if($rule_id != ''){
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table_name = $resource->getTableName('salesrule_label');
            $qry = "SELECT label FROM $table_name WHERE rule_id = $rule_id AND store_id = $store_id";
            $label = $readConnection->fetchOne($qry);
            return $label;
        }
        return '';
    }
    
}

if(!function_exists('getMooRetailExpressAttribute')){   
    function getMooRetailExpressAttribute(){
           if (Mage::helper('pickpack')->isInstalled('Moogento_RetailExpress')) {
                if ((Mage::helper('core')->isModuleEnabled('Moogento_RetailExpress'))){
                     return true;
                }                
            }
            return false;
        
    }    
}