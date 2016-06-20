<?php
/**
 * 
 * Date: 18.11.15
 * Time: 12:25
 */

class Moogento_Pickpack_Helper_State extends Mage_Core_Helper_Abstract
{
    protected $_states = array(
        array('name'=>'Alabama', 'abbrev'=>'AL'),
        array('name'=>'Alaska', 'abbrev'=>'AK'),
        array('name'=>'Arizona', 'abbrev'=>'AZ'),
        array('name'=>'Arkansas', 'abbrev'=>'AR'),
        array('name'=>'California', 'abbrev'=>'CA'),
        array('name'=>'Colorado', 'abbrev'=>'CO'),
        array('name'=>'Connecticut', 'abbrev'=>'CT'),
        array('name'=>'Delaware', 'abbrev'=>'DE'),
        array('name'=>'Florida', 'abbrev'=>'FL'),
        array('name'=>'Georgia', 'abbrev'=>'GA'),
        array('name'=>'Guam', 'abbrev'=>'GU'),
        array('name'=>'Hawaii', 'abbrev'=>'HI'),
        array('name'=>'Idaho', 'abbrev'=>'ID'),
        array('name'=>'Illinois', 'abbrev'=>'IL'),
        array('name'=>'Indiana', 'abbrev'=>'IN'),
        array('name'=>'Iowa', 'abbrev'=>'IA'),
        array('name'=>'Kansas', 'abbrev'=>'KS'),
        array('name'=>'Kentucky', 'abbrev'=>'KY'),
        array('name'=>'Louisiana', 'abbrev'=>'LA'),
        array('name'=>'Maine', 'abbrev'=>'ME'),
        array('name'=>'Maryland', 'abbrev'=>'MD'),
        array('name'=>'Massachusetts', 'abbrev'=>'MA'),
        array('name'=>'Michigan', 'abbrev'=>'MI'),
        array('name'=>'Minnesota', 'abbrev'=>'MN'),
        array('name'=>'Mississippi', 'abbrev'=>'MS'),
        array('name'=>'Missouri', 'abbrev'=>'MO'),
        array('name'=>'Montana', 'abbrev'=>'MT'),
        array('name'=>'Nebraska', 'abbrev'=>'NE'),
        array('name'=>'Nevada', 'abbrev'=>'NV'),
        array('name'=>'New Hampshire', 'abbrev'=>'NH'),
        array('name'=>'New Jersey', 'abbrev'=>'NJ'),
        array('name'=>'New Mexico', 'abbrev'=>'NM'),
        array('name'=>'New York', 'abbrev'=>'NY'),
        array('name'=>'North Carolina', 'abbrev'=>'NC'),
        array('name'=>'North Dakota', 'abbrev'=>'ND'),
        array('name'=>'Ohio', 'abbrev'=>'OH'),
        array('name'=>'Oklahoma', 'abbrev'=>'OK'),
        array('name'=>'Oregon', 'abbrev'=>'OR'),
        array('name'=>'Pennsylvania', 'abbrev'=>'PA'),
        array('name'=>'Puerto Rico', 'abbrev'=>'PR'),
        array('name'=>'Rhode Island', 'abbrev'=>'RI'),
        array('name'=>'South Carolina', 'abbrev'=>'SC'),
        array('name'=>'South Dakota', 'abbrev'=>'SD'),
        array('name'=>'Tennessee', 'abbrev'=>'TN'),
        array('name'=>'Texas', 'abbrev'=>'TX'),
        array('name'=>'Utah', 'abbrev'=>'UT'),
        array('name'=>'Vermont', 'abbrev'=>'VT'),
        array('name'=>'Virginia', 'abbrev'=>'VA'),
        array('name'=>'Washington', 'abbrev'=>'WA'),
        array('name'=>'West Virginia', 'abbrev'=>'WV'),
        array('name'=>'Wisconsin', 'abbrev'=>'WI'),
        array('name'=>'Wyoming', 'abbrev'=>'WY'),
        array('name'=>'Alberta', 'abbrev'=>'AB'),
        array('name'=>'British Columbia', 'abbrev'=>'BC'),
        array('name'=>'Manitoba', 'abbrev'=>'MB'),
        array('name'=>'New Brunswick', 'abbrev'=>'NB'),
        array('name'=>'Newfoundland and Labrador', 'abbrev'=>'NL'),
        array('name'=>'Northwest Territories', 'abbrev'=>'NT'),
        array('name'=>'Nova Scotia', 'abbrev'=>'NS'),
        array('name'=>'Nunavut', 'abbrev'=>'NU'),
        array('name'=>'Ontario', 'abbrev'=>'ON'),
        array('name'=>'Prince Edward Island', 'abbrev'=>'PE'),
        array('name'=>'Quebec', 'abbrev'=>'QC'),
        array('name'=>'Saskatchewan', 'abbrev'=>'SK'),
        array('name'=>'Yukon', 'abbrev'=>'YT')
    );

    private function convertState($name, $to='abbrev') {

        $return = false;
        foreach ($this->_states as $state) {
            if ($to == 'name') {
                if (strtolower($state['abbrev']) == strtolower($name)){
                    $return = $state['name'];
                    break;
                }
            } else if ($to == 'abbrev') {
                if (strtolower($state['name']) == strtolower($name)){
                    $return = strtoupper($state['abbrev']);
                    break;
                }
            }
        }
        return $return;
    }
}
