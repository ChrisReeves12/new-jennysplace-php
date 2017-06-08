<?php
/**
* The SkuRepository class definition.
*
* This repository class queries skus using various functions
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Repository;

use Doctrine\ORM\EntityRepository;
use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class SkuRepository
 * @package Library\Model\Repository
 */
class SkuRepository extends EntityRepository
{
    /**
     * Finds a sku by product and option values
     *
     * @param Product $product
     * @param $data
     *
     * @return Sku
     */
    public function findOneByOptions(Product $product, $data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get option value relationships
        $qb = $em->createQueryBuilder();
        $qb->select('partial oov.{id}')->from('Library\Model\Relationship\OptionOptionValue', 'oov');
        $qb->join('Library\Model\Product\Option', 'o', 'WITH', 'o = oov.option');
        $qb->join('Library\Model\Product\OptionValue', 'ov', 'WITH', 'ov = oov.option_value');

        $count = 0;

        foreach ($data as $key => $value)
        {
            if ($count == 0)
            {
                $qb->where("o.id = {$key} AND ov.id = {$value}");
            }
            else
            {
                $qb->orWhere("o.id = {$key} AND ov.id = {$value}");
            }

            $count++;
        }

        $option_option_values = $qb->getQuery()->getArrayResult();
        $assoc_ids = [];

        foreach ($option_option_values as $option_option_value)
        {
            $assoc_ids[] = (int) $option_option_value['id'];
        }

        asort($assoc_ids);

        $skus = $product->getSkus();
        $match_found = false;
        $sku = null;

        if (count($skus) > 0)
        {
            foreach ($skus as $sku)
            {
                $soovs = $sku->getSkuOptionOptionValues()->toArray();
                $option_value_info = [];
                if (count($soovs) > 0)
                {
                    foreach ($soovs as $soov)
                    {
                        $option_value_info[] = $soov->getOptionOptionValue()->getId();
                    }
                }
                asort($option_value_info);

                if ($option_value_info == $assoc_ids)
                {
                    $match_found = true;
                }

                if ($match_found)
                {
                    break;
                }
            }
        }

        if ($match_found)
        {
            return $sku;
        }
        else
        {
            return null;
        }
    }
}