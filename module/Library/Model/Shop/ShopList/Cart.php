<?php
/**
* The Cart class definition.
*
* This class represents shopping carts.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop\ShopList;

use Doctrine\ORM\Mapping\OneToMany;
use Library\Model\Shop\ShopList;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Library\Model\User\User;

/**
 * @Entity
 * @Table(name="carts")
 * @HasLifecycleCallbacks
**/
class Cart extends ShopList
{
    /**
     * @OneToMany(targetEntity="Library\Model\User\User", mappedBy="saved_cart")
     * @var User
     */
    protected $user;

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
}