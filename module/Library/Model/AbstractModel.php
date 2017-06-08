<?php
/**
* The AbstractModel class definition.
*
* Represents the basis of which all models come from.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model;

use Doctrine\Common\Collections\Collection;
use Zend\Stdlib\Hydrator\ClassMethods;

/**
 * Class AbstractModel
 * @package Library\Model
 */
abstract class AbstractModel
{
    /**
     * Sets the class memebers of the entity by taking information from
     * a key/value array.
     * @param array $data_array
     */
    public function setData($data_array)
    {
        $hydrator = new ClassMethods();
        $hydrator->hydrate($data_array, $this);
    }

    /**
     * Returns a collection of values on the model
     *
     * @param array $ignore_attribute
     * @return array
     */
    public function toArray($ignore_attribute = null)
    {
        $class_methods = get_class_methods($this);
        $res_array = [];

        if (!empty($class_methods))
        {
            foreach ($class_methods as $class_method)
            {
                if (strpos($class_method, 'get') === 0)
                {
                    $value = $this->{$class_method}();
                    $value_name = str_replace('get', '', $class_method);
                    $value_name = strtolower($value_name);

                    if ($value_name != $ignore_attribute)
                    {
                        if ($value instanceof Collection)
                        {
                            $collection = [];
                            foreach ($value as $value_element)
                            {
                                $collection[] = $value_element;
                            }

                            $value[$value_name] = $collection;
                        }
                        elseif ($value instanceof AbstractModel)
                        {
                            $res_array[$value_name] = $value->toArray();
                            $res_array[$value_name]['_data_type'] = get_class($value);
                        }
                        elseif ($value instanceof \DateTime)
                        {
                            $res_array[$value_name] = $value->format("m-d-Y");
                        }
                        else
                        {
                            $res_array[$value_name] = $value;
                        }
                    }
                }
            }
        }

        return $res_array;
    }

    /**
     * Displays the human readable name of the current instance of the entity for menus
     * @return string
     */
    public function showDisplayName()
    {
        $display_name = "";
        if (method_exists($this, 'getName'))
        {
            $display_name = $this->getName();
        }
        elseif(method_exists($this, 'getLabel'))
        {
            $display_name = $this->getLable();
        }

        return $display_name;
    }
}