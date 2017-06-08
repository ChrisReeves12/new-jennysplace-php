<?php
/**
* The Address class definition.
*
* This model represents physical addresses
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\User;

use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * Class Address
 * @package Library\Model\User
 */

/**
 * @Entity
 * @Table(name="addresses")
 * @HasLifecycleCallbacks
 */
class Address extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="company", type="string", length=500, nullable=true)
     * @var string
     */
    protected $company;

    /**
     * @Column(name="first_name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $first_name;

    /**
     * @Column(name="last_name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $last_name;

    /**
     * @Column(name="email", type="string", length=500, nullable=false)
     */
    protected $email;

    /**
     * @Column(name="phone", type="string", length=500, nullable=true)
     * @var string
     */
    protected $phone;

    /**
     * @Column(name="address_line_1", type="string", length=500, nullable=false)
     * @var string
     */
    protected $line_1;

    /**
     * @Column(name="address_line_2", type="string", length=500, nullable=true)
     * @var string
     */
    protected $line_2;

    /**
     * @Column(name="city", type="string", length=500, nullable=false)
     * @var string
     */
    protected $city;

    /**
     * @Column(name="state", type="string", length=500, nullable=false)
     * @var string
     */
    protected $state;

    /**
     * @Column(name="zipcode", type="string", length=500, nullable=false)
     * @var string
     */
    protected $zipcode;

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
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
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getLine1()
    {
        return $this->line_1;
    }

    /**
     * @param string $line_1
     */
    public function setLine1($line_1)
    {
        $this->line_1 = $line_1;
    }

    /**
     * @return string
     */
    public function getLine2()
    {
        return $this->line_2;
    }

    /**
     * @param string $line_2
     */
    public function setLine2($line_2)
    {
        $this->line_2 = $line_2;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }
}