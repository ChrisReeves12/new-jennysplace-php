<?php
/**
* The Option class definition.
*
* Options are the attributes that define products. (e.g. size, color, ,ect.)
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
 * Class Option
 * @package Library\Model\Product
 */

/**
 * @Entity(repositoryClass="Library\Model\Repository\OptionRepository")
 * @Table(name="options")
 * @HasLifecycleCallbacks
 */
class Option extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="name", type="string", length=500, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\OptionOptionValue", mappedBy="option", cascade={"remove", "persist"})
     * @var OptionOptionValue[]
     */
    protected $option_option_values;

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
     * @return OptionOptionValue[]
     */
    public function getOptionOptionValues()
    {
        return $this->option_option_values;
    }

    /**
     * @param OptionOptionValue[] $option_option_values
     */
    public function setOptionOptionValues($option_option_values)
    {
        $this->option_option_values = $option_option_values;
    }

    /**
     * @param OptionValue $option_value
     */
    public function addOptionValue(OptionValue $option_value)
    {
        $option_option_value = new OptionOptionValue();
        $option_option_value->setOption($this);
        $option_option_value->setOptionValue($option_value);
        $this->option_option_values[] = $option_option_value;
    }
}