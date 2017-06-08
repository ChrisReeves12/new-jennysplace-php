<?php
/**
* The ShopListEventListener class definition.
*
* This class contains different event listeners for Shop Lists and shop list elements
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\EventListener\ShopList;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Library\Model\Shop\ShopList;
use Library\Model\Shop\ShopListElement;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ShopListEventListener
 * @package Library\EventListener\ShopList
 */
class ShopListEventListener
{
    protected $object;
    protected $shop_lists_to_update;
    protected $omit_shop_lists;
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

        // Handle updating shop lists
        if ($this->object instanceof ShopList)
        {
            $this->shop_lists_to_update[spl_object_hash($this->object)] = $this->object;
        }

        // Handle updating shop list elements and their shop lists
        elseif ($this->object instanceof ShopListElement)
        {
            $this->shop_lists_to_update[spl_object_hash($this->object->getShopList())] = $this->object->getShopList();
            $this->object->calculateTotal();
        }
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $this->object = $eventArgs->getObject();

        // Check if this shop list should be ommited
        if ($this->object instanceof ShopList)
        {
            $this->omit_shop_lists[spl_object_hash($this->object)] = $this->object;
        }

        // Add shop lists from elements that need to be updated
        if ($this->object instanceof ShopListElement)
        {
            $shop_list = $this->object->getShopList();

            if ($shop_list instanceof ShopList)
            {
                $this->shop_lists_to_update[spl_object_hash($shop_list->getId())] = $shop_list;
            }
        }
    }

    public function onFlush(OnFlushEventArgs $onFlushEventArgs)
    {
        // Get entity manager;
        $em = $onFlushEventArgs->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity_to_update)
        {
            if ($entity_to_update instanceof ShopList\Order)
            {
                $update_changeset = $unitOfWork->getEntityChangeSet($entity_to_update);
                if (!empty($update_changeset['status']))
                {
                    if ($update_changeset['status'][1] == 'Shipped' && ($update_changeset['status'][0] != $update_changeset['status'][1]))
                    {
                        // Set the shipping date
                        $entity_to_update->setShippingDate(new \DateTime());
                    }
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $postFlushEventArgs)
    {
        if (!empty($this->shop_lists_to_update))
        {
            $em = $postFlushEventArgs->getEntityManager();

            // Update shop lists like orders and carts
            foreach ($this->shop_lists_to_update as $key => $shop_list)
            {
                // Check if list is empty
                if (empty($this->shop_lists_to_update))
                    break;

                // Check if this shop list should be ommitted
                if (isset($this->omit_shop_lists[$key]))
                    continue;

                // Update shop list
                if ($shop_list instanceof ShopList)
                {
                    $shop_list->calculateWeight();
                    $shop_list->calculateTotals($this->service_manager);
                }

                // Consolidate duplicate skus' quantities
                $line_items = $shop_list->getShopListElements();
                $line_item_sku_table = [];

                if (count($line_items) > 0)
                {
                    // Create table of skus to line items and get duplicates
                    foreach ($line_items as $line_item)
                    {
                        $line_item_sku_table[$line_item->getSku()->getId()][] = $line_item;
                    }

                    if (count($line_item_sku_table) > 0)
                    {
                        foreach ($line_item_sku_table as $sku_id => $line_item_sku)
                        {
                            // If there is more than one there are duplicate line items
                            if (count($line_item_sku) > 1)
                            {
                                // Consolidate these by adding their quantities together
                                $new_qty = 0;

                                foreach ($line_item_sku as $key => $line_item)
                                {
                                    $new_qty += $line_item->getQuantity();

                                    // Keep only one of the duplicates and delete the rest of them
                                    if ($key > 0)
                                    {
                                        $em->remove($line_item);
                                    }
                                }

                                // Set the new total quantity on the first line item
                                $line_item_sku[0]->setQuantity($new_qty);
                            }
                        }
                    }
                }

                unset($this->shop_lists_to_update[$key]);
            }

            $em->flush();
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