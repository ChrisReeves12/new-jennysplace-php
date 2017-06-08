<?php
/**
* The AbstractService class definition.
*
* This service is the basis for which all other service come from
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\AbstractModel;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class AbstractService
 * @package Library\Service
 */
class AbstractServiceBackup
{
    /**
     * Deletes entities by an array of ids
     *
     * @param $ids[]
     * @param AbstractModel $entity
     */
    static public function deleteByIds($ids, $entity)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get ids
        if (!is_array($ids))
        {
            $ids = explode(',', $ids);
        }

        $entity_class = get_class($entity);
        $entities = $em->getRepository($entity_class)->findBy(['id' => $ids]);

        if (count($entities) > 0)
        {
            foreach ($entities as &$entity)
            {
                if ($entity instanceof AbstractModel)
                {
                    // Delete the entity
                    $em->remove($entity);
                }
            }
        }
    }
}