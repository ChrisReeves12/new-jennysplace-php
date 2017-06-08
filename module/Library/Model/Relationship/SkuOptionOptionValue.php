<?php
/**
* The SkuOptionOptionValue class definition.
*
* Represents option-value pairs with skus
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Relationship;

use Library\Model\AbstractModel;
use Library\Model\Product\Sku;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * Class SkuOptionOptionValue
 * @package Library\Model\Relationship
 */

/**
 * @Entity
 * @Table(name="assoc_skus_options_option_values")
 * @HasLifecycleCallbacks
 */
class SkuOptionOptionValue extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @ManyToOne(targetEntity="Library\Model\Product\Sku", inversedBy="sku_option_option_values")
     * @JoinColumn(name="sku_id", referencedColumnName="id")
     * @var Sku
     */
    protected $sku;

    /**
     * @ManyToOne(targetEntity="Library\Model\Relationship\OptionOptionValue", inversedBy="option_option_value_skus")
     * @JoinColumn(name="assoc_option_option_value_id", referencedColumnName="id")
     * @var OptionOptionValue
     */
    protected $option_option_value;

    /**
     * @return Sku
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param Sku $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return OptionOptionValue
     */
    public function getOptionOptionValue()
    {
        return $this->option_option_value;
    }

    /**
     * @param OptionOptionValue $option_option_value
     */
    public function setOptionOptionValue($option_option_value)
    {
        $this->option_option_value = $option_option_value;
    }
}