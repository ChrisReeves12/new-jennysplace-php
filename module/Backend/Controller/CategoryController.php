<?php
/**
* The CategoryController class definition.
*
* The controller that handles the adding, editing and deleting of categories.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Category\CreateUpdate;
use Library\Model\Category\Category;
use Library\Model\Media\Image;
use Library\Model\Shop\ShopList\QueryList;
use Library\Service\CategoryService;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CategoryController
 * @package Backend\Controller
 */
class CategoryController extends JPController
{
    protected $create_update_form;

    /** @var  Category */
    protected $category;

    protected $category_id;

    /**
     * The page that shows a single category where you can edit it.
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get category ID if passed in
        $this->category_id = isset($_GET['id']) ? $_GET['id'] : null;

        // Create form to collect information about category
        $this->create_update_form = new CreateUpdate();

        // Handle posts
        $this->handle_post();

        // Build form
        $this->build_form();

        $main_photo = null;

        // Load category and hydrate form if category is being viewed
        if (!is_null($this->category_id))
        {
            $this->category = $em->getRepository('Library\Model\Category\Category')->findOneById($this->category_id);
            if (!($this->category instanceof Category))
            {
                throw new \Exception("This category does not exist in the database.");
            }

            // Hydrate form
            $main_photo = $this->hydrate_form();
        }

        return ['create_update_form' => $this->create_update_form, 'main_photo' => $main_photo];
    }

    /**
     * Handle post actions
     *
     * @return null|\Zend\Http\Response
     * @throws \Exception
     */
    public function handle_post()
    {
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $files = $this->getRequest()->getFiles()->toArray();

            $data = array_merge_recursive($data, $files);

            $task = $data['task'];
            unset($data['task']);

            switch ($task)
            {
                case 'create_update':

                    // Get category ID if passed in
                    $category_id = isset($_GET['id']) ? $_GET['id'] : null;

                    // Validate form
                    $this->create_update_form->setData($data);
                    if ($this->create_update_form->isValid())
                    {
                        $valid_data = $this->create_update_form->getData();

                        /** @var CategoryService $category_service */
                        $category_service = $this->getServiceLocator()->get('category');
                        $this->category = $category_service->save($valid_data, $category_id);
                    }

                    // Save database changes
                    EntityManagerSingleton::getInstance()->flush();

                    // Refresh the page
                    return $this->redirect()->toUrl($_SERVER['REDIRECT_URL'] . '?id=' . $this->category->getId());
                    break;
            }
        }

        return null;
    }

    /**
     * Create the form
     */
    public function build_form()
    {
        // Populate the category drop down
        $em = EntityManagerSingleton::getInstance();

        $category_options = [0 => 'None'];
        $category_listings = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy($this->category_id);

        if (!empty($category_listings))
        {
            foreach($category_listings as $listing)
            {
                // Construct listing name
                $listing_name = "";
                if (count($listing['ancestors']) > 0)
                {
                    foreach ($listing['ancestors'] as $ancestor)
                    {
                        $listing_name .= $ancestor['name'] . " >> ";
                    }
                }

                $listing_name .= $listing['name'];
                $category_options[$listing['id']] = $listing_name;
            }
        }

        $this->create_update_form->get('parent')->setAttribute('options', $category_options);
        $this->create_update_form->get('points_to')->setAttribute('options', $category_options);

        // Populate query lists
        $query_lists = $em->getRepository('Library\Model\Shop\ShopList\QueryList')->findAll();
        $query_list_array = [];
        $query_list_array[0] = 'None';
        if (count($query_lists) > 0)
        {
            foreach ($query_lists as $query_list)
            {
                $query_list_array[$query_list->getId()] = $query_list->getName();
            }
        }

        $this->create_update_form->get('query_list')->setAttribute('options', $query_list_array);
    }

    /**
     * Fills up the category creation form if an id of a product is passed in
     * @return \Library\Model\Media\Image|null
     */
    public function hydrate_form()
    {
        // Fill form with category info
        $category = $this->category;
        $this->create_update_form->get('category_name')->setValue($category->getName());
        $this->create_update_form->get('inactive')->setValue($category->getInactive());
        $this->create_update_form->get('sort_order')->setValue($category->getSortOrder());
        $this->create_update_form->get('description')->setValue($category->getDescription());
        $this->create_update_form->get('meta_description')->setValue($category->getPage()->getDescription());
        $this->create_update_form->get('keywords')->setValue($category->getKeywords());

        $query_list = $category->getQueryList();
        if ($query_list instanceof QueryList)
        {
            $this->create_update_form->get('query_list')->setValue($query_list->getId());
        }

        $parent_category = $category->getParentCategory();
        if ($parent_category instanceof Category)
        {
            $this->create_update_form->get('parent')->setValue($parent_category->getId());
        }

        $points_to_category = $category->getPointsTo();
        if ($points_to_category instanceof Category)
        {
            $this->create_update_form->get('points_to')->setValue($points_to_category->getId());
        }

        // Get image to show
        $image = $category->getDefaultImage();
        if ($image instanceof Image)
        {
            return $image;
        }

        return null;
    }
}