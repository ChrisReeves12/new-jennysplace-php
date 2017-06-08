<?php
/**
* The OptionRepository class definition.
*
* This class handles all of the functions needed to create, read and update options for products
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Library\Model\Category\Category;
use Library\Model\Product\Option;
use Library\Model\Product\Product;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class OptionRepository
 * @package Library\Model\Repository
 */
class OptionRepository extends EntityRepository
{
    /**
     * Deletes several options by passing an array of ids
     * @param int[] $option_ids
     */
    public function deleteByIds($option_ids)
    {
        $em = EntityManagerSingleton::getInstance();

        $options = $em->getRepository('Library\Model\Product\Option')->findBy(['id' => $option_ids]);
        if (count($options) > 0)
        {
            foreach ($options as &$option)
            {
                if ($option instanceof Category)
                {
                    // Delete the option
                    $em->remove($option);
                }
            }
        }
    }

    /**
     * Returns the options associated with the skus under a product if applicaple
     *
     * @param int $product_id
     * @return Option[]
     *
     * @throws \Exception
     */
    public function findByProduct($product_id)
    {
        $em = EntityManagerSingleton::getInstance();

        // Get the product
        $product = $em->getRepository('Library\Model\Product\Product')->findOneById($product_id);
        if (false === ($product instanceof Product))
        {
            throw new \Exception("The product ID being passed does not match a product in the database.");
        }

        // Find the skus and options under the product
        $qb = $em->createQueryBuilder();
        $qb->select('o')->from('Library\Model\Product\Option', 'o');
        $qb->join('Library\Model\Relationship\OptionOptionValue', 'oov', 'WITH', 'o = oov.option');
        $qb->join('Library\Model\Relationship\SkuOptionOptionValue', 'soov', 'WITH', 'oov = soov.option_option_value');
        $qb->join('Library\Model\Product\Sku', 's', 'WITH', 's = soov.sku');
        $qb->join('Library\Model\Product\Product', 'p', 'WITH', 'p = s.product');
        $qb->where($qb->expr()->eq('s.is_default', '0'))->andWhere($qb->expr()->eq('p', ':product'));
        $qb->setParameter('product', $product);

        $results = $qb->getQuery()->getResult();
        return $results;
    }
}