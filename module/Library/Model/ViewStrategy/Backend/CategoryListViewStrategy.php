<?php
/**
* The CategoryViewStrategy class definition.
*
* This strategy renders categories in the list view
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CategoryListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class CategoryListViewStrategy extends GenericListViewStrategy
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
        $qb->orderBy('e.sort_order', 'DESC');
        $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
        $this->list_results = $qb->getQuery()->getResult();
    }

    /**
     * Creates lookup table for long category names
     * @return array
     */
    private function _createCategoryDictionary()
    {
        // List categories with ancestors
        $em = EntityManagerSingleton::getInstance();

        $categories = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy();

        // Create lookup table with proper category display names
        $cat_dictionary = [];
        foreach ($categories as $category)
        {
            $ancestors = $category['ancestors'];
            if (!empty($ancestors))
            {
                $ancestor_string = "";
                foreach ($ancestors as $ancestor)
                {
                    $ancestor_string .= $ancestor['name'] . ' >> ';
                }

                // Attach ancestor string to name
                $category['name'] = $ancestor_string . $category['name'];
            }

            $cat_dictionary[$category['id']] = $category['name'];
        }

        return $cat_dictionary;
    }

    /**
     * Process listings for display
     */
    protected function processListings()
    {
        // Create lookup table with proper category display names
        $cat_dictionary = $this->_createCategoryDictionary();

        $new_results = [];

        if (!empty($this->list_results))
        {
            foreach ($this->list_results as &$result)
            {
                // Add parameters directly
                $new_result['Id'] = $result->getId();
                $new_result['Name'] = $cat_dictionary[$result->getId()];
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
            $keyword = $_GET['keyword'];
            $filter = $_GET['filter'];
            $entity_info = $this->entity_table[$this->entity];
            $entity = $entity_info['entity'];

            $qb = $em->createQueryBuilder();
            $qb->select('e');
            $qb->from($entity, 'e');

            $qb->where($qb->expr()->like('e.'.$filter, ':keyword'));

            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
            $qb->orderBy('e.sort_order', 'DESC');
            $qb->addOrderBy('e.date_created', 'DESC');
            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getResult();
        }
    }
}