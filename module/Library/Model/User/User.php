<?php
/**
* The User class definition.
*
* Represents a user or a customer.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\User;

use Doctrine\ORM\Mapping\ManyToOne;
use Library\Model\AbstractModel;
use Library\Model\Shop\ShopList\Cart;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Library\Service\UserService;

/**
 * Class User
 * @package Library\Model\User
 */

/**
 * @Entity
 * @Table(name="users")
 * @HasLifecycleCallbacks
 */
class User extends AbstractModel
{
    const USER_STATUS_UNVERIFIED = 0;
    const USER_STATUS_VERIFIED = 1;

    use StandardModelTrait;

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
     * @Column(name="tax_id", type="string", length=30, nullable=true)
     * @var string
     */
    protected $tax_id;

    /**
     * @Column(name="email", type="string", length=255, unique=true, nullable=false)
     * @var string
     */
    protected $email;

    /**
     * @Column(name="store_credit", type="decimal", scale=2, nullable=true)
     * @var float
     */
    protected $store_credit;

    /**
     * @Column(name="admin_memo", type="text", nullable=true)
     * @var string
     */
    protected $admin_memo;


    /**
     * @Column(name="phone", type="string", length=500, nullable=true)
     * @var string
     */
    protected $phone;

    /**
     * @Column(name="fax", type="string", length=500, nullable=true)
     * @var string
     */
    protected $fax;

    /**
     * @Column(name="password", type="string", length=500, nullable=true)
     * @var string
     */
    protected $password;

    /**
     * @Column(name="role", type="string", length=500, nullable=false)
     * @var string
     */
    protected $role;

    /**
     * @Column(name="newsletter", type="integer", nullable=false)
     * @var bool
     */
    protected $newsletter;

    /**
     * @Column(name="synced_to_newsletter", type="boolean", nullable=true)
     * @var bool
     */
    protected $important;

    /**
     * @ManyToOne(targetEntity="Library\Model\Shop\ShopList\Cart", inversedBy="user", cascade={"remove", "persist"})
     * @JoinColumn(name="saved_cart_id", referencedColumnName="id")
     * @var Cart
     */
    protected $saved_cart;

    /**
     * @Column(name="status", type="integer", nullable=false)
     * @var int
     */
    protected $status = USER::USER_STATUS_UNVERIFIED;

    /**
     * @Column(name="token", type="string", length=500, nullable=true)
     * @var string
     */
    protected $token;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\Address", cascade={"all"})
     * @JoinColumn(name="billing_address", referencedColumnName="id")
     * @var Address
     */
    protected $billing_address;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\Address", cascade={"all"})
     * @JoinColumn(name="shipping_address", referencedColumnName="id")
     * @var Address
     */
    protected $shipping_address;

    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->tax_id;
    }

    /**
     * @param string $tax_id
     */
    public function setTaxId($tax_id)
    {
        $this->tax_id = $tax_id;
    }

    /**
     * @return string
     */
    public function getAdminMemo()
    {
        return $this->admin_memo;
    }

    /**
     * @param string $admin_memo
     */
    public function setAdminMemo($admin_memo)
    {
        $this->admin_memo = $admin_memo;
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
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @param string $fax
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return UserService::encrypt_password($this->password);
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = UserService::encrypt_password($password);
    }

    /**
     * @param string $password
     */
    public function setRawPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getRawPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param bool $newsletter
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;
    }

    /**
     * @return Cart
     */
    public function getSavedCart()
    {
        return $this->saved_cart;
    }

    /**
     * @param Cart $saved_cart
     */
    public function setSavedCart($saved_cart)
    {
        $this->saved_cart = $saved_cart;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * @param Address $billing_address
     */
    public function setBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @param Address $shipping_address
     */
    public function setShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
    }

    /**
     * Displays the human readable name of the current instance of the entity for menus
     * @return string
     */
    public function showDisplayName()
    {
        return $this->first_name . " " . $this->last_name;
    }

    /**
     * @return float
     */
    public function getStoreCredit()
    {
        return number_format($this->store_credit, 2, '.', '');
    }

    /**
     * @param float $store_credit
     */
    public function setStoreCredit($store_credit)
    {
        $this->store_credit = ($store_credit < 0) ? 0 : round($store_credit, 2);
    }

    /**
     * Reduces the store credit by a given amount
     * @param float $reduction
     */
    public function deductStoreCredit($reduction)
    {
        $store_credit = $this->getStoreCredit() - $reduction;
        $this->setStoreCredit(floatval($store_credit));
    }

    /**
     * @return boolean
     */
    public function isImportant()
    {
        return $this->important;
    }

    /**
     * @param boolean $important
     */
    public function setImportant($important)
    {
        $this->important = $important;
    }
}