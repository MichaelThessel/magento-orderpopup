<?php

class MichaelThessel_Orderpopup_Block_Content extends Mage_Core_Block_Template
{
    /**
     * Get popup options
     *
     * @return void
     */
    public function getSettings()
    {
        return json_encode(array(
           'settings' => array(
               'delay_initial' => Mage::getStoreConfig('orderpopup/settings/delay_initial'),
               'delay' => Mage::getStoreConfig('orderpopup/settings/delay'),
               'delay_dialog' => Mage::getStoreConfig('orderpopup/settings/delay_dialog'),
           ),
       ));
    }
}
