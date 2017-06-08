<?php
/**
* The TaxService class definition.
*
* Contains various functions for creating and updating taxes
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\Tax;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class TaxService
 * @package Library\Service\Shop
 */
class TaxService extends AbstractService
{

    /**
     * @param array $data
     * @param float $tax
     *
     * @return Tax
     */
    public function save($data, $tax)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        if (!($tax instanceof Tax))
        {
            $tax = new Tax();
        }

        // Assign parameters
        $tax->setState($data['state']);
        $tax->setRate($data['rate']);
        $tax->setInactive($data['inactive']);

        $em->persist($tax);

        return $tax;
    }
}