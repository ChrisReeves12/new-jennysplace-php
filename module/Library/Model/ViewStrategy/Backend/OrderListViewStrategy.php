<?php
/**
* The OrderListViewStrategy class definition.
*
* This strategy renders orders in the list view
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class OrderListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class OrderListViewStrategy extends GenericListViewStrategy
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
        $qb->orderBy('e.date_created', 'DESC');
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
                $new_result['First Name'] = $result->getUser()->getFirstName();
                $new_result['Last Name'] = $result->getUser()->getLastName();
                $new_result['Order Number'] = $result->getOrderNumber();
                $new_result['Status'] = $result->getStatus();
                $new_result['Date'] = $result->getDateCreated()->format('m/d/Y');
                $new_result['Pay Method'] = $result->getPaymentMethod()->getName();
                $new_result['Sub-Total'] = '$'.$result->getSubTotal();
                $new_result['Total'] = '$'.$result->getTotal();

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
            $qb->innerJoin('Library\Model\User\User', 'u', 'WITH', 'u = e.user');
            $qb->innerJoin('Library\Model\Shop\PaymentMethod', 'pm', 'WITH', 'pm = e.payment_method');

            // Get the correct field
            switch ($filter)
            {
                case 'first_name':
                    $qb->where($qb->expr()->like('u.'.$filter, ':keyword'));
                    break;

                case 'last_name':
                    $qb->where($qb->expr()->like('u.'.$filter, ':keyword'));
                    break;

                case 'pay_method':
                    $qb->where($qb->expr()->like('pm.name', ':keyword'));
                    break;

                default:
                    $qb->where($qb->expr()->like('e.'.$filter, ':keyword'));
                    break;
            }

            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
            $qb->orderBy('e.date_created', 'DESC');
            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getResult();
        }
    }
}