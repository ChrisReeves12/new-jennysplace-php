<?php
/**
* The OptionOptionValue class definition.
*
* This class represents the relationship between product options and eligible values.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Doctrine\ORM\Mapping\Index;
use Library\Model\AbstractModel;
use Library\Model\Product\Option;
use Library\Model\Product\OptionValue;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class OptionOptionValue
 * @package Library\Model\Relationship
 */

/**
 * @Entity(repositoryClass="Library\Model\Repository\OptionOptionValueRepository")
 * @Table(name="assoc_options_option_values", indexes={@Index(name="idx_option_value_search", columns={"option_id", "option_value_id"})})
 * @HasLifecycleCallbacks
 */
class OptionOptionValue extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Option", inversedBy="option_option_values")
     * @JoinColumn(name="option_id", referencedColumnName="id")
     * @var Option
     */
    protected $option;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\OptionValue", inversedBy="option_value_options")
     * @JoinColumn(name="option_value_id", referencedColumnName="id")
     * @var OptionValue
     */
    protected $option_value;

    /**
     * @OneToMany(targetEntity="Library\Model\Relationship\SkuOptionOptionValue", mappedBy="option_option_value", cascade={"remove", "persist"})
     * @var SkuOptionOptionValue[]
     */
    protected $option_option_value_skus;

    /**
     * @return SkuOptionOptionValue[]
     */
    public function getOptionOptionValueSkus()
    {
        return $this->option_option_value_skus;
    }

    /**
     * @param SkuOptionOptionValue[] $option_option_value_skus
     */
    public function setOptionOptionValueSkus($option_option_value_skus)
    {
        $this->option_option_value_skus = $option_option_value_skus;
    }

    /**
     * @return Option
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param Option $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return OptionValue
     */
    public function getOptionValue()
    {
        return $this->option_value;
    }

    /**
     * @param OptionValue $option_value
     */
    public function setOptionValue($option_value)
    {
        $this->option_value = $option_value;
    }
}