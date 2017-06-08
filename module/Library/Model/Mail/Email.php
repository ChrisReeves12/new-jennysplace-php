<?php
/**
 * The Email class definition.
 *
 * The description of the class
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\Mail;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Subscription\MailSubscription;
use Library\Model\Traits\StandardModelTrait;
use Library\Service\Settings;

/**
 * Class Email
 * @package Library\Model\Mail
 */

/**
 * @Entity
 * @Table(name="emails")
 * @HasLifecycleCallbacks
 */
class Email extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="completed", type="boolean", nullable=false)
     * @var bool
     */
    protected $completed;

    /**
     * @Column(name="scheduled_send_time", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $scheduled_send_time;

    /**
     * @Column(name="from_email", type="string", nullable=false)
     * @var string
     */
    protected $from;

    /**
     * @Column(name="subject", type="string", nullable=false)
     * @var string
     */
    protected $subject;

    /**
     * @Column(name="message", type="text", nullable=false)
     * @var string
     */
    protected $message;

    /**
     * @ManyToOne(targetEntity="Library\Model\Mail\Campaign")
     * @JoinColumn(name="campaign_id", referencedColumnName="id")
     * @var Campaign
     */
    protected $campaign;

    /**
     * @Column(name="token", type="string", nullable=true)
     * @var string
     */
    protected $token;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @PrePersist
     * @param string $token
     */
    public function setToken($token)
    {
        // Set token by current timestamp
        $this->token = md5(time());
    }

    /**
     * @return \DateTime
     */
    public function getScheduledSendTime()
    {
        return $this->scheduled_send_time;
    }

    /**
     * @param \DateTime $scheduled_send_time
     */
    public function setScheduledSendTime($scheduled_send_time)
    {
        $this->scheduled_send_time = $scheduled_send_time;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return Campaign
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @param Campaign $campaign
     */
    public function setCampaign($campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }
}