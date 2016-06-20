<?php
/**
 *
 * CART2QUOTE CONFIDENTIAL
 * __________________
 *
 *  [2009] - [2016] Cart2Quote B.V.
 *  All Rights Reserved.
 *
 * NOTICE OF LICENSE
 *
 * All information contained herein is, and remains
 * the property of Cart2Quote B.V. and its suppliers,
 * if any.  The intellectual and technical concepts contained
 * herein are proprietary to Cart2Quote B.V.
 * and its suppliers and may be covered by European and Foreign Patents,
 * patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Cart2Quote B.V.
 *
 * @category    Ophirah
 * @package     Qquoteadv
 * @copyright   Copyright (c) 2016 Cart2Quote B.V. (https://www.cart2quote.com)
 * @license     https://www.cart2quote.com/ordering-licenses(https://www.cart2quote.com)
 */

/**
 * Class Ophirah_Qquoteadv_Helper_License
 */
final class Ophirah_Qquoteadv_Helper_License extends Mage_Core_Helper_Abstract
{
    //Warning
    private $w1 = "Unpaid usage of our licensed functionalities is prohibited.";
    private $w2 = "Unpaid usage of our licensed functionalities is prohibited.";
    private $w3 = "Unpaid usage of our licensed functionalities is prohibited.";
    private $w4 = "Unpaid usage of our licensed functionalities is prohibited.";
    private $w5 = "Unpaid usage of our licensed functionalities is prohibited.";
    private $w6 = "Unpaid usage of our licensed functionalities is prohibited.";
    //End - Warning

    /**
     * The date to expire this module at - the format is Ymd for instance for 7th of may 2012 this would be 20120507
     *
     * @var int
     */
    private static $_expiryDate = 20160708;

    /**
     * The result of getAccessLevelFromKey.
     *
     * If it is already found, there is no need to check for the key again.
     * This improves performance and isn't a security risk because it is a private var
     * and needs to be set every request again.
     *
     * @var bool
     */
    private $_accessLevelFromKey = false;

    /**
     * The result of the hasExpired function
     *
     * If it is already checked, there is no need to check it again.
     * This improves performance and isn't a security risk because it is a private var
     * and needs to be set every request again.
     *
     * @var null
     */
    private $_hasExpired = null;

    /**
     * @return string
     */
    final private static function getC2QCreateHash()
    {
        return "opensource";
    }

    /**
     * @return int
     */
    final public function getC2QExpiryDate()
    {
        $extendDays = self::getTrialExtendDays();
        if($extendDays !== null){
            $year = (int)substr(self::$_expiryDate, 0, 4);
            $month = (int)substr(self::$_expiryDate, 4, 2);
            $day = (int)substr(self::$_expiryDate, 6, 2);
            return date("Ymd", mktime(0, 0, 0, $month, $day+$extendDays, $year));
        }
        return self::$_expiryDate;
    }

    /**
     * @return bool
     */
    final public static function isOpenSourceC2QVersion()
    {
        if (strtolower(self::getC2QCreateHash()) == "opensource") {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    final public function hasExpired()
    {
        if($this->_hasExpired === null){
            //not expired?
            $this->_hasExpired = false;

            //check if this is the first used installation of Cart2Quote
            if($this->isOldVersionDetected()){
                if($this->getTrialExtendDays() === null){
                    //old version is detected and there is no trial extension key
                    //trial is not allowed anymore
                    $this->_hasExpired = true;
                    return true;
                }
            }

            if (self::$_expiryDate < date("Ymd") and self::$_expiryDate !== false) {
                $extendDays = $this->getTrialExtendDays();
                if($extendDays !== null){
                    if (self::$_expiryDate > date("Ymd", strtotime("-".$extendDays." days"))){
                        //expired but extended trials is not expired
                        $this->_hasExpired = false;
                    } else {
                        //expired and extended trials is also expired
                        $this->_hasExpired =  true;
                    }
                } else {
                    //expired
                    $this->_hasExpired =  true;
                }
            }
        }

        return $this->_hasExpired;
    }

    /**
     * This function compares the given license option with the available options for the current license
     *
     * @param $fnName
     * @param array|null $createHash
     * @param bool $noHash
     * @return bool
     */
    final public function validLicense($fnName, $createHash = null, $noHash = false)
    {
        $standard = array(
            'create-edit-admin',    // Create & edit quotes in admin panel
            'convert-admin',        // Convert quotes to order in admin panel
            'my-quotes',            // My Quote integration in Customer Dashboard
            'pdf-email-proposals',  // Create PDF & email price proposals in mere seconds
            'attach-email-files',   // Attach extra files with the price proposal email
            'auto-tier-prices',     // Auto enter tier prices in price proposals*
            'non-free',             // Key for non-free options
            'standard',             // Key for standard options
            'starter'               // Key for starter options
        );

        $professional = array_merge($standard, array(
            'auto-proposal',    // Auto submit quotations
            'link-rep2quote',   // Link sales representatives to quotes
            'link-order2quote', // Link orders to quotes
            'email-auto-login', // Auto login links in emails
            'professional',     // Key for professional options
            'business',         // Key for business options
            'quick_quote_mode'  // Quick quote
        ));

        $enterprise = array_merge($professional, array(
            'api',                                  // API functionality for linking to CRM and ERP
            'export',                               // Export quotes to csv
            'CRMaddon',                             // CRMaddon module
            'mass_update_quote_requests',           // Mass update in System>Config
            'qquoteadv_qquoteadv_reminder_email',   // Reminder email
            'qquoteadv_qquoteadv_expire_email',     // Expire email
            'customer_group_allow',                 // Cart2Quote allow quotation based on group
            'extra-fields',                         // Custom quote form fields
            'enterprise',                           // Kkey for enterprise options
            'tier-cost',                            // Tier cost functionality
            'supplier-bidding-tool'                 // Supplier Bidding Tool
        ));

        $level = $this->getAccessLevel($createHash, $noHash);

        switch ($level) {
            case null:
                return false;
                break;

            case 399530:
                $versionFns = $standard;
                break;

            case 599530:
                $versionFns = $professional;
                break;

            case 799530:
                $versionFns = $enterprise;
                break;
        }

        if (in_array($fnName, $versionFns)) {
            return true;
        }

        return false;
    }

    /**
     * It would be nice to use this in the system>configuration page
     * But in case of an ionCube issue, the page wouldn't load.
     *
     * @param null $createHash
     * @return string
     */
    final public function getEdition($createHash = null)
    {
        $level = $this->getAccessLevel($createHash);

        // if no valid license found check for trial version
        if ($this->isTrialVersion($createHash) && !$this->hasExpired()) {
            $trial = ' (trial)';
        } else {
            $trial = '';
        }

        if (strtolower(self::getC2QCreateHash()) == "opensource") {
            return 'Opensource';
        }

        switch ($level) {
            case null:
                return 'Free';

            case 399530:
                return 'Starter';

            case 599530:
                return 'Business';

            case 799530:
                return 'Enterprise' . $trial;

            default:
                return 'Free';
                break;
        }
    }

    /**
     * Check if quote is from current trial version
     * $createHash = array( 0 => [HASH], 1 => [INCREMENTID])
     *
     * @param null $createHash
     * @param bool $noHash
     * @return bool
     */
    final public function isTrialVersion($createHash = null, $noHash = false)
    {
        if ($this->getAccessLevelFromKey() == null && self::$_expiryDate !== false) {
            if (is_array($createHash) && ($createHash[0] == $this->getCreateHash($createHash[1]))) {
                return true;
            }
            if($noHash){
                return true;
            }
        }

        return false;
    }

    /**
     * @return int|null
     */
    final public function getAccessLevelFromKey(){ return 799530; }     

    /**
     * @param $licenseKey
     * @return int|null
     */
    

    /**
     * @param null $createHash
     * @param bool $noHash
     * @return int|null
     */
    final public function getAccessLevel($createHash = null, $noHash = false)
    {
        // get access from license key
        $access = $this->getAccessLevelFromKey();

        // if no valid license found check for trial version
        if ($this->isTrialVersion($createHash, $noHash) && !$this->hasExpired()) {
            $access = 799530;
        }

        return $access;
    }

    /**
     * Function to strip http/https, www and / form a domain
     *
     * @param $input
     * @return mixed
     */
    final private function _stripUrlForLicenseKeyCheck($input)
    {
        $input = trim($input, '/');

        if (!preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);

        $domain = preg_replace('/^www\./', '', $urlParts['host']);

        return $domain;
    }

    /**
     * Unique hash for specific
     * Cart2Quote version
     *
     * @param string $incrementId
     * @return string
     */
    final public function getCreateHash($incrementId)
    {
        return self::_createHash($incrementId);
    }

    /**
     * @param $incrementId
     * @return string
     */
    final protected function _createHash($incrementId)
    {
        return md5($incrementId . self::$_expiryDate . self::getC2QCreateHash());
    }

    /**
     * Get the extended trial days from the licensen key
     * This function only works with the v5 keys.
     *
     * @return int|null
     */
    final private function getTrialExtendDays(){ return null;} 

    /**
     * This function try's to detect old versions of Cart2Quote
     *
     * This check is only used if there is not license key filled in the backend
     * This check only search for items form version v4.1.6 of Cart2Quote and later
     * This check only checks one item for each month.
     * So at the time of writing this function the worst case scenario is that this generates 20x14 md5 hashes.
     * That would set you back 115 microseconds or 0.000115 seconds. Just so you know ;)
     *
     * @return bool
     */
    final private function isOldVersionDetected(){ return false;} 

    /**
     * In case of a Starter license, check if the quote url is the same as the base url
     *
     * @param $storeId
     * @return bool
     */
    final public function checkQuoteLicense($storeId) {
        if($this->getAccessLevel() == 399530){
            $baseHost = Mage::getStoreConfig('web/unsecure/base_url', 0);
            if (!$baseHost) {
                $baseHost = Mage::getStoreConfig('web/secure/base_url', 0);
            }
            $baseHost = $this->_stripUrlForLicenseKeyCheck($baseHost);

            $quoteHost = Mage::getStoreConfig('web/unsecure/base_url', $storeId);
            if (!$quoteHost) {
                $quoteHost = Mage::getStoreConfig('web/secure/base_url', $storeId);
            }
            $quoteHost = $this->_stripUrlForLicenseKeyCheck($quoteHost);

            if($quoteHost != $baseHost){
                return false;
            }
        }

        return true;
    }

    /**
     * Check if this is free user.
     *
     * @return bool
     */
    final public function isFreeUser(){
        //check if 'non-free' is allowed
        if($this->validLicense('non-free', null, true)){
            //is not a free user
            return false;
        } else {
            //is a free user
            return true;
        }
    }

    /**
     * Get the Cart2Quote version
     *
     * @return mixed
     */
    public function getCart2QuoteVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Qquoteadv")->version;
        return $version;
    }

    /**
     * Get the Not2Order version
     *
     * @return mixed
     */
    public function getNot2OrderVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Not2Order")->version;
        return $version;
    }

    /**
     * Get the CRMaddon version
     *
     * @return mixed
     */
    public function getCRMaddonVersion(){
        $version = Mage::getConfig()->getModuleConfig("Ophirah_Crmaddon")->version;
        return $version;
    }

    /**
     * If ionCube is loaded, get the version
     *
     * @return string
     */
    public function getIonCubeVersion(){
        if (extension_loaded('ionCube Loader')) {
            $ioncube_version = $this->ioncube_loader_version();
            return $ioncube_version;
        } else {
            return 'IonCube is not installed';
        }
    }

    /**
     * Get the PHP version
     *
     * @return string
     */
    public function getPHPVersion(){
        $version = phpversion();
        return $version;
    }

    /**
     * Get the Cart2Quote license
     *
     * @return mixed
     */
    public function getCart2QuoteLicense(){
        $license_key = Mage::getStoreConfig('qquoteadv_general/quotations/licence_key');
        return $license_key;
    }

    /**
     * Get the Cart2Quote edition
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteEdition(){
        $edition = Mage::getStoreConfig('qquoteadv_general/quotations/edition');

        if(!isset($edition) || empty($edition)){
            $edition = 'unknown';
        }

        return $edition;
    }

    /**
     * Get the Magento version
     *
     * @return mixed
     */
    public function getMagentoVersion(){
        return Mage::getVersion();
    }

    /**
     * Get the Magento edition if that function is available
     * If not, then the Magento version is probably below 1.7
     *
     * @return string
     */
    public function getMagentoEdition(){
        if(method_exists('Mage', 'getEdition')){
            $edition = Mage::getEdition();
            return $edition;
        } else {
            return '';
        }
    }

    /**
     * Get the current domain
     *
     * @return mixed
     */
    public function getCurrentDomain(){
        $host = Mage::getStoreConfig('web/unsecure/base_url', 0);
        if (!$host) {
            $host = Mage::getStoreConfig('web/secure/base_url', 0);
            if (!$host) {
                if (!empty($_SERVER['HTTP_HOST'])) {
                    $host = $_SERVER['HTTP_HOST'];
                } else {
                    //This function is never triggered in cron, so no need for a fallback
                    $host = $_SERVER['SERVER_NAME'];
                }
            }
        }
        $host = self::_stripUrlForLicenseKeyCheck($host);

        return $_SERVER['SERVER_NAME']." (".$host.")";
    }

    /**
     * This function gets the ionCube version from the integer version sting
     * It also has a fallback for ionCube < v3.1
     *
     * @return string
     */
    public function ioncube_loader_version() {
        if ( function_exists('ioncube_loader_iversion') ) {
            $ioncube_loader_iversion = ioncube_loader_iversion();
            $ioncube_loader_version_major       = (int)substr($ioncube_loader_iversion,0,1);
            $ioncube_loader_version_minor       = (int)substr($ioncube_loader_iversion,1,2);
            $ioncube_loader_version_revision    = (int)substr($ioncube_loader_iversion,3,2);
            $ioncube_loader_version = "$ioncube_loader_version_major.$ioncube_loader_version_minor.$ioncube_loader_version_revision";
        } else {
            $ioncube_loader_version = ioncube_loader_version();
        }
        return $ioncube_loader_version;
    }

    /**
     * Get the Cart2Quote expiry date
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteExpiryDate(){
        $expiryDate = Mage::getStoreConfig('qquoteadv_general/quotations/expiry_date');

        if(!isset($expiryDate) || empty($expiryDate)){
            $expiryDate = 'unknown';
        }

        return $expiryDate;
    }

    /**
     * Get the Cart2Quote trial expired
     * This data is only available if Cart2Quote gets enabled in the global config page
     *
     * @return string
     */
    public function getCart2QuoteTrialExpired(){
        return Mage::getStoreConfig('qquoteadv_general/quotations/has_expired');
    }
}
