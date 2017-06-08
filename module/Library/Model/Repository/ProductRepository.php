<?php
/**
* The ProductRepository class definition.
*
* Contains finder methods for querying products
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Library\Model\Product\Product;
use Library\Model\Product\Status;
use Library\Model\Shop\ShopList;
use Library\Service\DB\EntityManagerSingleton;
use Library\Model\Category\Category;

/**
 * Class ProductRepository
 * @package Library\Model\Repository
 */
class ProductRepository extends EntityRepository
{
    /**
     * Returns a list of products in category filtering matching only the override status
     *
     * @param Category $category
     * @param Status $product_status
     * @param int $first_page,
     * @param int $max_results,
     * @return Product[]
     */
    public function findByCategoryAndStatus($category, $product_status, $first_page = 0, $max_results = 100)
    {
        $em = EntityManagerSingleton::getInstance();
        $qb = $em->createQueryBuilder();

        $qb->select('p')->from('Library\Model\Product\Product', 'p');
        $qb->innerJoin('Library\Model\Relationship\ProductCategory', 'pc', 'WITH', 'pc.product = p');
        $qb->innerJoin('Library\Model\Product\Status', 's', 'WITH', 'p.status = s');
        $qb->where('p.status = :product_status');
        $qb->andWhere('pc.category = :category');
        $qb->setFirstResult($first_page);
        $qb->setMaxResults($max_results);
        $qb->orderBy('pc.sort_order', 'DESC');
        $qb->setParameters(['product_status' => $product_status, 'category' => $category]);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns products in sort order by category
     *
     * @param Category $category
     * @param int $first_result,
     * @param int $max_results,
     * @return Product[]
     */
    public function findByCategoryInOrder(Category $category, $first_result, $max_results)
    {
        $em = EntityManagerSingleton::getInstance();
        $qb = $em->createQueryBuilder();

        if (is_null($category->getQueryList()))
        {
            $qb->select('p')->from('Library\Model\Product\Product', 'p');
            $qb->join('Library\Model\Page\Page', 'pg', 'WITH', 'p.page = pg');
            $qb->join('Library\Model\Relationship\ProductCategory', 'pc', 'WITH', 'pc.product = p');
            $qb->where('pc.category = :category');
            $qb->andWhere($qb->expr()->eq('pg.inactive', 0));
            $qb->andWhere("p.status = :status");
            //$qb->orderBy('pc.sort_order', 'DESC');
            $qb->orderBy('pc.date_created', 'DESC');
            $qb->setParameters(['category' => $category, 'status' => $em->getReference('Library\Model\Product\Status', 1)]);
            $qb->setFirstResult($first_result);
            $qb->setMaxResults($max_results);
        }
        else
        {
            $shop_list = $category->getQueryList();
            $qb->select('p')->from('Library\Model\Product\Product', 'p');
            $qb->innerJoin('Library\Model\Product\Sku', 's', 'WITH', 's.product = p');
            $qb->innerJoin('Library\Model\Shop\ShopListElement', 'sle', 'WITH', 'sle.sku = s');
            $qb->innerJoin('Library\Model\Shop\ShopList\QueryList', 'ql', 'WITH', 'ql = sle.shop_list');
            $qb->where('ql.id = :shop_list');
            $qb->setFirstResult($first_result);
            $qb->setMaxResults($max_results);
            $qb->setParameters(['shop_list' => $shop_list->getId()]);
        }

        $results = $qb->getQuery()->getResult();
        return $results;
    }

    /**
     * Returns a the products represented in a shop list
     *
     * @param ShopList $shop_list
     *
     * @param $first_result
     * @param $max_results
     *
     * @return array
     */
    public function findByShopList(ShopList $shop_list, $first_result, $max_results)
    {
        $em = EntityManagerSingleton::getInstance();
        $qb = $em->createQueryBuilder();
        $qb->select('p')->from('Library\Model\Product\Product', 'p');
        $qb->innerJoin('Library\Model\Page\Page', 'pg', 'WITH', 'p.page = pg');
        $qb->innerJoin('Library\Model\Product\Sku', 's', 'WITH', 's.product = p');
        $qb->innerJoin('Library\Model\Shop\ShopListElement', 'sle', 'WITH', 'sle.sku = s');
        $qb->innerJoin('Library\Model\Shop\ShopList', 'sl', 'WITH', 'sl = sle.shop_list');
        $qb->where('sl = :shop_list');
        $qb->andWhere($qb->expr()->eq('pg.inactive', 0));
        $qb->setFirstResult($first_result);
        $qb->setMaxResults($max_results);
        $qb->setParameters(['shop_list' => $shop_list]);

        $results = $qb->getQuery()->getResult();
        return $results;
    }

    /**
     * Returns the total amount of products under a category
     *
     * @param Category $category
     * @return int
     */
    public function getProductCount(Category $category)
    {
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();

        if (is_null($category->getQueryList()))
        {
            $qb->select($qb->expr()->count('p'))->from('Library\Model\Product\Product', 'p');
            $qb->innerJoin('Library\Model\Page\Page', 'pg', 'WITH', 'p.page = pg');
            $qb->innerJoin('Library\Model\Relationship\ProductCategory', 'pc', 'WITH', 'pc.product = p');
            $qb->where('pc.category = :category');
            $qb->andWhere($qb->expr()->eq('pg.inactive', 0));
            $qb->andWhere("p.status = :status");
            $qb->setParameters(['category' => $category, 'status' => $em->getReference('Library\Model\Product\Status', 1)]);
        }
        else
        {
            $qb->select($qb->expr()->count('p'))->from('Library\Model\Product\Product', 'p');
            $qb->innerJoin('Library\Model\Product\Sku', 's', 'WITH', 's.product = p');
            $qb->innerJoin('Library\Model\Shop\ShopListElement', 'sle', 'WITH', 'sle.sku = s');
            $qb->innerJoin('Library\Model\Shop\ShopList\QueryList', 'ql', 'WITH', 'ql = sle.shop_list');
            $qb->where('ql.id = :shop_list');
            $qb->setParameters(['shop_list' => $category->getQueryList()]);
        }

        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count;
    }

    /**
     * Find products by keywords for search
     *
     * @param string $keywords
     * @param int $first_result
     * @param int $max_results
     * @return Product[]
     */
    public function findByKeywords($keywords, $first_result, $max_results)
    {
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select('p')->from('Library\Model\Product\Product', 'p');
        $qb->join('Library\Model\Page\Page', 'pg', 'WITH', 'p.page = pg');
        $qb->join('Library\Model\Relationship\ProductCategory', 'pc', 'WITH', 'pc.product = p');
        $qb->join('Library\Model\Category\Category', 'c', 'WITH', 'c = pc.category');
        $qb->join('Library\Model\Product\Sku', 's', 'WITH', 's.product = p');
        $qb->where($qb->expr()->like('pg.keywords', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('s.number', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.title', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.description', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.url_handle', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('c.name', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('p.name', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('p.product_code', "'%{$keywords}%'"));
        $qb->andWhere($qb->expr()->eq('pg.inactive', 0));
        $qb->andWhere("p.status = :status")->setParameter('status', $em->getReference('Library\Model\Product\Status', 1));
        $qb->orderBy('p.date_created', 'DESC');

        $results = $qb->getQuery()->getResult();

        // Paginate results
        $pages_of_products = array_chunk($results, $max_results, true);
        $products = $pages_of_products[$first_result];

        return $products;
    }

    /**
     * Find number of products total, that would be returned from the search
     *
     * @param string $keywords
     * @return int
     */
    public function findByKeywordsCount($keywords)
    {
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select($qb->expr()->countDistinct('p'))->from('Library\Model\Product\Product', 'p');
        $qb->join('Library\Model\Page\Page', 'pg', 'WITH', 'p.page = pg');
        $qb->join('Library\Model\Relationship\ProductCategory', 'pc', 'WITH', 'pc.product = p');
        $qb->join('Library\Model\Category\Category', 'c', 'WITH', 'c = pc.category');
        $qb->join('Library\Model\Product\Sku', 's', 'WITH', 's.product = p');
        $qb->where($qb->expr()->like('pg.keywords', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('s.number', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.title', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.description', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('pg.url_handle', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('c.name', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('p.name', "'%{$keywords}%'"));
        $qb->orWhere($qb->expr()->like('p.product_code', "'%{$keywords}%'"));
        $qb->andWhere($qb->expr()->eq('pg.inactive', 0));
        $qb->andWhere("p.status = :status")->setParameter('status', $em->getReference('Library\Model\Product\Status', 1));

        $count = (int) $qb->getQuery()->getSingleScalarResult();
        return $count;
    }
}