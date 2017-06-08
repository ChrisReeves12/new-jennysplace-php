<?php
/**
* The ContentBlockListViewStrategy class definition.
*
* This is the content block strategy that is used to render the list view in the backend
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;

use Library\Controller\JPController;
use Library\Model\Page\ContentBlock;
use Library\Model\ViewStrategy\IViewStrategy;
use Zend\View\Model\JsonModel;
use Zend\Config\Reader\Xml as XmlReader;

/**
 * Class ContentBlockListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class ContentBlockListViewStrategy implements IViewStrategy
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
            'identifyer' => ['handle', 'Handle'],
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
        return count($this->findAllContentBlocks());
    }

    /**
     * Get all content blocks
     * @return array
     */
    protected function findAllContentBlocks()
    {
        // Get file to save contents to
        $file = getcwd() . '/content.xml';

        // Create a file reader
        $reader = new XmlReader();

        $content_blocks = [];
        $contents_array_xml = $reader->fromFile($file);

        if (!empty($contents_array_xml))
        {
            foreach ($contents_array_xml as $key => $element)
            {
                $content_block = new ContentBlock();
                $content_block->setHandle($key);
                $content_block->setContent($element['content']);
                $content_block->setTimestamp(new \DateTime($element['timestamp']));
                $content_blocks[$key] = $content_block;
            }
        }

        return $content_blocks;
    }

    /**
     * Get listings from file
     */
    protected function queryListings()
    {
        $results =  $this->findAllContentBlocks();
        $array_chunks = array_chunk($results, $this->max_row_view);

        if (!empty($array_chunks[$this->page_id - 1]))
        {
            foreach ($array_chunks[$this->page_id - 1] as $content_block)
            {
                $this->list_results[$content_block->getHandle()] = $content_block;
            }
        }
    }

    /**
     * Process listings for display
     */
    protected function processListings()
    {
        //$fields = $this->entity_info['parameters'];
        $new_results = [];

        if (!empty($this->list_results))
        {
            foreach ($this->list_results as $result)
            {
                $new_result = [
                    'Handle' => $result->getHandle()
                ];

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
            $keyword = $_GET['keyword'];

            $results = [];
            if (isset($this->list_results[$keyword]))
            {
                $results[$keyword] = $this->list_results[$keyword];
                $this->list_results = $results;
            }

            $array_chunks = array_chunk($this->list_results, $this->max_row_view);

            if (!empty($array_chunks[$this->page_id - 1]))
            {
                foreach ($array_chunks[$this->page_id - 1] as $content_block)
                {
                    $this->list_results[$content_block->getHandle()] = $content_block;
                }
            }
        }
    }
}