<?php
/**
* The CustomPageViewStrategy class definition.
*
* This strategy renders categories in the list view
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CustomPageListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class CustomPageListViewStrategy extends GenericListViewStrategy
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
        $qb->join('Library\Model\Page\Page', 'p', 'WITH', 'p = e.page');
        $qb->orderBy('p.title', 'ASC');
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
                $new_result['Title'] = $result->getPage()->getTitle();
                $new_result['URL Handle'] = $result->getPage()->getUrlHandle();
                $new_result['Date Added'] = $result->getDateCreated()->format('m/d/Y');

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
            $keyword = $_GET['keyword'];
            $filter = $_GET['filter'];
            $entity_info = $this->entity_table[$this->entity];
            $entity = $entity_info['entity'];

            $qb = $em->createQueryBuilder();
            $qb->select('e');
            $qb->from($entity, 'e');

            $qb->innerJoin('Library\Model\Page\Page', 'p', 'WITH', 'p = e.page');

            // Handle filters for search
            if ($filter == 'id')
            {
                $qb->where($qb->expr()->like('e.' . $filter, ':keyword'));
            }
            else
            {
                $qb->where($qb->expr()->like('p.' . $filter, ':keyword'));
            }

            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
            $qb->addOrderBy('e.date_created', 'DESC');
            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getResult();
        }
    }
}