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
* File        Page.php
* @category   Moogento
* @package    pickPack
* @copyright  Copyright (c) 2016 Moogento <info@moogento.com> / All rights reserved.
* @license    https://moogento.com/License.html
*/ 


class Moogento_Pickpack_Model_Moo_Pdf_Page extends Zend_Pdf_Page
{
    public function drawRoundedRectangle($x1, $y1, $x2, $y2, $radius,
                                         $fillType = Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE) {

        $this->_addProcSet('PDF');

        if (!is_array($radius)) {
            $radius = array($radius, $radius, $radius, $radius);
        } else {
            for ($i = 0; $i < 4; $i++) {
                if (!isset($radius[$i])) {
                    $radius[$i] = 0;
                }
            }
        }

        $topLeftX = $x1;
        $topLeftY = $y2;
        $topRightX = $x2;
        $topRightY = $y2;
        $bottomRightX = $x2;
        $bottomRightY = $y1;
        $bottomLeftX = $x1;
        $bottomLeftY = $y1;

        //draw top side
        $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX + $radius[0]);
        $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " m\n";
        $x1Obj = new Zend_Pdf_Element_Numeric($topRightX - $radius[1]);
        $y1Obj = new Zend_Pdf_Element_Numeric($topRightY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top right corner if needed
        if ($radius[1] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y1Obj = new Zend_Pdf_Element_Numeric($topRightY);
            $x2Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y2Obj = new Zend_Pdf_Element_Numeric($topRightY);
            $x3Obj = new Zend_Pdf_Element_Numeric($topRightX);
            $y3Obj = new Zend_Pdf_Element_Numeric($topRightY - $radius[1]);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw right side
        $x1Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
        $y1Obj = new Zend_Pdf_Element_Numeric($bottomRightY + $radius[2]);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom right corner if needed
        if ($radius[2] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
            $y1Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $x2Obj = new Zend_Pdf_Element_Numeric($bottomRightX);
            $y2Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $x3Obj = new Zend_Pdf_Element_Numeric($bottomRightX - $radius[2]);
            $y3Obj = new Zend_Pdf_Element_Numeric($bottomRightY);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw bottom side
        $x1Obj = new Zend_Pdf_Element_Numeric($bottomLeftX + $radius[3]);
        $y1Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw bottom left corner if needed
        if ($radius[3] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y1Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
            $x2Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y2Obj = new Zend_Pdf_Element_Numeric($bottomLeftY);
            $x3Obj = new Zend_Pdf_Element_Numeric($bottomLeftX);
            $y3Obj = new Zend_Pdf_Element_Numeric($bottomLeftY + $radius[3]);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        //draw left side
        $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX);
        $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY - $radius[0]);
        $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . " l\n";

        //draw top left corner if needed
        if ($radius[0] != 0) {
            $x1Obj = new Zend_Pdf_Element_Numeric($topLeftX);
            $y1Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $x2Obj = new Zend_Pdf_Element_Numeric($topLeftX);
            $y2Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $x3Obj = new Zend_Pdf_Element_Numeric($topLeftX + $radius[0]);
            $y3Obj = new Zend_Pdf_Element_Numeric($topLeftY);
            $this->_contents .= $x1Obj->toString() . ' ' . $y1Obj->toString() . ' '
                . $x2Obj->toString() . ' ' . $y2Obj->toString() . ' '
                . $x3Obj->toString() . ' ' . $y3Obj->toString() . ' '
                . " c\n";
        }

        switch ($fillType) {
            case Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE:
                $this->_contents .= " B*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_FILL:
                $this->_contents .= " f*\n";
                break;
            case Zend_Pdf_Page::SHAPE_DRAW_STROKE:
                $this->_contents .= " S\n";
                break;
        }

        return $this;
    }
}
