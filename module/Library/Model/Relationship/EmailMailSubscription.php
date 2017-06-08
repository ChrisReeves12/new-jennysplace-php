<?php
/**
 * The EmailMailSubscription class definition.
 *
 * The description of the class
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\Relationship;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Mail\Email;
use Library\Model\Subscription\MailSubscription;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class EmailMailSubscription
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_email_maillist")
 * @HasLifecycleCallbacks
 */
class EmailMailSubscription extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Subscription\MailSubscription")
     * @JoinColumn(name="subscriber_id", referencedColumnName="id")
     * @var MailSubscription
     */
    protected $subscriber;

    /**
     * @ManyToOne(targetEntity="Library\Model\Mail\Email")
     * @JoinColumn(name="email_id", referencedColumnName="id")
     * @var Email
     */
    protected $email;

    /**
     * @Column(name="sent", type="boolean", nullable=false)
     * @var boolean
     */
    protected $sent;

    /**
     * @Column(name="opened", type="boolean", nullable=false)
     * @var boolean
     */
    protected $opened;

    /**
     * @Column(name="date_opened", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $date_opened;

    /**
     * @Column(name="unsubscribed", type="boolean", nullable=true)
     * @var boolean
     */
    protected $unsubscribed;

    /**
     * @Column(name="date_sent", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $date_sent;

    /**
     * @return \DateTime
     */
    public function getDateOpened()
    {
        return $this->date_opened;
    }

    /**
     * @param \DateTime $date_opened
     */
    public function setDateOpened($date_opened)
    {
        $this->date_opened = $date_opened;
    }

    /**
     * @return \DateTime
     */
    public function getDateSent()
    {
        return $this->date_sent;
    }

    /**
     * @param \DateTime $date_sent
     */
    public function setDateSent($date_sent)
    {
        $this->date_sent = $date_sent;
    }

    /**
     * @Column(name="failure_message", type="text", nullable=true)
     * @var string
     */
    protected $failure_message;

    /**
     * @return string
     */
    public function getFailureMessage()
    {
        return $this->failure_message;
    }

    /**
     * @param string $failure_message
     */
    public function setFailureMessage($failure_message)
    {
        $this->failure_message = $failure_message;
    }

    /**
     * Did the user unsubscribe from this email?
     * @return boolean
     */
    public function isUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * @param boolean $unsubscribed
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;
    }

    /**
     * @return MailSubscription
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param MailSubscription $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param Email $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function wasSent()
    {
        return $this->sent;
    }

    /**
     * @param boolean $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return boolean
     */
    public function wasOpened()
    {
        return $this->opened;
    }

    /**
     * @param boolean $opened
     */
    public function setOpened($opened)
    {
        $this->opened = $opened;
    }
}