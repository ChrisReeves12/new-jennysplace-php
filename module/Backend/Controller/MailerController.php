<?php
/**
 * The MailerController class definition.
 *
 * This controller contains all of the actions pertaining to the email
 * and email campaigning tools
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Mailer\CampaignCreateUpdate;
use Library\Form\Mailer\EmailCreateUpdate;
use Library\Model\Mail\Campaign;
use Library\Model\Mail\Email;
use Library\Service\Settings;
use Zend\View\Model\JsonModel;

/**
 * Class MailerController
 * @package Backend\Controller
 */
class MailerController extends JPController
{
    protected $create_update_campaign_form;
    protected $campaign;
    protected $emails_on_campaign;
    protected $email_mail_subscribers;
    protected $email_create_update_form;
    protected $email;

    /**
     * Create and update campaigns
     * @return array
     * @throws \Exception
     */
    public function campaignAction()
    {
        // Handle post requests
        if (!empty($result = $this->_handle_post()))
        {
            return $result;
        }

        // Build form
        $this->_build_and_hydrate_campaign_form();

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/mailer.js');

        return ['create_update_form' => $this->create_update_campaign_form, 'emails_on_campaign' => $this->emails_on_campaign, 'campaign' => $this->campaign];
    }

    /**
     * Create and update emails
     * @return array
     */
    public function emailAction()
    {
        // Handle post requests
        if (!empty($result = $this->_handle_post()))
        {
            return $result;
        }

        // Build form
        $this->_build_and_hydrate_email_form();

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/mailer.js');

        return ['email_form' => $this->email_create_update_form, 'email' => $this->email, 'email_mail_subs' => $this->email_mail_subscribers];
    }

    /**
     * Hydrate the email form
     */
    private function _build_and_hydrate_email_form()
    {
        $em = $this->getServiceLocator()->get('entity_manager');
        $this->email_create_update_form = new EmailCreateUpdate();

        // Get campains to populate campaign selection box
        $campaigns = $em->getRepository('Library\Model\Mail\Campaign')->findAll();
        $campaign_options[0] = 'None';

        /** @var Campaign $campaign */
        foreach ($campaigns as $campaign)
        {
            $campaign_options[$campaign->getId()] = $campaign->getName();
        }

        $this->email_create_update_form->get('campaign')->setAttribute('options', $campaign_options);

        // Hydrate the form if id was passed in
        if (!empty($_GET['id']))
        {
            $email = $em->getReference('Library\Model\Mail\Email', $_GET['id']);
            if (!($email instanceof Email))
                throw new \Exception("The email being loaded cannot be found in the database.");

            $this->email_create_update_form->get('subject')->setValue($email->getSubject());
            $this->email_create_update_form->get('from')->setValue($email->getFrom());
            $this->email_create_update_form->get('scheduled_send_time')->setValue(!is_null($email->getScheduledSendTime()) ? $email->getScheduledSendTime()->format('m/d/Y') : '');
            $this->email_create_update_form->get('message')->setValue($email->getMessage());
            $this->email_create_update_form->get('campaign')->setValue(is_null($email->getCampaign()) ? 0 : $email->getCampaign()->getId());
            $this->email = $email;

            // Gather information for metrics
            $this->email_mail_subscribers = $em->getRepository('Library\Model\Relationship\EmailMailSubscription')->findBy([
                'email' => $email
            ]);
        }
        else
        {
            // Place default from
            $this->email_create_update_form->get('from')->setValue(Settings::get('site_title') . " <" . Settings::get('site_email') . ">");
        }

        // If a campaign query is being passed in, use that
        if (!empty($_GET['campaign']))
        {
            $this->email_create_update_form->get('campaign')->setValue($_GET['campaign']);
        }
    }

    /**
     * Build create update form
     */
    private function _build_and_hydrate_campaign_form()
    {
        $em = $this->getServiceLocator()->get('entity_manager');

        // Check if we should hydrate the form with an existing campaign
        if (!empty($_GET['id']))
        {
            $id = $_GET['id'];
            $campaign = $em->getReference('Library\Model\Mail\Campaign', $id);
            if (!($campaign instanceof Campaign))
                throw new \Exception("Email campaign with ID of ${id} cannot be found in the database.");

            $this->campaign = $campaign;
        }

        $this->create_update_campaign_form = new CampaignCreateUpdate();
        if (isset($campaign))
        {
            $this->create_update_campaign_form->get('name')->setValue($campaign->getName());
            $this->create_update_campaign_form->get('inactive')->setValue($campaign->isInactive());

            // Get emails on the campaign to populate the scroll box
            $this->emails_on_campaign = $campaign->getEmails();
        }
    }

    /**
     * Handle various post requests
     * @return JsonModel | null
     */
    private function _handle_post()
    {
        // Get entity manager
        $em = $this->getServiceLocator()->get('entity_manager');
        $mailer_service = $this->getServiceLocator()->get('mailer');

        $task = $_POST['task'];
        unset($_POST['task']);

        // Handle all the different tasks of post requests
        switch ($task)
        {
            case 'save_campaign':

                $data = array_merge($_POST, $_GET);

                $campaign = $mailer_service->saveCampaign($data);

                $em->flush();

                // Redirect user to the campaign
                $this->redirect()->toUrl('/admin/mailer/campaign?id=' . $campaign->getId());
                break;

            case 'async_remove_email_from_campaign':

                $email_id = $_POST['email_id'];
                $email = $em->getReference('Library\Model\Mail\Email', $email_id);

                $mailer_service->removeEmailCampaign($email);
                $em->flush();
                return new JsonModel(['error' => false]);
                break;

            case 'save_email':

                $data = array_merge($_POST, $_GET);

                $email = $mailer_service->saveEmail($data);

                $em->flush();

                // Redirect user to the campaign
                $this->redirect()->toUrl('/admin/mailer/email?id=' . $email->getId());
                break;

            case 'test_email':

                $email_id = $_POST['email_id'];
                $email = $em->getReference('Library\Model\Mail\Email', $email_id);

                $mailer_service->sendTestEmail($email);
                return new JsonModel(['error' => false]);
                break;

            case 'launch_campaign':

                $data = array_merge($_POST, $_GET);
                $campaign = $em->getReference('Library\Model\Mail\Campaign', $data['campaign_id']);
                $mailer_service->scheduleCampaignForLaunch($campaign);
                $em->flush();

                return new JsonModel(['error' => false]);
                break;

            case 'unlaunch_campaign':

                $data = array_merge($_POST, $_GET);
                $campaign = $em->getReference('Library\Model\Mail\Campaign', $data['campaign_id']);
                $mailer_service->unlaunchCampaign($campaign);
                $em->flush();

                return new JsonModel(['error' => false]);
                break;
        }

        return null;
    }
}