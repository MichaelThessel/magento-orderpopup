<?php
/**
 * Ajax controller to fetch the order popup info
 */
class MichaelThessel_Orderpopup_AjaxController extends Mage_Core_Controller_Front_Action
{
    protected $orders = [];
    protected $formatString = '';
    protected $cacheKey = 'orderpopupData';
    protected $cacheLifeTime = 3600;

    /**
     * Returns most recent orders formatted as popups
     */
    public function loadAction()
    {
        $cache = Mage::app()->getCache();
        if (!$data = unserialize($cache->load($this->cacheKey))) {
            $this->orders = Mage::getModel('orderpopup/orders')->getOrders(20);

            $this->formatString = Mage::getStoreConfig('orderpopup/settings/format_string');
            $this->formatOrders();

            $data = [
                'popups' => $this->orders,
                'batchId' => md5(serialize($this->orders)),
            ];

            $cache->save(serialize($data), $this->cacheKey, [], $this->cacheLifeTime);
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($data));
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
            $out = ['content' => $formatString];

            // Replace user info
            foreach (['name', 'city', 'state', 'country'] as $repl) {
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
