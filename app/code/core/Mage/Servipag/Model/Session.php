<?php
//Modulo de Pago de Servipag para Mangento
//Versi�n 0.0.1 
//Fecha �ltima Modificaci�n: 19-10-2011
//Autor: Francisco Hurtado
//Email: frhurtadob@gmail.com

class Mage_Servipag_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $this->init('Servipag');
    }
}