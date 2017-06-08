<?php
/**
 * The MailChimp class definition.
 *
 * The plugin used to interact with Mailchimp's API
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Plugin\MaillistMethod;
use Library\Model\Subscription\MailSubscription;
use Zend\Http\Client as HTTPClient;
use Zend\Http\Headers;
use Zend\Http\Request;

/**
 * Class MailChimp
 * @package Library\Plugin\MaillistMethod
 */
class MailChimp implements IMaillistStrategy
{
    /**
     * @var array
     */
    protected $newsletter_data;

    /**
     * @return MailSubscription[]
     */
    public function getNewsletterData()
    {
        return $this->newsletter_data;
    }

    /**
     * @param MailSubscription[] $subscriber_data
     */
    public function setNewsletterData($subscriber_data)
    {
        $this->newsletter_data = $subscriber_data;
    }

    /**
     * Syncs newsletter subscribers to remote
     * @param string $list_id
     */
    public function syncToRemote($list_id)
    {
        $skipped = $added = $updated = 0;

        // Check if there is data to sync
        if (count($this->getNewsletterData()) > 0)
        {
            foreach ($this->getNewsletterData() as $subscriber)
            {
                // Create contact info
                $sub_info = [
                    'email_address' => $subscriber->getEmail(),
                    'status' => $subscriber->getActive() ? 'subscribed' : 'unsubscribed',
                    'email_type' => 'html',
                    'merge_fields' => [
                        'NAME' => $subscriber->getName()
                    ]
                ];

                // Try adding contact
                $http_client = new HTTPClient("https://us8.api.mailchimp.com/3.0/lists/{$list_id}/members");
                $http_client->setOptions(['maxredirects' => 0, 'timeout' => 60]);
                $http_client->setMethod(Request::METHOD_POST);
                $http_headers = new Headers();
                $http_headers->addHeaderLine('content-type: "application/json"');
                $http_headers->addHeaderLine('Authorization: Basic dfc1f745e4f5f5cd84ccb653cdfb602d-us8');
                $http_client->setHeaders($http_headers);
                $http_client->setRawBody(json_encode($sub_info));

                $response = json_decode($http_client->send()->getContent());

                // Check for error
                if ($response->title == 'Member Exists')
                {
                    // Update the contact that already exists
                    $email_hash = md5(strtolower($subscriber->getEmail()));
                    $http_client = new HTTPClient("https://us8.api.mailchimp.com/3.0/lists/{$list_id}/members/{$email_hash}");
                    $http_client->setOptions(['maxredirects' => 0, 'timeout' => 60]);
                    $http_client->setMethod(Request::METHOD_PUT);
                    $http_headers = new Headers();
                    $http_headers->addHeaderLine('content-type: "application/json"');
                    $http_headers->addHeaderLine('Authorization: Basic dfc1f745e4f5f5cd84ccb653cdfb602d-us8');
                    $http_client->setHeaders($http_headers);
                    $http_client->setRawBody(json_encode($sub_info));
                    $http_client->send()->getContent();
                    $updated++;
                    echo "Updated remote contact: {$subscriber->getEmail()}. \n";
                }
                else
                {
                    echo "Added: '" . $sub_info['email_address'] . "' \n";
                    $added++;
                }
            }

            // Job complete
            echo "Skipped: {$skipped}, Added: {$added}, Updated: {$updated} \n";
        }
    }
}