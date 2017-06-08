<?php
/**
* The StandardModelTrait trait definition.
*
* This represents some common functionality that goes on all models.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Traits;

use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

/**
 * Class StandardModelTrait
 * @package Library\Model\Traits
 */
trait StandardModelTrait
{
    /**
     * @GeneratedValue
     * @Id
     * @Column(type="integer")
     * @var int
     */
    protected $id;

    /**
     * Returns id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @Column(name="date_created", type="datetime")
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @Column(name="date_modified", type="datetime")
     * @var \DateTime
     */
    protected $date_modified;

    /**
     * Automatically sets the date modified field
     * @preUpdate
     * @prePersist
     *
     * @param \DateTime $date_modified
     */
    public function setDateModified($date_modified = null)
    {
        if ($date_modified instanceof \DateTime)
        {
            $this->date_modified = $date_modified;
        }
        else
        {
            if (empty($this->date_modified))
            {
                $this->date_modified = new \DateTime();
            }
        }
    }

    /**
     * Automatically sets the date created field
     * @prePersist
     *
     * @param \DateTime $date_created
     */
    public function setDateCreated($date_created = null)
    {
        if ($date_created instanceof \DateTime)
        {
            $this->date_created = $date_created;
        }
        else
        {
            if (empty($this->date_created))
            {
                $this->date_created = new \DateTime();
            }
        }
    }

    /**
     * Returns date created.
     * @return string
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns date modified.
     * @return string
     */
    public function getDateModified()
    {
        return $this->date_modified;
    }

}