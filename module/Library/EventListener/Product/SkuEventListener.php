<?php
/**
* The SkuEventListener class definition.
*
* This class contains different event listeners for skus
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\EventListener\Product;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Product\Status;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SkuEventListener
 * @package Library\EventListener\Product
 */
class SkuEventListener
{
    protected $object;
    protected $products_to_update;
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

        // Handle updating skus
        if ($this->object instanceof Sku)
        {
            $sku = $this->object;
            $in_stock = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(1);
            $out_of_stock = $eventArgs->getObjectManager()->getRepository('Library\Model\Product\Status')->findOneById(2);

            // Mark "out of stock" on skus below or at zero quantity if coming from "in stock" status
            if ($sku->getQuantity() == 0 || $sku->getQuantity() < 0)
            {
                if ($sku->getStatus() == $in_stock)
                {
                    $sku->setQuantity(0);
                    $sku->setStatus($out_of_stock);
                }
            }

            // If coming from "out of stock" make it in stock
            elseif ($sku->getQuantity() > 0)
            {
                if ($sku->getStatus() == $out_of_stock)
                {
                    $sku->setStatus($in_stock);
                }
            }

            // The product's status might need to be changed now, so persist the product to fire off its event listeners
            $this->products_to_update[spl_object_hash($sku->getProduct())] = $sku->getProduct();
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

    /**
     * Handle events after the flush
     * @param PostFlushEventArgs $postFlushEventArgs
     */
    public function postFlush(PostFlushEventArgs $postFlushEventArgs)
    {
        if (!empty($this->products_to_update))
        {
            $em = $postFlushEventArgs->getEntityManager();

            // Update proucts
            foreach ($this->products_to_update as $key => $product)
            {
                // Check if list is empty
                if (empty($this->products_to_update))
                    break;

                // Update product to fire off the event listeners
                if ($product instanceof Product)
                {
                    $status_override = $product->getStatusOverride();

                    if (!($status_override instanceof Status) || (($status_override instanceof Status) && $status_override->getId() != 3))
                    {
                        $product->setDateModified(new \DateTime());
                        $em->persist($product);
                    }
                }

                unset($this->products_to_update[$key]);
            }

            $em->flush();
        }
    }
}