<?php
/**
* The OptionValue class definition.
*
* OptionValues are the eligible values an option can have (e.g blue, red, xl, small, ect)
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Product;

use Library\Model\AbstractModel;
use Library\Model\Relationship\OptionOptionValue;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class OptionValue
 * @package Library\Model\Product
 */

/**
 * @Entity
 * @Table(name="option_values")
 * @HasLifecycleCallbacks
 */
class OptionValue extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\OptionOptionValue", mappedBy="option_value", cascade={"remove", "persist"})
     * @var OptionOptionValue[]
     */
    protected $option_value_options;

    /**
     * @return OptionOptionValue[]
     */
    public function getOptionValueOptions()
    {
        return $this->option_value_options;
    }

    /**
     * @param OptionOptionValue[] $option_value_options
     */
    public function setOptionValueOptions($option_value_options)
    {
        $this->option_value_options = $option_value_options;
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
}