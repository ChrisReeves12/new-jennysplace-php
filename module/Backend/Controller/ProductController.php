<?php
/**
* The ProductController class definition.
*
* This controller manages all of the actions needed regarding products.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Doctrine\Common\Collections\Criteria;
use Library\Controller\JPController;
use Library\Form\Product\AddImages;
use Library\Form\Product\AddSkus;
use Library\Form\Product\CreateUpdate;
use Library\Model\Category\Category;
use Library\Model\Product\Option;
use Library\Model\Product\OptionValue;
use Library\Model\Product\Product;
use Library\Model\Product\Status;
use Library\Model\Relationship\OptionOptionValue;
use Library\Model\Relationship\ProductImage;
use Library\Model\Relationship\ProductVideo;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\SkuService;
use Library\Service\Settings;
use Zend\View\Model\JsonModel;

/**
 * Class ProductController
 * @package Backend\Controller
 */
class ProductController extends JPController
{
    protected $create_update_form;
    protected $add_images_form;
    protected $add_skus_form;
    protected $status_options;
    protected $product_id;
    protected $product;

    /**
     * Shows a single product for editing or creating.
     *
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        $em = EntityManagerSingleton::getInstance();

        // Check if a product is being edited by checking for an id
        $this->product_id = !empty($_GET['id']) ? $_GET['id'] : null;
        if (!is_null($this->product_id))
        {
            $this->product = $em->getRepository('Library\Model\Product\Product')->findOneById($this->product_id);
        }

        // Create form to display
        $this->create_update_form = new CreateUpdate();
        $this->add_images_form = new AddImages();
        $this->add_skus_form = new AddSkus();

        // Handle post
        $response = $this->_handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Create forms
        $this->build_forms();

        // Populate form with data if product exists
        $product_category_listings = [];
        $additional_image_info = [];
        $additional_video_info = [];
        $sku_information = [];
        $theme_ids = [];

        if (!empty($this->product_id))
        {
            if (empty($this->product) || false === ($this->product instanceof Product))
            {
                $this->product = $em->getRepository('Library\Model\Product\Product')->findOneById($this->product_id);
                if (!($this->product instanceof Product))
                {
                    throw new \Exception("This product cannot be found in the database.");
                }
            }

            // Add data to main product form
            list($product_category_listings, $theme_ids) = $this->hydrate_product_form();

            // Add additional images data
            $additional_image_info = $this->hydrate_additional_image_form();

            // Add additional video data
            $additional_video_info = $this->hydrate_additional_video_form();

            // Add sku information to form
            $sku_information = $this->hydrate_skus_form();

        }

        // Pass other variables about the product to the view
        if (!empty($this->product))
        {
            $main_photo = $this->product->getDefaultImage();
        }

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/product.js');

        // Get list of main categories and build list of sub categories
        $main_categories = $em->getRepository('Library\Model\Category\Category')->findBy(['parent_category' => null]);
        $sub_categories_table = [];

        /** @var Category $main_category */
        foreach ($main_categories as $main_category)
        {
            $sub_categories = $em->getRepository('Library\Model\Category\Category')->findBy(['parent_category' => $main_category]);

            if (!isset($sub_categories_table[$main_category->getId()]))
                $sub_categories_table[$main_category->getId()] = [];

            // Add subcategories to table
            if (count($sub_categories) > 0)
            {
                /** @var Category $sub_category */
                foreach ($sub_categories as $sub_category)
                {
                    $sub_categories_table[$main_category->getId()][] = [
                        $sub_category->getId(),
                        $sub_category->getName()
                    ];
                }
            }
        }

        // Get categories for category dialog popup
        $category_options = [];
        $category_listings = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy();

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

        // Get theme categories for theme list
        $themes = $em->getRepository('Library\Model\Category\Category')->findBy(['parent_category' => $em->getReference('Library\Model\Category\Category', Category::THEME_CATEGORY_ID)]);

        // Return variables to view
        return [
            'themes' => $themes,
            'category_options' => $category_options,
            'sub_categories_table' => $sub_categories_table,
            'main_categories' => $main_categories,
            'product_category_listings' => $product_category_listings,
            'theme_ids' => $theme_ids,
            'sku_information' => $sku_information,
            'create_update_form' => $this->create_update_form,
            'add_images_form' => $this->add_images_form,
            'add_skus_form' => $this->add_skus_form,
            'additional_images' => $additional_image_info,
            'additional_videos' => $additional_video_info,
            'main_photo' => isset($main_photo) ? $main_photo : null,
            'product' => $this->product
        ];
    }

    /**
     * Hydrates the product form from a loaded product.
     * @return array
     */
    public function hydrate_product_form()
    {
        $em = EntityManagerSingleton::getInstance();

        $this->create_update_form->get('name')->setValue($this->product->getName());

        $this->create_update_form->get('product_id')->setValue($this->product->getId());
        $this->create_update_form->get('base_price')->setValue($this->product->getBasePrice());
        $this->create_update_form->get('discount_price')->setValue($this->product->getDiscountPrice());
        $this->create_update_form->get('tax')->setValue($this->product->getTax());
        $this->create_update_form->get('base_weight')->setValue($this->product->getBaseWeight());
        $this->create_update_form->get('description')->setValue($this->product->getDescription());
        $this->create_update_form->get('meta_description')->setValue($this->product->getPage()->getDescription());
        $this->create_update_form->get('keywords')->setValue($this->product->getPage()->getKeywords());
        $this->create_update_form->get('date_added')->setValue($this->product->getDateCreated()->format('m/d/Y'));
        $this->create_update_form->get('show_more_caption')->setValue($this->product->shouldShowMoreCaption());

        // Get the quantity and status from the skus
        $quantity = $this->product->getQuantityFromSkus();
        $status_override = $this->product->getStatusOverride();
        $status = $this->product->getStatus();

        if (!($status_override instanceof Status))
            $this->create_update_form->get('status_override')->setValue(0);
        else
            $this->create_update_form->get('status_override')->setValue($status_override->getId());

        if (!($status instanceof Status))
            $this->create_update_form->get('status')->setValue(0);
        else
            $this->create_update_form->get('status')->setValue($status->getId());


        $this->create_update_form->get('quantity')->setValue($quantity);
        $this->create_update_form->get('sort_order')->setValue($this->product->getSortOrder());
        $this->create_update_form->get('product_code')->setValue($this->product->getProductCode());

        // Place themes and categories in the category box
        $category_listings = [];
        $theme_ids = [];
        $ancestor_listing_name = "";
        $product_categories = $this->product->getProductCategories();

        foreach($product_categories as $product_category)
        {
            $category = $product_category->getCategory();

            // Place theme categories in the theme check box list
            if ($category->getId() == Category::THEME_CATEGORY_ID || (!is_null($category->getParentCategory()) && $category->getParentCategory()->getId() == Category::THEME_CATEGORY_ID))
            {
                $theme_ids[] = $category->getId();
            }
            else
            {
                $category_info = $em->getRepository('Library\Model\Category\Category')->findCategoryAncestors($category);
                $category_ancestors = $category_info['ancestors'];

                if (count($category_ancestors) > 0)
                {
                    // Construct listing name
                    if (count($category_ancestors) > 0)
                    {
                        foreach ($category_ancestors as $ancestor)
                        {
                            $ancestor_listing_name .= $ancestor['name'] . " >> ";
                        }
                    }
                }

                $category_name = $ancestor_listing_name . $category->getName();
                $category_listings[] = ['id' => $category->getId(), 'name' => $category_name];
                $ancestor_listing_name = "";
            }
        }

        return [$category_listings, $theme_ids];
    }

    /**
     * Handles all of the posts that involve saving, updating and deleting products
     * @return array
     *
     * @throws \Exception
     */
    private function _handle_post()
    {
        $product_service = $this->getServiceLocator()->get('product');
        $sku_service = $this->getServiceLocator()->get('sku');

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $files = $this->getRequest()->getFiles()->toArray();
            $data = array_merge_recursive($data, $files);
            $json_image_data = [];

            $task = $data['task'];
            unset($data['task']);

            // Perform action based on task
            switch ($task)
            {
                // Saves and updates a product's general information
                case 'create_update':

                    // Validate form
                    $this->create_update_form->setData($data);

                    if ($this->create_update_form->isValid())
                    {
                        // Collect data and save
                        $valid_data = $this->create_update_form->getData();
                        $this->product = $product_service->save($valid_data, $this->product_id);
                        EntityManagerSingleton::getInstance()->flush();

                        // Refresh the page
                        return $this->redirect()->toUrl($_SERVER['REDIRECT_URL'] . '?id=' . $this->product->getId());
                    }
                    break;

                // Get option values from option to popuplate sku option value select box
                case 'fetch_option_values':

                    $option = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Product\Option')->findOneById($data['option_id']);
                    $option_value_set = [];
                    if (!is_null($option))
                    {
                        $option_value_rels = $option->getOptionOptionValues();
                        if (count($option_value_rels) > 0)
                        {
                            foreach ($option_value_rels as $option_value_rel)
                            {
                                $option_value_set[$option_value_rel->getOptionValue()->getId()] = $option_value_rel->getOptionValue()->getName();
                            }
                        }
                    }

                    return ['error' => false, 'option_values' => $option_value_set];
                    break;

                // Saves additional photos to product
                case 'add_additional_photos':

                    $product_id = $data['product_id'];
                    unset($data['product_id']);

                    if (empty($product_id))
                    {
                        throw new \Exception("A product must first be created before adding additional images to it.");
                    }

                    // Validate form
                    $this->add_images_form->setData(['image' => $data]);

                    if ($this->add_images_form->isValid())
                    {
                        // Collect data and save
                        $valid_data = $this->add_images_form->getData();
                        $this->product = $product_service->addAdditionalImages($valid_data, $product_id);
                    }

                    // Save database changes
                    EntityManagerSingleton::getInstance()->flush();
                    $image_path = Settings::get('image_path');

                    // Get additional images to send back to javascript
                    $additional_image_rels = $this->product->getProductImages();
                    if (count($additional_image_rels) > 0)
                    {
                        $json_image_data = [];
                        foreach ($additional_image_rels as $additional_image_rel)
                        {
                            $json_image_data[] = [
                                'rel_id' => $additional_image_rel->getId(),
                                'url' => $image_path . '/product_images/' . $additional_image_rel->getImage()->getUrl(),
                                'alt' => $additional_image_rel->getImage()->getAlt(),
                                'title' => $additional_image_rel->getImage()->getTitle(),
                                'sort_order' => $additional_image_rel->getSortOrder()
                            ];
                        }
                    }

                    // Send info to javascript
                    return ['error' => false, 'images' => $json_image_data];
                    break;

                // Add a video to a product
                case 'add_additional_video':

                    $product_video = $product_service->addAdditionalVideo($data, $this->product);
                    EntityManagerSingleton::getInstance()->flush();

                    $video_code = <<< EOD
                    <div data-rel-id='{$product_video->getId()}' class="multi_video inline">
                        {$product_video->getVideo()->getEmbedCode(100, 80)}<br/>
                        <a class='remove_video' href=''>[Remove]</a>
                    </div>
EOD;

                    return ['error' => false, 'video_code' => $video_code];
                    break;

                // Removes an additional photo
                case 'remove_additional_photo':

                    $product_service->deleteByIds([$data['rel_id']], new ProductImage());
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                // Removes an additional photo
                case 'remove_additional_video':

                    $product_service->deleteByIds([$data['rel_id']], new ProductVideo());
                    EntityManagerSingleton::getInstance()->flush();

                    return ['error' => false];
                    break;

                // Update skus
                case 'update_skus':

                    // Find product
                    $refresh_page = $sku_service->saveToProduct($data, $this->product_id);
                    EntityManagerSingleton::getInstance()->flush();

                    $refresh = $refresh_page == SkuService::FLAG_DONT_REFRESH_PAGE ? false : true;

                    return ['error' => false, 'refresh' => $refresh];
                    break;

                // Upload images for products before saving
                case 'upload_images':
                    $image_service = $this->getServiceLocator()->get('image');
                    $images = $image_service->save($files);
                    EntityManagerSingleton::getInstance()->flush();

                    // Return an array of the image ids and urls
                    $image_info = [];
                    if (count($images) > 0)
                    {
                        foreach ($images as $image)
                        {
                            $image_info[$image->getId()] = $image->getUrl();
                        }
                    }

                    return ['error' => false, 'images' => $image_info];
                    break;

                // Get information to add new sku
                case 'get_new_sku_info':

                    // Get options
                    $options = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Product\Option')->findBy(['id' => $data['options']]);
                    $option_values = [];
                    if (count($options) > 0)
                    {
                        foreach ($options as $option)
                        {
                            if ($option instanceof Option)
                            {
                                $option_values[$option->getId()][$option->getName()] = [];
                                $option_value_rels = $option->getOptionOptionValues();
                                foreach ($option_value_rels as $option_value_rel)
                                {
                                    $option_id = $option->getId();
                                    $option_name = $option->getName();

                                    $option_values[$option_id][$option_name][$option_value_rel->getOptionValue()->getId()] = $option_value_rel->getOptionValue()->getName();
                                }
                            }
                        }
                    }

                    // Get product statuses
                    $statuses = [];
                    $product_statuses = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Product\Status')->findAll();
                    if (count($product_statuses) > 0)
                    {
                        foreach ($product_statuses as $product_status)
                        {
                            $statuses[$product_status->getId()] = $product_status->getName();
                        }
                    }

                    // Create HTML to render to screen
                    $print_sku_dialog = $this->getServiceLocator()->get('viewhelpermanager')->get('print_sku_dialog');
                    $sku_dialog = $print_sku_dialog(null, $options, $statuses);

                    return ['error' => false, 'sku_dialog_html' => $sku_dialog];
                    break;

                // Add a new option value to the option
                case 'add_new_option_value':

                    $new_value_name = trim($data['value_name']);
                    $option = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Product\Option')->findOneById($data['option_id']);

                    if (!($option instanceof Option))
                    {
                        throw new \Exception("The option that you're adding a new value to has been removed from the database.");
                    }

                    // Get option values
                    $option_option_values = $option->getOptionOptionValues();
                    $option_value = null;

                    foreach ($option_option_values as $option_option_value)
                    {
                        if (strtolower($option_option_value->getOptionValue()->getName()) == strtolower($new_value_name))
                        {
                            $option_value = $option_option_value->getOptionValue();
                            break;
                        }
                    }

                    // Search for the value by name
                    if (!($option_value instanceof OptionValue))
                    {
                        $is_new = true;
                        $option_value = new OptionValue();
                        $option_value->setName($new_value_name);
                        EntityManagerSingleton::getInstance()->persist($option_value);

                        // Create a new Option Option Value relationship
                        $option_option_value = new OptionOptionValue();
                        $option_option_value->setOption($option);
                        $option_option_value->setOptionValue($option_value);
                        EntityManagerSingleton::getInstance()->persist($option_option_value);
                    }
                    else
                    {
                        $is_new = false;
                    }

                    EntityManagerSingleton::getInstance()->flush();

                    // Send the option value ID back to javascript
                    return ['error' => false, 'option_value_id' => $option_value->getId(),
                        'option_value_name' => $option_value->getName(), 'is_new' => $is_new];

                    break;
            }
        }

        return null;
    }

    /**
     * Fill in forms on sku information
     */
    public function hydrate_skus_form()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get product options
        $sku_info = [];
        $options = $em->getRepository('Library\Model\Product\Option')->findByProduct($this->product_id);

        $sku_info['product_options'] = $options;

        // Get skus
        $sku_info['skus'] = $this->product->getSkus();

        // Get status options
        $sku_info['status_options'] = $this->status_options;

        return $sku_info;
    }

    /**
     * Builds the forms necessary
     */
    public function build_forms()
    {
        // Get list of categories to populate the category selector
        $em = EntityManagerSingleton::getInstance();

        $category_options = [];
        $category_listings = $em->getRepository('Library\Model\Category\Category')->findAllWithHierarchy();

        // Get list of categories that are theme categories so that they are are not included
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->eq('id', Category::THEME_CATEGORY_ID))->orWhere($criteria->expr()->eq('parent_category', $em->getReference('Library\Model\Category\Category', Category::THEME_CATEGORY_ID)));
        $theme_categories = $em->getRepository('Library\Model\Category\Category')->matching($criteria);
        $all_theme_category_ids = [];
        if ($theme_categories->count() > 0)
        {
            /** @var Category $theme_category */
            foreach ($theme_categories as $theme_category)
                $all_theme_category_ids[] = $theme_category->getId();
        }

        if (!empty($category_listings))
        {
            foreach($category_listings as $listing)
            {
                // Don't include theme categories in the list
                if (in_array($listing['id'], $all_theme_category_ids))
                    continue;

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

        // Get list of statuses to list for products
        $this->status_options = [];
        $status_listings = $em->getRepository('Library\Model\Product\Status')->findAll();
        $this->status_options[0] = "None";

        if (count($status_listings) > 0)
        {
            foreach ($status_listings as $staus_listing)
            {
                $this->status_options[$staus_listing->getId()] = $staus_listing->getName();
            }
        }

        // Get list of options for skus form
        $option_list = [];
        $options = $em->getRepository('Library\Model\Product\Option')->findAll();
        if (!empty($options))
        {
            foreach ($options as $option)
            {
                $option_list[$option->getId()] = $option->getName();
            }
        }

        // Add content to forms
        $this->create_update_form->get('category')->setAttribute('options', $category_options);
        $this->create_update_form->get('status_override')->setAttribute('options', $this->status_options);
        $this->create_update_form->get('status')->setAttribute('options', $this->status_options);
        $this->add_skus_form->get('options')->setAttribute('options', $option_list);
    }

    /**
     * Get images to popuplate the additional image fields
     * @return array
     */
    public function hydrate_additional_image_form()
    {
        $additional_image_info = [];
        $product_image_relationships = $this->product->getProductImages();
        if (count($product_image_relationships) > 0)
        {
            foreach ($product_image_relationships as $product_image_relationship)
            {
                $additional_image_info[] =
                    [
                        'rel_id' => $product_image_relationship->getId(),
                        'url' => $product_image_relationship->getImage()->getUrl(),
                        'title' => $product_image_relationship->getImage()->getTitle(),
                        'alt' => $product_image_relationship->getImage()->getAlt()
                    ];
            }
        }

        return $additional_image_info;
    }

    /**
     * Get videos to popuplate the additional video fields
     * @return array
     */
    public function hydrate_additional_video_form()
    {
        $additional_video_info = [];

        $em = EntityManagerSingleton::getInstance();

        $product_video_relationships = $em->getRepository('Library\Model\Relationship\ProductVideo')->findBy(['product' => $this->product], ['sort_order' => 'DESC']);
        if (count($product_video_relationships) > 0)
        {
            foreach ($product_video_relationships as $product_video_relationship)
            {
                $additional_video_info[] =
                    [
                        'rel_id' => $product_video_relationship->getId(),
                        'embed_code' => $product_video_relationship->getVideo()->getEmbedCode(100, 80)
                    ];
            }
        }

        return $additional_video_info;
    }
}