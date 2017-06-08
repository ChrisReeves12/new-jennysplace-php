<?php
/**
* The GenericListViewStrategy class definition.
*
* This is the general strategy that is used to render the list view in the backend
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;

use Library\Controller\JPController;
use Library\Model\ViewStrategy\IViewStrategy;
use Library\Service\DB\EntityManagerSingleton;
use Zend\View\Model\JsonModel;

/**
 * Class GenericListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class GenericListViewStrategy implements IViewStrategy
{
    protected $page_id;
    protected $entity;
    protected $entity_table;
    protected $controller;
    protected $max_row_view;
    protected $entity_info;
    protected $row_count;
    protected $list_results = [];
    protected $machine_field_names;

    /**
     * Renders the output of the list
     *
     * @param JPController $controller
     * @return array | JsonModel
     */
    public function render(JPController $controller)
    {
        // Load settings
        $this->controller = $controller;
        $this->prepareEntity();

        // Handle posts
        $response = $this->controller->handle_post();
        if ($response instanceof JsonModel)
        {
            return $response;
        }

        // Get the fields to show
        $fields = isset($this->entity_info['parameters']) ? $this->entity_info['parameters'] : [];
        $this->machine_field_names = [];
        foreach ($fields as $machine_name => $alias)
        {
            $this->machine_field_names[] = $machine_name;
        }

        // Get the total amount of rows
        $max_page_num = $this->getListRowCount();

        // Get rows
        $this->queryListings();

        // Get rows by search if applicable
        $this->handle_search();

        // Make results use alias names
        $this->processListings();

        return [
            'list_items' => $this->list_results,
            'list_info' => $this->entity_info,
            'total_row_count' => $this->row_count,
            'max_view_count' => $this->max_row_view,
            'max_page_num' => $max_page_num,
            'cur_page' => $this->page_id,
            'hide_create' => isset($this->entity_info['hide_create']) ? $this->entity_info['hide_create'] : false
        ];
    }

    /**
     * Prepares and sets up the entity to be used
     */
    protected function prepareEntity()
    {
        // Settings
        $this->max_row_view = 50;
        if (isset($_GET['page']) && is_numeric($_GET['page']))
        {
            $this->page_id = $_GET['page'];
        }
        else
        {
            $this->page_id = 1;
        }

        $this->entity = $this->controller->getEntity();

        // Get information about the entity to display to view
        $this->entity_table = $this->controller->getEntityTable();
        $this->entity_info = $this->entity_table[$this->entity];
    }

    /**
     * Get the number of rows total in the list
     * @return int
     */
    protected function getListRowCount()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select($qb->expr()->count('enty'))->from($this->entity_info['entity'], 'enty');
        $this->row_count = $qb->getQuery()->getSingleScalarResult();
        $max_page_num = (int) ceil($this->row_count / $this->max_row_view);
        $qb = null;

        return $max_page_num;
    }

    /**
     * Get listings from database
     */
    protected function queryListings()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        
        $qb = $em->createQueryBuilder();
        $qb->select('partial e.{id, '.implode(',', $this->machine_field_names).'}')->from($this->entity_info['entity'], 'e');
        $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);

        // Get sorting information
        $sort_info = isset($this->entity_info['sort']) ? $this->entity_info['sort'] : ['date_created', 'DESC'];
        $qb->orderBy('e.' . $sort_info[0], $sort_info[1]);

        $this->list_results = $qb->getQuery()->getArrayResult();
    }

    /**
     * Process listings for display
     */
    protected function processListings()
    {
        $fields = $this->entity_info['parameters'];
        $new_results = [];

        if (!empty($this->list_results))
        {
            foreach ($this->list_results as &$result)
            {
                // Go through the params
                foreach ($result as $key => $value)
                {
                    if (!empty($this->entity_info['omit_parameters']) && in_array($key, $this->entity_info['omit_parameters']))
                        continue;

                    $alias_name = $fields[$key];

                    // Handle displaying date values
                    if ($value instanceof \DateTime)
                    {
                        $new_result[$alias_name] = $value->format('m-d-Y');
                    }
                    else
                    {
                        $new_result[$alias_name] = $value;
                    }
                }

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
            $qb->select('partial e.{id, '.implode(',', $this->machine_field_names).'}');
            $qb->from($entity, 'e');
            $qb->where($qb->expr()->like('e.'.$filter, ':keyword'));
            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);

            // Get sorting information
            $sort_info = isset($this->entity_info['sort']) ? $this->entity_info['sort'] : ['date_created', 'DESC'];
            $qb->orderBy('e.' . $sort_info[0], $sort_info[1]);

            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getArrayResult();
        }
    }
}