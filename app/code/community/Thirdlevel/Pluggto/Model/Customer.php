<?php

class Thirdlevel_Pluggto_Model_Customer extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("pluggto/customer");

    }

    private function UpdateUserInformationIfApplicable($magentoCustomerModel, $orderDataFromPluggto)
    {
        Mage::helper('pluggto')->WriteLogForModule('Info', 'Entrada '.  __METHOD__);

        // updated customer data
        // if has VAT save on customer table
        // Verifico se há número de documento a ser salvo
        if (isset($orderDataFromPluggto['buyer_doc_number']) && $orderDataFromPluggto['buyer_doc_number'] != null)
        {
            // Verifico se o lojista colocou o nome de algum campo
            $customFieldToStoreCFPorCNPJ = Mage::getStoreConfig('pluggto/configs/custom_document_field');
            if (isset($orderDataFromPluggto['payer_cpf']) && !empty($orderDataFromPluggto['payer_cpf'])) $document = $orderDataFromPluggto['payer_cpf'];
            if (isset($orderDataFromPluggto['payer_cnpj']) && !empty($orderDataFromPluggto['payer_cnpj'])) $document = $orderDataFromPluggto['payer_cnpj'];


            if (isset($document) && $customFieldToStoreCFPorCNPJ != '' && $customFieldToStoreCFPorCNPJ != null)
            {
               // Possível erro com o uso de array no setData
                $magentoCustomerModel->addData(array(trim($customFieldToStoreCFPorCNPJ)=>$document));
            } else
            {
                $magentoCustomerModel->setTaxvat($document);
            }
        }

        foreach ($magentoCustomerModel->getAddresses() as $address) {
            $caddress   = Mage::getModel('customer/address')->load($address['entity_id']);
            $caddress   = $this->PopulateShippingData($orderDataFromPluggto,$caddress);
            $caddress->save();
            break;
        }

        $customFieldToStoreCFPorCNPJ = Mage::getStoreConfig('pluggto/configs/default_ie_field');

        if(empty($customFieldToStoreCFPorCNPJ)){
            $customFieldToStoreCFPorCNPJ = 'ie';
        }

        if(!empty($orderDataFromPluggto['payer_ie'])){
            $magentoCustomerModel->setData($customFieldToStoreCFPorCNPJ,$orderDataFromPluggto['payer_ie']);
        }


        if(empty($data['payer_gender'])){
            $magentoCustomerModel->setTipopessoa('Juridica');
        } else {
            $magentoCustomerModel->setTipopessoa('Fisica');
        }

        $magentoCustomerModel->save();
    }

    public function PopulateShippingData($dados,$shipping=null)
    {
        if(is_null($shipping)){
            $shipping = new Mage_Customer_Model_Address;
        }

        if(isset($dados['receiver_name'])) $shipping->setFirstname($dados['receiver_name']);
        if(isset($dados['receiver_lastname']))$shipping->setLastname($dados['receiver_lastname']);


        if(isset($dados['receiver_state_id'])){
            $shipping->setRegionId($dados['receiver_state_id']);
        } elseif (isset($dados['receiver_state'])){
            $shipping->setRegionId($dados['receiver_state']);
        }

        $ReceiverAddressLine = array();

        // receiver address line
        if(!empty($dados['receiver_address']))            $ReceiverAddressLine[]  = $dados['receiver_address'];
        if(!empty($dados['receiver_address_number']))     $ReceiverAddressLine[]  = $dados['receiver_address_number'];
        if(!empty($dados['receiver_additional_info']))    $ReceiverAddressLine[]  = $dados['receiver_additional_info'];
        if(!empty($dados['receiver_neighborhood']))       $ReceiverAddressLine[]  = $dados['receiver_neighborhood'];
        if(!empty($dados['receiver_address_complement'])) $ReceiverAddressLine[]  = $dados['receiver_address_complement'];


        if(!empty($ReceiverAddressLine)){
            $shipping->setStreet($ReceiverAddressLine);
        }


        if (isset($dados['receiver_zipcode']))
        {
            $shipping->setPostcode($dados['payer_zipcode']);
        }
        if (isset($dados['receiver_city']))
        {
            $shipping->setCity($dados['receiver_city']);
        }
        if (isset($dados['receiver_state']))
        {
            $shipping->setRegion($dados['receiver_state']);
        }


        $regionModel = Mage::getModel('directory/region')->loadByCode($dados['receiver_state'],$dados['receiver_country']);
        $regionId = $regionModel->getId();

        if(!empty($regionId)){
            $shipping->setRegionId($regionId);
        }

        if (isset($dados['receiver_country']))
        {
            $shipping->setCountryId($dados['receiver_country']);
        }
        $phoneArray = array();

        if(!empty($dados['receiver_phone_area'])){
            $phoneArray[] = $dados['receiver_phone_area'];
        }

        if($dados['receiver_phone']){
            $phoneArray[] = $dados['receiver_phone'];
        }

        if(!empty($phoneArray) && is_array($phoneArray)){
            $phone = implode(' ',$phoneArray);
        }

        if (isset($phone))
        {
            $shipping->setTelephone($phone);
        }

        if(isset($dados['payer_cpf'])){
            $shipping->setVatId($dados['payer_cpf']);
        }

        $shipping->setIsDefaultShipping(true);
        $shipping->setIsDefaultBilling(true);

        return $shipping;
    }


    public function getCustomer($data){


        // Set data used to retrieve or create/update user
        $this->login     = $data['payer_email'];
        $this->firstname = $data['payer_name'];
        $this->lastname  = $data['payer_lastname'];

        $storeIdWherePutClient = Mage::getStoreConfig('pluggto/configs/default_store');



        if(empty($storeIdWherePutClient)){
            $storeIdWherePutClient = Mage::app()->getWebsite()->getId();
            $storeWherePutClient = Mage::getModel('core/store')->load($storeIdWherePutClient);
        } else {
            $storeWherePutClient = Mage::getModel('core/store')->load($storeIdWherePutClient);
        }

        $customer = Mage::getModel('customer/customer');
        $customer->setStore($storeWherePutClient);
        $customer->setWebsiteId($storeWherePutClient->getWebsiteId());
        $customer->loadByEmail($data['payer_email']);


        //Check if user already exists on Store
        if ($customer->getId())
        {

            try
            {
                $this->UpdateUserInformationIfApplicable($customer, $data);
            } catch (exception $e)
            {

                Mage::helper('pluggto')->WriteLogForModule('Error', __METHOD__ . ' Error saving customer data. Error Message: ' . $e->getMessage());
            }

            // user was found
            $data = array('id'       => $customer->getId(),
                'login'              => $customer->getEmail(),
                'name'               => $this->firstname,
                'lastname'           => $this->lastname,
                'email'              => $this->login);

            return $data;

        } else
        {


            $groupId = Mage::getStoreConfig('pluggto/configs/customer_group');

            // creates a new user
            Mage::helper('pluggto')->WriteLogForModule('Info', 'Novo Usuario prestes a ser criado');

            $this->password = $customer->generatePassword(8);

            try
            {
                $customer->setId(null)
                    ->setFirstname($this->firstname)
                    ->setLastname($this->lastname)
                    ->setEmail($this->login)
                    ->setPassword($this->password)
                    ->setConfirmation($this->password);

                $customer->setStore($storeWherePutClient);
                $customer->setWebsiteId($storeWherePutClient->getWebsiteId());


                if(!empty($groupId)){
                    $customer->setGroupId($groupId);
                }


                // if has VAT save on customer table
                //Customização para escolher campo onde salvar CPF/CNPJ

                if (isset($data['payer_cpf']) && !empty($data['payer_cpf'])) $document = $data['payer_cpf'];
                if (isset($data['payer_cnpj'])  && !empty($data['payer_cnpj'])) $document = $data['payer_cnpj'];

                try {



                    // Verifico se há número de documento a ser salvo
                    if (isset($document) && $document != null)
                    {
                        // Verifico se o lojista colocou o nome de algum campo
                        $customFieldToStoreCFPorCNPJ = Mage::getStoreConfig('pluggto/configs/default_doc_field');

                        if (($customFieldToStoreCFPorCNPJ != '') && ($customFieldToStoreCFPorCNPJ != null))
                        {
                            $customer->setData($customFieldToStoreCFPorCNPJ, $document);
                        } else
                        {
                            $customer->setTaxvat($document);
                        }
                    }

                } catch (exception $e)
                {
                    // Essa exception só é atingida se o banco de dados der um erro
                    $customer->setTaxvat($document);
                }

                // if has address save Address information

                    $customFieldToStoreIE = Mage::getStoreConfig('pluggto/configs/default_ie_field');

                    if(empty($customFieldToStoreIE)){
                        $customFieldToStoreIE = 'ie';
                    }

                    if(!empty($data['payer_ie'])){
                        $customer->setData($customFieldToStoreIE,$data['payer_ie']);
                    }


                    $shipping = $this->PopulateShippingData($data);

                    $customer->addAddress($shipping);
                    if(empty($data['payer_gender'])){
                        $customer->setTipopessoa('Juridica');
                    } else {
                        $customer->setTipopessoa('Fisica');
                    }

                    $customer->save();


                $data = array(
                    'id'       => $customer->getId(),
                    'password' => $this->password,
                    'login'    => $this->login,
                    'name'     => $this->firstname,
                    'lastname' => $this->lastname
                );

                return $data;


            } catch (exception $e)
            {

                return $e->getMessage();
                Mage::helper('pluggto')->WriteLogForModule('Error', $e->getMessage());
            }



            return $data;
        }

    }


	
	// send to pluggto
	public function send(){
		
	}
	// receive from pluggto
	public function receive(){
		
	}
	
	// create at store
	public function create(){
		
	}
	
	// update at store
	public function update(){
		
	}	

}
	 