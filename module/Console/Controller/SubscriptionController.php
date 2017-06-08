<?php
/**
 * The SubscriptionController class definition.
 *
 * This class contains console functions regarding our mailing lists
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Console\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Library\Controller\JPController;
use Library\Model\Subscription\MailSubscription;
use Library\Model\User\User;
use Library\Plugin\MaillistMethod\IMaillistStrategy;
use Library\Plugin\MaillistMethod\MailChimp;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class SubscriptionController
 * @package Console\Controller
 */
class SubscriptionController extends JPController
{
    /**
     * @type IMaillistStrategy
     */
    protected $mail_list_strategy;

    public function __construct()
    {
        // For now, the default mail list strategy is Mailchimp
        if (!($this->mail_list_strategy instanceof IMaillistStrategy))
        {
            $this->mail_list_strategy = new MailChimp();
        }
    }

    /**
     * Syncs newsletter signup users to remote emailing platform
     */
    public function syncnewslettertoremoteAction()
    {
        // Get newsletter subscribers
        $em = EntityManagerSingleton::getInstance();
        $subscribers = $em->getRepository('Library\Model\Subscription\MailSubscription')->findAll();
        $this->mail_list_strategy->setNewsletterData($subscribers);

        // Send to the Newsletter list
        $this->mail_list_strategy->syncToRemote('9bf1d509eb');
    }

    /**
     * Runs scheduled email campaigns
     */
    public function runemailcampaignsAction()
    {
        $em = $this->getServiceLocator()->get('entity_manager');

        // Find all campaigns that are scheduled to start
        $campaigns_to_run = $em->getRepository('Library\Model\Mail\Campaign')->findBy(['launched' => true, 'status' => 'In Queue', 'inactive' => false]);
        $mailer_service = $this->getServiceLocator()->get('mailer');
        $mailer_service->runCampaigns($campaigns_to_run);
        $em->flush();

        echo "---Email campaign job complete---\n";
    }

    /**
     * Brings customer who are opted in to the mail list
     */
    public function syncuserstonewsletterAction()
    {
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity_manager');
        $done = false;
        $page = 0;
        $batch_size = 3;

        while (!$done)
        {
            $criteria = new Criteria();
            $criteria
                ->setFirstResult($page * $batch_size)
                ->setMaxResults($batch_size)
                ->where($criteria->expr()->eq('status', 1));
            $customers = $em->getRepository('Library\Model\User\User')->matching($criteria);
            if ($customers->isEmpty())
            {
                $done = true;
            }
            else
            {
                /** @var User $customer */
                foreach ($customers as $customer)
                {
                    // Is this user in the maillist?
                    $subscriber = $em->getRepository('Library\Model\Subscription\MailSubscription')->findOneBy([
                        'email' => $customer->getEmail()
                    ]);

                    if ($subscriber instanceof MailSubscription)
                    {
                        // Update the existing subscriber
                        $subscriber->setActive($customer->getNewsletter());
                        $subscriber->setName($customer->getFirstName() . ' ' . $customer->getLastName());
                    }
                    else
                    {
                        // Create a new subscriber to represent the customer
                        $subscriber = new \Library\Model\Subscription\MailSubscription();
                        $subscriber->setName($customer->getFirstName() . ' ' . $customer->getLastName());
                        $subscriber->setEmail($customer->getEmail());
                        $subscriber->setActive($customer->getNewsletter());
                        $subscriber->setSyncedToRemote(false);
                        $em->persist($subscriber);
                    }
                }

                $page++;
            }
        }

        $em->flush();

        echo "Done! Added contacts. \n";
        exit(0);
    }
}