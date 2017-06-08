<?php
/**
* The ShippingRangeListViewStrategy class definition.
*
* This strategy renders shipping ranges in the list view
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class ShippingRangeListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class ShippingRangeListViewStrategy extends GenericListViewStrategy
{
    /**
     * Get listings from database
     */
    protected function queryListings()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select('e')->from($this->entity_info['entity'], 'e');
        $qb->innerJoin('Library\Model\Shop\ShippingMethod', 'sm', 'WITH', 'sm = e.shipping_method');
        $qb->orderBy('e.low_value', 'ASC');
        $qb->addOrderBy('sm.id', 'ASC');
        $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
        $this->list_results = $qb->getQuery()->getResult();
    }

    /**
     * Process listings for display
     */
    protected function processListings()
    {
        $new_results = [];

        if (!empty($this->list_results))
        {
            foreach ($this->list_results as &$result)
            {
                // Add parameters directly
                $new_result['Id'] = $result->getId();
                $new_result['Shipping Method'] = $result->getShippingMethod()->getName();
                $new_result['Price'] = "$".$result->getPrice();
                $new_result['Low Value'] = $result->getLowValue();
                $new_result['High Value'] = $result->getHighValue();
                $new_result['Date'] = $result->getDateCreated()->format('m/d/Y');

                $new_results[] = $new_result;
            }

            $this->list_results = $new_results;
        }
    }

    /**
     * Handle search
     */
    public function handle_search()
    {
        if (!empty($_GET['task']) && $_GET['task'] == 'search')
        {
            $em = EntityManagerSingleton::getInstance();
            $filter = $_GET['filter'];
            $keyword = $_GET['keyword'];
            $entity_info = $this->entity_table[$this->entity];
            $entity = $entity_info['entity'];

            $qb = $em->createQueryBuilder();
            $qb->select('e');
            $qb->from($entity, 'e');
            $qb->innerJoin('Library\Model\Shop\ShippingMethod', 'sm', 'WITH', 'sm = e.shipping_method');

            // Get the correct field
            switch ($filter)
            {
                case 'shipping_method':
                    $qb->where($qb->expr()->like('sm.name', ':keyword'));
                    break;

                default:
                    $qb->where($qb->expr()->like('e.'.$filter, ':keyword'));
                    break;
            }

            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
            $qb->orderBy('e.low_value', 'ASC');
            $qb->addOrderBy('sm.id', 'ASC');
            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getResult();
        }
    }
}