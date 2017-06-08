<?php
/**
 * The IMaillistStrategy class definition.
 *
 * Defines a type of mailling list like Bronto, or Mailchimp
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Plugin\MaillistMethod;

/**
 * Interface IMaillistStrategy
 * @package Library\Plugin\MaillistMethod
 */
interface IMaillistStrategy
{
    /**
     * @param array
     */
    public function setNewsletterData($subscriber_data);

    /**
     * @return array
     */
    public function getNewsletterData();

    /**
     * Syncs newsletter subscribers to remote
     * @param string $list_id
     */
    public function syncToRemote($list_id);
}