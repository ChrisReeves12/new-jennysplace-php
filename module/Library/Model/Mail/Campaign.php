<?php
/**
 * The Campain class definition.
 *
 * The description of the class
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\Mail;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class Campaign
 * @package Library\Model\Mail
 */

/**
 * @Entity
 * @Table(name="campaigns")
 * @HasLifecycleCallbacks
 */
class Campaign extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @var boolean
     */
    protected $inactive;

    /**
     * @OneToMany(targetEntity="Library\Model\Mail\Email", mappedBy="campaign", cascade={"remove", "persist"})
     * @var Email[]
     */
    protected $emails;

    /**
     * @Column(name="launched", type="boolean", nullable=true)
     * @var boolean
     */
    protected $launched;

    /**
     * @Column(name="last_launched", type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $last_launched;

    /**
     * @Column(name="remaining_emails", type="integer", nullable=true)
     * @var int
     */
    protected $remaining_emails;

    /**
     * @return int
     */
    public function getRemainingEmails()
    {
        return $this->remaining_emails;
    }

    /**
     * @param int $remaining_emails
     */
    public function setRemainingEmails($remaining_emails)
    {
        $this->remaining_emails = $remaining_emails;
    }

    /**
     * @Column(name="status", type="string", nullable=false, options={"default":"Not Started"})
     * @var string
     */
    protected $status;

    public function __construct()
    {
        if (empty($this->status))
        {
            $this->status = "Not Started";
        }
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getLastLaunched()
    {
        return $this->last_launched;
    }

    /**
     * @param \DateTime $last_launched
     */
    public function setLastLaunched($last_launched)
    {
        $this->last_launched = $last_launched;
    }

    /**
     * @return boolean
     */
    public function hasLaunched()
    {
        return $this->launched;
    }

    /**
     * @param boolean $launched
     */
    public function setLaunched($launched)
    {
        $this->launched = $launched;
    }

    /**
     * @return Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param Email[] $emails
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isInactive()
    {
        return $this->inactive;
    }

    /**
     * @param boolean $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }
}