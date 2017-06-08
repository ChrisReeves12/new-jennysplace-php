<?php
/**
* The ProductViewStrategy class definition.
*
* This strategy renders categories in the list view
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\ViewStrategy\Backend;
use Library\Model\Media\Image;
use Library\Model\Product\Product;
use Library\Service\DB\EntityManagerSingleton;
use Library\Service\Settings;

/**
 * Class ProductListViewStrategy
 * @package Library\Model\ViewStrategy\Backend
 */
class ProductListViewStrategy extends GenericListViewStrategy
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
        $qb->addOrderBy('e.date_created', 'DESC');
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
            /** @var Product $result */
            foreach ($this->list_results as &$result)
            {
                // Add parameters directly
                $new_result['Id'] = $result->getId();

                // Show image
                $default_image = $result->getDefaultImage();
                $image_path = Settings::get('image_path');

                if ($default_image instanceof Image)
                {
                    $new_result['Image'] = "<div class='thumbnail-image'><a data-fancybox-group='gallery' class='fancybox-effects-d' href='".$image_path."/product_images/".$default_image->getUrl()."'><img src='".$image_path."/product_images/".$default_image->getUrl()."'/></a></div>";
                }
                else
                {
                    $new_result['Image'] = "<div class='thumbnail-image'><img src='".$image_path."/layout_images/no_image.jpg'/></div>";
                }

                $new_result['Product Code'] = $result->getProductCode();

                $new_result['Name'] = $result->getName();
                $new_result['Base Price'] = $result->getBasePrice();
                $new_result['Discount Price'] = $result->getDiscountPrice();
                $new_result['Status'] = $result->getStatus()->getName();
                $new_result['On Hand'] = $result->getQuantityFromSkus();
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

            $qb->innerJoin('Library\Model\Product\Status', 's', 'WITH', 's = e.status');

            // Handle filters for search
            switch ($filter)
            {
                case 'status':
                    $qb->where($qb->expr()->like('s.name', ':keyword'));
                    break;

                default:
                    $qb->where($qb->expr()->like('e.'.$filter, ':keyword'));
                    break;
            }

            $qb->setFirstResult(($this->page_id - 1) * $this->max_row_view)->setMaxResults($this->max_row_view);
            $qb->addOrderBy('e.date_created', 'DESC');
            $qb->setParameter('keyword', "%{$keyword}%");
            $this->list_results = $qb->getQuery()->getResult();
        }
    }
}