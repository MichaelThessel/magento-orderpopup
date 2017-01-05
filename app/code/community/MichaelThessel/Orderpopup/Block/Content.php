<?php

class MichaelThessel_Orderpopup_Block_Content extends Mage_Core_Block_Template
{
    protected $orders = array();
    protected $formatString = '';

    protected function _construct()
    {
        $cacheKey = array(
            'orderpopup_block',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
        );

        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => 1800,
            'cache_key' => implode('_', $cacheKey),
        ));
    }

    /**
     * Load content for individual popups
     *
     * @return void
     */
    public function getContent()
    {
        $this->orders = Mage::getModel('orderpopup/orders')->getOrders(20);

        $this->formatString = Mage::getStoreConfig('orderpopup/settings/format_string');
        $this->formatOrders();

        return json_encode(array(
           'popups' => $this->orders,
           'settings' => array(
               'delay_initial' => Mage::getStoreConfig('orderpopup/settings/delay_initial'),
               'delay' => Mage::getStoreConfig('orderpopup/settings/delay'),
               'delay_dialog' => Mage::getStoreConfig('orderpopup/settings/delay_dialog'),
           ),
           'batchId' => md5(serialize($this->orders)),
       ));
    }

    /**
     * Format order list for display in popup
     *
     * @return void
     */
    protected function formatOrders()
    {
        $formatString = $this->formatString;
        array_walk($this->orders, function(&$o) use ($formatString) {
            $out = array('content' => $formatString);

            // Replace user info
            foreach (array('name', 'city', 'state', 'country') as $repl) {
                $out['content'] = str_replace('[' . $repl . ']', $o[$repl], $out['content']);
            }

            // Replace product info
            $product = Mage::getModel('catalog/product')->load($o['productId']);
            $out['content'] = str_replace(
                '[product]',
                '<a href="' . $product->getProductUrl() . '">' . $product->getName() . '</a>',
                $out['content']
            );

            // Add image
            $imageUrl = Mage::helper('catalog/image')->init($product, 'image')->resize(83, 100);
            $out['imageUrl'] = (string)$imageUrl;

            // Add product URL
            $out['productUrl'] = $product->getProductUrl() ;

            $o = $out;
        });
    }
}
