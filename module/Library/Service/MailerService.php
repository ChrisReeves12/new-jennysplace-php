<?php
/**
 * The MailerService class definition.
 *
 * Handles all the heavy lifting and various tasks regarding the mailer API
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;
use Doctrine\ORM\EntityManager;
use Library\Model\Mail\Campaign;
use Library\Model\Mail\Email;
use Library\Model\Relationship\EmailMailSubscription;
use Library\Model\Subscription\MailSubscription;
use Library\Model\User\User;
use Zend\Validator\EmailAddress;

/**
 * Class MailerService
 * @package Library\Service
 */
class MailerService extends AbstractService
{
    protected $store_settings;

    public function initialize()
    {
        $this->store_settings = Settings::getAll();
    }

    /**
     * Saves a campgain to the database
     * @param array $data
     * @return Campaign
     */
    public function saveCampaign($data)
    {
        // Find existing campaign
        $em = $this->getServiceManager()->get('entity_manager');

        if (!empty($data['id']))
            $campaign = $em->getReference('Library\Model\Mail\Campaign', $data['id']);
        else
        {
            $campaign = new Campaign();
            $em->persist($campaign);
        }

        // Save data to campaign
        $campaign->setName($data['name']);
        $campaign->setInactive($data['inactive'] ?? false);

        return $campaign;
    }

    /**
     * Saves an email to the database under a campaign
     * @param array $data
     * @return Email
     * @throws \Exception
     */
    public function saveEmail($data)
    {
        // Find existing campaign
        $em = $this->getServiceManager()->get('entity_manager');

        if (!empty($data['id']))
            $email = $em->getReference('Library\Model\Mail\Email', $data['id']);
        else
        {
            $email = new Email();
            $em->persist($email);
        }

        // Save email
        $campaign = null;
        if ($data['campaign'] > 0)
        {
            $campaign = $em->getReference('Library\Model\Mail\Campaign', $data['campaign']);
            if (!($campaign instanceof Campaign))
                $campaign = null;
        }

        $email->setSubject($data['subject']);
        $email->setCampaign($campaign);
        $email->setFrom($data['from']);

        // Set scheduled send date
        if (!empty($data['scheduled_send_time']))
        {
            $date = new \DateTime($data['scheduled_send_time']);
            $email->setScheduledSendTime($date);
        }
        else
        {
            $email->setScheduledSendTime(null);
        }

        $email->setMessage($data['message']);

        if (empty($email->getId()))
            $email->setCompleted(false);

        return $email;
    }

    /**
     * Sends a test email to the store email
     *
     * @param Email $email
     *
     * @throws \Exception
     */
    public function sendTestEmail($email)
    {
        $store_email = $this->store_settings['site_email'];

        // Create a fake subscriber to represent the store
        $store_sub = new MailSubscription();
        $store_sub->setActive(true);
        $store_sub->setName("Store");
        $store_sub->setEmail($store_email);
        $failure_message = null;

        $this->processEmail($email, $store_sub, $failure_message);
        if (!empty($failure_message))
        {
            throw new \Exception($failure_message);
        }
    }

    /**
     * Removes a campaign from an email
     * @param Email $email
     */
    public function removeEmailCampaign($email)
    {
        $email->setCampaign(null);
    }

    /**
     * Schedules a campaign to be launched
     * @param Campaign $campaign
     * @throws \Exception
     */
    public function scheduleCampaignForLaunch($campaign)
    {
        // Check if campaign is not already scheduled
        if (!$campaign->isInactive() && !$campaign->hasLaunched() && ($campaign->getStatus() == 'Not Started' || $campaign->getStatus() == 'Stopped'));
        {
            // Check if there are emails on this campaign before launching
            $emails = $campaign->getEmails();
            if (count($emails) == 0)
            {
                throw new \Exception("You must add at least one email to the campaign before launch.");
            }

            // Calculate the number of remaining emails
            $em = $this->getServiceManager()->get('entity_manager');
            $remaining_emails = $em->getRepository('Library\Model\Mail\Email')->findBy(['campaign' => $campaign, 'completed' => false]);
            $campaign->setRemainingEmails(count($remaining_emails));

            // Set status on campaign appropriately
            $campaign->setStatus('In Queue');
            $campaign->setLastLaunched(new \DateTime());
            $campaign->setLaunched(true);
        }
    }

    /**
     * Unlaunches a campaign
     * @param Campaign $campaign
     */
    public function unlaunchCampaign($campaign)
    {
        // Check if campaign is not already unlaunched
        if ($campaign->hasLaunched())
        {
            $campaign->setStatus('Stopped');
            $campaign->setLaunched(false);
        }
    }

    /**
     * @param Email $email
     * @param MailSubscription|string $recipient
     * @param string $failure_message
     *
     * @return bool
     */
    public function processEmail($email, $recipient, &$failure_message = null)
    {
        if ($_SERVER['APP_ENV'] == 'production')
            $use_smtp = true;
        else
            $use_smtp = false;

        if ($use_smtp)
        {
            $transport = new \Zend\Mail\Transport\Smtp();
            $smpt_options = new \Zend\Mail\Transport\SmtpOptions([
                'name' => 'newjennysplace.com',
                'host' => '127.0.0.1',
                'port' => 25
            ]);

            $transport->setOptions($smpt_options);
        }
        else
        {
            $transport = new \Zend\Mail\Transport\Sendmail();
        }

        // Validate email address
        $email_validator = new EmailAddress();
        $email_address = ($recipient instanceof MailSubscription) ? $recipient->getEmail() : $recipient;

        if (!$email_validator->isValid($email_address))
        {
            $failure_message = "The email address is not valid.";
            return false;
        }

        // Form email
        $message = new \Zend\Mail\Message();

        try
        {
            $message->setTo($email_address);

            $message->setFrom($email->getFrom());
            $message->setSubject($email->getSubject());

            $html_part = new \Zend\Mime\Part($email->getMessage());
            $html_part->type = 'text/html';

            $mime_message = new \Zend\Mime\Message();
            $mime_message->addPart($html_part);

            // Create part of the email that will be for opting out
            if ($recipient instanceof MailSubscription)
            {
                $usub_link = $this->store_settings['site_url'] . '/subscription/release?token=' . $email->getToken() . '&id=' . $recipient->getId();
                $otp_out_section = new \Zend\Mime\Part("To unsubscribe to this newsletter, click <a href=\"http://{$usub_link}\">here</a>.");
                $otp_out_section->type = 'text/html';
                $mime_message->addPart($otp_out_section);

                // Create tracker
                $tracker_tag = "<img src='http://{$this->store_settings['site_url']}/subscription/track?token={$email->getToken()}&id={$recipient->getId()}'/>";
                $tracker_section = new \Zend\Mime\Part($tracker_tag);
                $tracker_section->type = 'text/html';
                $mime_message->addPart($tracker_section);
            }

            // Send email
            $message->setBody($mime_message);

            if ($_SERVER['APP_ENV'] == 'production')
                $transport->send($message);
            else
                echo "Development mode: No send \n";

            return true;
        }
        catch (\Exception $ex)
        {
            $failure_message = $ex->getMessage();
            return false;
        }
    }

    /**
     * Unsubscribes email subscriber from mail list
     * @param MailSubscription $subscriber
     * @param string $token
     * @return boolean
     */
    public function unsubscribe($subscriber, $token)
    {
        // Check if token is valid
        $em = $this->getServiceManager()->get('entity_manager');
        $email = $em->getRepository('Library\Model\Mail\Email')->findOneByToken($token);
        $result = false;

        if ($email instanceof Email)
        {
            $subscriber->setActive(false);

            // Set the unsubscribed metric
            $email_sub_rel = $em->getRepository('Library\Model\Relationship\EmailMailSubscription')->findOneBy([
                'email' => $email,
                'subscriber' => $subscriber
            ]);

            if ($email_sub_rel instanceof EmailMailSubscription)
                $email_sub_rel->setUnsubscribed(true);

            // Check if this email corresponds to a user as well, if so, deactive the newsletter
            $user = $em->getRepository('Library\Model\User\User')->findOneByEmail($subscriber->getEmail());
            if ($user instanceof User)
            {
                $user->setNewsletter(false);
            }

            $result = true;
        }

        return $result;
    }

    /**
     * Generates the tracker image used to determine if an email has been opened
     * @return string
     */
    public function produceTrackerImage()
    {
        $blank = getcwd() . '/public/img/layout_images/blank.gif';
        ob_start();
        header('Content-Type: image/gif'); // Change image type if you use gif/jpg
        header('Content-Disposition: inline; filename="blank.gif"'); // Image name
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($blank));
        header('Accept-Ranges: bytes');
        header('ETag: "' . md5($blank) . '"');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($blank)) . ' GMT');
        readfile($blank);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Runs scheduled campaigns
     * @param Campaign[] $campaigns
     */
    public function runCampaigns($campaigns)
    {
        /** @var EntityManager $em */
        $em = $this->getServiceManager()->get('entity_manager');

        // Get emails out of each campaign and process them
        if (count($campaigns) > 0)
        {
            /** @var Campaign $campaign */
            foreach ($campaigns as $campaign)
            {
                $emails = $campaign->getEmails();
                $total_number_of_emails = count($emails);
                $emails_already_complete = 0;
                $emails_sent = 0;

                $campaign->setStatus('In Processing');
                $em->flush($campaign);
                echo "-- Starting Campaign: '{$campaign->getName()}' --\n";

                /** @var Email $email */
                foreach ($emails as $email)
                {
                    if (!$email->isCompleted())
                    {
                        // Check if email should be processed now
                        $now_time = new \DateTime();
                        if ($email->getScheduledSendTime() == null || $email->getScheduledSendTime() <= $now_time)
                        {
                            // TODO: For now, our email list is just newsletter subscribers
                            $users_to_email = $em->getRepository('Library\Model\Subscription\MailSubscription')->findBy(['active' => true]);

                            // The email is marked as 'complete' when all users have been sent the email
                            $sub_count = count($users_to_email);
                            $emails_sent = 0;

                            /** @var MailSubscription $user_to_email */
                            foreach ($users_to_email as $user_to_email)
                            {
                                // Has the user already gotten this email?
                                $email_sub_rel = $em->getRepository('Library\Model\Relationship\EmailMailSubscription')->findOneBy(['subscriber' => $user_to_email, 'email' => $email]);

                                if ($email_sub_rel instanceof EmailMailSubscription)
                                {
                                    if ($email_sub_rel->wasSent())
                                    {
                                        echo "Email '{$user_to_email->getEmail()}' was already sent \n";
                                        continue;
                                    }
                                }
                                else
                                {
                                    $email_sub_rel = new EmailMailSubscription();
                                }

                                $email_sub_rel->setEmail($email);
                                $email_sub_rel->setSubscriber($user_to_email);
                                $email_sub_rel->setOpened(false);
                                $email_sub_rel->setSent(false);
                                $em->persist($email_sub_rel);
                                $failure_message = null;

                                // Process email
                                $success = $this->processEmail($email, $user_to_email, $failure_message);
                                if ($success)
                                {
                                    echo "Sent email id: {$email->getId()} to '{$user_to_email->getEmail()}' \n";
                                    $email_sub_rel->setFailureMessage(null);
                                    $email_sub_rel->setDateSent(new \DateTime());
                                    $emails_sent++;
                                }
                                else
                                {
                                    echo "Failed to send to email: '{$user_to_email->getEmail()}' \n";
                                    $email_sub_rel->setFailureMessage($failure_message);
                                    $em->persist($email_sub_rel);
                                }

                                $email_sub_rel->setSent($success);
                                $em->flush($email_sub_rel);
                                $em->clear($email_sub_rel);
                            }

                            if (count($users_to_email) > 0)
                            {
                                if ($emails_sent == $sub_count)
                                {
                                    $email->setCompleted(true);
                                    $emails_sent++;
                                }
                                else
                                {
                                    echo "Falure to send to email id: {$email->getId()} to '{$user_to_email->getEmail()}' \n";
                                    $email->setCompleted(false);
                                }
                            }
                        }
                    }
                    else
                    {
                        $emails_already_complete++;
                    }
                }

                // Calculate the status of the campaign
                $emails_remaining = ($total_number_of_emails - $emails_already_complete - $emails_sent);
                if ($emails_remaining > 0)
                {
                    $campaign->setRemainingEmails($emails_remaining);
                }
                else
                {
                    $campaign->setRemainingEmails(0);
                    $campaign->setStatus('Complete');
                }

                echo "-- Campaign '{$campaign->getName()}'' has run. --\n";
                echo "-- Campaign Status: {$campaign->getStatus()} -- \n";
                echo "-- Emails remaining on campaign '{$campaign->getId()}': {$campaign->getRemainingEmails()} \n";
            }
        }
        else
        {
            echo "No campaigns scheduled for launch. \n";
        }
    }
}