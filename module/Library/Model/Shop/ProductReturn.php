<?php
/**
* The ProductReturn class definition.
*
* This class represents the product returns order put in
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;
use Library\Model\User\User;

/**
 * Class ProductReturn
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="product_return")
 * @HasLifecycleCallbacks
 */
class ProductReturn extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\User\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @var User
     */
    protected $user;

    /**
     * @OneToOne(targetEntity="Library\Model\Shop\ShopListElement", inversedBy="product_return")
     * @JoinColumn(name="shop_list_element_id", referencedColumnName="id")
     * @var ShopListElement[]
     */
    protected $shop_list_element;

    /**
     * @Column(name="customer_message", type="text", nullable=true)
     * @var string
     */
    protected $customer_message;

    /**
     * @Column(name="admin_message", type="text", nullable=true)
     * @var string
     */
    protected $admin_message;

    /**
     * @Column(name="status", type="text", nullable=false)
     * @var string
     */
    protected $status;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return ShopListElement
     */
    public function getShopListElement()
    {
        return $this->shop_list_element;
    }

    /**
     * @param ShopListElement $shop_list_element
     */
    public function setShopListElement($shop_list_element)
    {
        $this->shop_list_element = $shop_list_element;
    }

    /**
     * @return string
     */
    public function getCustomerMessage()
    {
        return $this->customer_message;
    }

    /**
     * @param string $customer_message
     */
    public function setCustomerMessage($customer_message)
    {
        $this->customer_message = $customer_message;
    }

    /**
     * @return string
     */
    public function getAdminMessage()
    {
        return $this->admin_message;
    }

    /**
     * @param string $admin_message
     */
    public function setAdminMessage($admin_message)
    {
        $this->admin_message = $admin_message;
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
}