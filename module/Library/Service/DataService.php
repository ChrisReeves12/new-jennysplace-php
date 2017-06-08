<?php
/**
* The DataService class definition.
*
* This class holds various datafix commands
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Doctrine\ORM\Tools\SchemaTool;
use Library\Model\Shop\ShopList\QueryList;
use Library\Model\Shop\ShopListElement;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class DBService
 * @package Library\Service
 */
class DataService extends AbstractService
{
    /**
     * Updates database schema
     */
    public function updateDatabase()
    {
        try
        {
            $em = EntityManagerSingleton::getInstance();

            $schema_tool = new SchemaTool($em);
            $schema_tool->updateSchema($em->getMetadataFactory()->getAllMetadata());
        }
        catch (\Exception $ex)
        {
            echo $ex->getMessage() . '\n';
            exit(1);
        }
    }

    /**
     * Creates and/or populates a query list in the database
     *
     * @param string $list_name
     */
    public function buildQueryList($list_name)
    {
        try
        {
            $em = EntityManagerSingleton::getInstance();

            // Get the query list
            $query_list = $em->getRepository('Library\Model\Shop\ShopList\QueryList')->findOneByName($list_name);
            $new_list = false;

            // Create a new list if it doesn't exist
            if (!($query_list instanceof QueryList))
            {
                $query_list = new QueryList();
                $new_list = true;
                $query_list->setDateModified();
                $query = '';
                $query_list->setQuery($query);
                $query_list->setName($list_name);
                $em->persist($query_list);

                $em->flush();
            }

            // Populate the list with the query
            if (!$new_list)
            {
                $query = $query_list->getQuery();
                $connection = $em->getConnection();

                if (!is_null($query_list->getId()))
                {
                    $connection->executeQuery("DELETE FROM `shop_list_elements` WHERE `shop_list_elements`.shop_list_id = " . $query_list->getId());
                }

                $results = $connection->fetchAll($query);
                $skus = $this->getSkusFromResults($results);

                $count = 0;

                if (!empty($skus))
                {
                    foreach ($skus as $sku)
                    {
                        $shop_list_line_item = new ShopListElement();
                        $shop_list_line_item->setName($sku->getProduct()->getName());
                        $shop_list_line_item->setSku($sku);
                        $shop_list_line_item->setSortOrder($count);
                        $shop_list_line_item->setQuantity(1);
                        $shop_list_line_item->setNumber($sku->getProduct()->getProductCode());
                        $shop_list_line_item->setShopList($query_list);
                        $shop_list_line_item->setStatus($sku->getRealStatus());
                        $shop_list_line_item->setPrice($sku->getProduct()->getBasePrice());
                        $shop_list_line_item->setWeight(0);
                        $shop_list_line_item->setTax(0);
                        $count++;

                        $em->persist($shop_list_line_item);
                    }
                }
            }

            $em->flush();
        }
        catch (\Exception $ex)
        {
            echo $ex->getMessage() . '\n';
            exit(1);
        }
    }

    /**
     * Get skus from results from a database
     *
     * @param $results
     * @return array
     */
    private function getSkusFromResults($results)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $sku_ids = [];

        if (!empty($results))
        {
            foreach ($results as $result)
            {
                $sku_ids[] = $result['sku_id'];
            }
        }

        $skus = $em->getRepository('Library\Model\Product\Sku')->findBy(['id' => $sku_ids]);
        return $skus;
    }
}