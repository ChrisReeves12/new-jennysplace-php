<?php
/**
* The PaymentMethod class definition.
*
* This class represents a payment method
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Shop;

use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * Class PaymentMethod
 * @package Library\Model\Shop
 */

/**
 * @Entity
 * @Table(name="payment_methods")
 * @HasLifecycleCallbacks
 **/
class PaymentMethod
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @Column(name="process_strategy", type="string", length=500, nullable=false)
     * @var string
     */
    protected $process_strategy;

    /**
     * @Column(name="is_supported", type="boolean", nullable=false)
     * @var bool
     */
    protected $is_supported;

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
     * @return bool
     */
    public function getIsSupported()
    {
        return $this->is_supported;
    }

    /**
     * @param bool $is_supported
     */
    public function setIsSupported($is_supported)
    {
        $this->is_supported = $is_supported;
    }

    /**
     * @return string
     */
    public function getProcessStrategy()
    {
        return $this->process_strategy;
    }

    /**
     * @param string $process_strategy
     */
    public function setProcessStrategy($process_strategy)
    {
        $this->process_strategy = $process_strategy;
    }
}