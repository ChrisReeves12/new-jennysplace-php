<?php
/**
* The ProductEventListener class definition.
*
* This class contains different event listeners for products
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\EventListener\Product;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Library\Model\Product\Product;
use Library\Model\Product\Status;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProductEventListener
 * @package Library\EventListener\Product
 */
class ProductEventListener
{
    protected $object;
    protected $service_manager;

    /**
     * @param ServiceLocatorInterface $service_manager
     */
    public function __construct(ServiceLocatorInterface $service_manager)
    {
        $this->service_manager = $service_manager;
    }

    /**
     * Handle preUpdate Doctrine event
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $this->object = $eventArgs->getObject();

        // Handle updating products
        if ($this->object instanceof Product)
        {
            $product = $this->object;

            /**@var \Library\Model\Product\Status $out_of_stock */
            $out_of_stock = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(2);
            $in_stock = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(1);
            $disabled = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(3);
            $preorder = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(5);

            // Set status according to skus and override
            if ($product->getStatusOverride() instanceof Status)
            {
                $product->setStatus($product->getStatusOverride());

                // Set default sku
                if (count($product->getSkus()) == 1 && $product->getSkus()[0]->getIsDefault())
                {
                    $product->getSkus()[0]->setStatus($product->getStatusOverride());
                }
            }

            // Get status from skus
            else
            {
                $product_skus = $product->getSkus();
                if (count($product_skus) > 0)
                {
                    // For default sku product that don't have an override, use quantity to determine stock status
                    if ($product_skus[0]->getIsDefault() && count($product_skus) == 1)
                    {
                        $default_sku = $product_skus[0];
                        if ($default_sku->getQuantity() <= 0)
                        {
                            $default_sku->setStatus($out_of_stock);
                            $product->setStatus($out_of_stock);
                        }
                        else
                        {
                            $default_sku->setStatus($in_stock);
                            $product->setStatus($in_stock);
                        }
                    }

                    // For products with multiple skus (products that don't have a default sku) get product stock status from skus
                    else
                    {
                        $all_out_of_stock = true;
                        $all_disabled = true;
                        $all_preorder = true;

                        foreach ($product_skus as $product_sku)
                        {
                            // This should never actually run because we're not suppose to have default skus here
                            if ($product_sku->getIsDefault())
                                continue;

                            if ($product_sku->getStatus() != $out_of_stock)
                                $all_out_of_stock = false;

                            if ($product_sku->getStatus() != $disabled)
                                $all_disabled = false;

                            if ($product_sku->getStatus() != $preorder)
                                $all_preorder = false;
                        }

                        if ($all_out_of_stock == true)
                            $product->setStatus($out_of_stock);
                        elseif ($all_disabled == true)
                            $product->setStatus($disabled);
                        elseif ($all_preorder == true)
                            $product->setStatus($preorder);
                        else
                            $product->setStatus($in_stock);
                    }
                }

                // If this product has no skus and no id, it's probrably a new product
                elseif (is_null($product->getId()))
                {
                    // For new products, just default it to in stock
                    $product->setStatus($in_stock);
                }
            }
        }
    }

    /**
     * Handle prePersist Doctrine event
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->preUpdate($eventArgs);
    }
}