<?php
/**
* The CategoryRepository class definition.
*
* This class performs various filter finder functions for getting category information.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Library\Model\Category\Category;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class CategoryRepository
 * @package Library\Model\Repository
 */
class CategoryRepository extends EntityRepository
{
    /**
     * Returns a formatted array of the categories along with the ancestors in hierarchical order
     *
     * @param null $excluded_category_id
     * @param null $first_result
     * @param null $max_results
     *
     * @return array
     */
    public function findAllWithHierarchy($excluded_category_id = null, $first_result = null, $max_results = null)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get all categories
        $qb = $em->createQueryBuilder();
        $qb->select('c')->from('Library\Model\Category\Category', 'c');

        if (!is_null($first_result))
            $qb->setFirstResult($first_result);

        if (!is_null($max_results))
            $qb->setMaxResults($max_results);

        $cats = $qb->getQuery()->getResult();

        // Create hierarchical listing
        $cat_listing = [];
        if (count($cats) > 0)
        {
            foreach ($cats as $cat)
            {
                // Exclude the category if given
                if (!is_null($excluded_category_id) && $cat->getId() == $excluded_category_id)
                {
                    continue;
                }

                $done = false;
                $cat_ancestry = [];
                $category = $cat;

                while($done === false)
                {
                    $parent = $category->getParentCategory();
                    if (!($parent instanceof Category))
                    {
                        $done = true;
                    }
                    else
                    {
                        $cat_ancestry[] = $parent;
                        $category = $parent;
                    }
                }

                $cat_ancestry_listing = [];
                if (!empty($cat_ancestry))
                {
                    for ($x = (count($cat_ancestry)-1); $x >= 0; $x--)
                    {
                        $cat_ancestor = $cat_ancestry[$x];

                        $cat_ancestry_listing[] = [
                            'id' => $cat_ancestor->getId(),
                            'name' => $cat_ancestor->getName(),
                            'is_inactive' => $cat_ancestor->getInactive()
                        ];
                    }
                }

                // Add the listing
                $cat_listing[] = [
                    'id' => $cat->getId(),
                    'name' => $cat->getName(),
                    'is_inactive' => $cat->getInactive(),
                    'ancestors' => $cat_ancestry_listing
                ];
            }
        }

        return $cat_listing;
    }

    /**
     * Returns an array of category information with ancestors
     * @param Category $category
     *
     * @return array
     */
    public function findCategoryAncestors(Category $category)
    {
        $done = false;
        $cat_ancestry = [];
        $original_category = $category;

        while($done === false)
        {
            $parent = $category->getParentCategory();
            if (!($parent instanceof Category))
            {
                $done = true;
            }
            else
            {
                $cat_ancestry[] = $parent;
                $category = $parent;
            }
        }

        $cat_ancestry_listing = [];
        if (!empty($cat_ancestry))
        {
            for ($x = (count($cat_ancestry)-1); $x >= 0; $x--)
            {
                $cat_ancestor = $cat_ancestry[$x];

                $cat_ancestry_listing[] = [
                    'id' => $cat_ancestor->getId(),
                    'name' => $cat_ancestor->getName(),
                    'is_inactive' => $cat_ancestor->getInactive()
                ];
            }
        }

        // Add the listing
        return [
            'id' => $original_category->getId(),
            'name' => $original_category->getName(),
            'is_inactive' => $original_category->getInactive(),
            'ancestors' => $cat_ancestry_listing
        ];
    }

    /**
     * Returns the sub categories in sort order of the category
     * @param Category $category
     *
     * @return array
     */
    public function findSubCategories(Category $category)
    {
        $em = EntityManagerSingleton::getInstance();
        $qb = $em->createQueryBuilder();
        $qb->select('c')->from('Library\Model\Category\Category', 'c')
            ->where('c.parent_category = :category')
            ->andWhere('c.inactive = :inactive')
            ->orderBy('c.sort_order', 'DESC')
            ->setParameters(['category' => $category, 'inactive' => false]);

        $results = $qb->getQuery()->getResult();
        return $results;
    }
}