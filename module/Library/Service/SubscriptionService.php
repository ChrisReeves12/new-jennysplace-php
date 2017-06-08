<?php
/**
* The SubscriptionService class definition.
*
* This service handles subscription services like the mail list signup
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Subscription\MailSubscription;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class SubscriptionService
 * @package Library\Service
 */
class SubscriptionService extends AbstractService
{
    /**
     * Saves the mail list subscriber
     * @param array $data
     */
    static public function saveMailList($data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Check if user is in database
        $subscriber = $em->getRepository('Library\Model\Subscription\MailSubscription')->findOneByEmail(strtolower($data['email']));
        if (!($subscriber instanceof MailSubscription))
        {
            $subscriber = new MailSubscription();
            $subscriber->setSyncedToRemote(false);
        }

        $subscriber->setName($data['name']);
        $subscriber->setEmail($data['email']);
        $subscriber->setActive(true);
        $em->persist($subscriber);
    }
}