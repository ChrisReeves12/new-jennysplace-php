<?php
/**
* The SubscriptionController class definition.
*
* The controller that houses actions regarding subscriptions
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\Controller;

use Library\Controller\JPController;
use Library\Form\Subscription\MailistSignup;
use Library\Model\Mail\Email;
use Library\Model\Relationship\EmailMailSubscription;
use Library\Model\Subscription\MailSubscription;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class SubscriptionController
 * @package Frontend\Controller
 */
class SubscriptionController extends JPController
{
    /**
     * Signs up a user to the mail list
     */
    public function mailAction()
    {
        // Get entity manager
        $subscription_service = $this->getServiceLocator()->get('subscription');

        $em = EntityManagerSingleton::getInstance();

        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $maillist_form = new MailistSignup();

            $maillist_form->setData($data);

            if ($maillist_form->isValid())
            {
                $new_data = $maillist_form->getData();
                $subscription_service->saveMailList($new_data);

                $em->flush();
                $this->flashMessenger()->addSuccessMessage("Thank you {$data['name']}, for signing up for our newsletter!");
                $this->redirect()->toRoute('home');
            }
            else
            {
                // Show error message
                $this->flashMessenger()->addErrorMessage("Please make sure all fields are complete when signing up for the email newsletter.");
                $this->flashMessenger()->addErrorMessage("Also, make sure you are using a valid email address.");
                $this->redirect()->toRoute('home');
            }
        }
    }

    /**
     * Produces tracking image to determine if email has been opened
     */
    public function trackAction()
    {
        if (!empty($_GET['token']) && !empty($_GET['id']))
        {
            // Find email and subscriber relationship to modify
            $em = $this->getServiceLocator()->get('entity_manager');
            $email = $em->getRepository('Library\Model\Mail\Email')->findOneByToken($_GET['token']);
            if ($email instanceof Email)
            {
                // Find subscriber
                $subscriber = $em->getRepository('Library\Model\Subscription\MailSubscription')->findOneById($_GET['id']);
                if ($subscriber instanceof MailSubscription)
                {
                    $email_sub_rel = $em->getRepository('Library\Model\Relationship\EmailMailSubscription')->findOneBy([
                        'email' => $email,
                        'subscriber' => $subscriber
                    ]);

                    if ($email_sub_rel instanceof EmailMailSubscription)
                    {
                        // Mark as opened if not opened yet
                        if (!$email_sub_rel->wasOpened())
                        {
                            $email_sub_rel->setOpened(true);
                            $email_sub_rel->setDateOpened(new \DateTime());
                            $em->flush();
                        }
                    }
                }
            }
        }

        // Produce tracker image
        $mailer_service = $this->getServiceLocator()->get('mailer');
        exit($mailer_service->produceTrackerImage());
    }

    /**
     * Releases a mail subscriber from the mailing list
     */
    public function releaseAction()
    {
        $success = false;

        if (!empty($_GET['id'] && !empty($_GET['token'])))
        {
            $mailer_service = $this->getServiceLocator()->get('mailer');
            $em = $this->getServiceLocator()->get('entity_manager');
            $subsriber = $em->getReference('Library\Model\Subscription\MailSubscription', $_GET['id']);
            if ($subsriber instanceof MailSubscription)
            {
                $success = $mailer_service->unsubscribe($subsriber, $_GET['token']);
                $em->flush();
            }
        }

        // Show messages
        if ($success)
        {
            $this->flashMessenger()->addSuccessMessage("You have been removed from our email newsletter.");
        }
        else
        {
            $this->flashMessenger()->addErrorMessage("Your email was not found on our newsletter list.");
        }

        $this->redirect()->toRoute('home');
    }
}