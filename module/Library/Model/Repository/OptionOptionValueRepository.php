<?php
/**
 * The OptionOptionValueRepository class definition.
 *
 * The description of the class
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\Repository;
use Doctrine\ORM\EntityRepository;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class OptionOptionValueRepository
 * @package Library\Model\Repository
 */
class OptionOptionValueRepository extends EntityRepository
{
    /**
     * Gets the relation association between options and values based on ids of each
     *
     * @param $option_id
     * @param $value_id
     *
     * @return array
     */
    public function findOneByIdAndValueId($option_id, $value_id)
    {
        $em = EntityManagerSingleton::getInstance();

        $qb = $em->createQueryBuilder();
        $qb->select('oov')
            ->from('Library\Model\Relationship\OptionOptionValue', 'oov')
            ->innerJoin('Library\Model\Product\Option', 'o', 'WITH', 'oov.option = o')
            ->innerJoin('Library\Model\Product\OptionValue', 'ov', 'WITH', 'oov.option_value = ov')
            ->where($qb->expr()->eq('o.id', $option_id))
            ->andWhere($qb->expr()->eq('ov.id', $value_id));

        return $qb->getQuery()->getSingleResult();
    }
}