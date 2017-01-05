<?php

class MichaelThessel_Orderpopup_Model_Orders extends Mage_Core_Model_Abstract
{
    protected $config = array();
    protected $cache;
    protected $cacheKey = 'orderpopup_orders';
    protected $cacheTtl = 1800;

    public function __construct()
    {
        $this->cache = Mage::app()->getCache();
    }

    /**
     * Get list of recent orders
     *
     * @param mixed $count Number of orders to get
     * @return array List of orders
     */
    public function getOrders($count)
    {
        $orders = unserialize($this->cache->load($this->cacheKey));
        if ($orders) return $orders;

        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
            ->setOrder('created_at', 'desc')
            ->setPageSize($count)->setCurPage(1);

        $orders = array();
        foreach ($orderCollection as $order) {
            $billingAddress = $order->getBillingAddress();

            foreach ($order->getAllVisibleItems() as $item) break;

            $orders[] = array(
                'city' => $this->format($billingAddress->getCity()),
                'state' => $this->format($billingAddress->getRegion()),
                'country' => Mage::app()->getLocale()->getCountryTranslation($billingAddress->getCountry()),
                'name' => $this->format($billingAddress->getFirstname()),
                'productId' => $item->getProduct()->getId(),
            );
        }

        $this->cache->save(serialize($orders), $this->cacheKey, array(), $this->cacheTtl);

        return $orders;
    }

    /**
     * Format order data
     *
     * @param mixed $string
     * @return string Formatted data
     */
    protected function format($string)
    {
        return ucfirst(strtolower($string));
    }
}
