<?php
/**
* The MailSubscription class definition.
*
* Represents the mail subscription
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Subscription;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class MailSubscription
 * @package Library\Model\Subscription
 */

/**
 * @Entity
 * @Table(name="maillist")
 * @HasLifecycleCallbacks
 */
class MailSubscription extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", length=500, type="string", nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="email", length=255, type="string", nullable=false, unique=true)
     * @var string
     */
    protected $email;

    /**
     * @Column(name="active", type="boolean", nullable=false)
     * @var bool
     */
    protected $active;

    /**
     * @Column(name="synced_to_remote", type="boolean", nullable=false)
     * @var bool
     */
    protected $synced_to_remote;

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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isSyncedToRemote()
    {
        return $this->synced_to_remote;
    }

    /**
     * @param bool $synced_to_remote
     */
    public function setSyncedToRemote($synced_to_remote)
    {
        $this->synced_to_remote = $synced_to_remote;
    }
}