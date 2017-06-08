<?php
/**
* The OptionService class definition.
*
* This class handles all of the creating, updating and deleting of product options and values.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Product\Option;
use Library\Model\Product\OptionValue;
use Library\Model\Relationship\OptionOptionValue;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class OptionService
 * @package Library\Service
 */
class OptionService extends AbstractService
{
    /**
     * @param array $data
     * @param int $option_id
     *
     * @return Option
     * @throws \Exception
     */
   public function save($data, $option_id = null)
   {
       // Get the entity manager
       $em = EntityManagerSingleton::getInstance();

       // Check if the options are being updated or created
       if (!empty($option_id))
       {
           $option = $em->getRepository('Library\Model\Product\Option')->findOneById($option_id);
           if (!($option instanceof Option))
           {
              throw new \Exception("The option ID entered does not match a product option in the database.");
           }
       }
       else
       {
           $option = new Option();
           $em->persist($option);
       }

       // Set properties of option from data
       $option->setName($data['name']);

       // Get values from form
       if (!empty($data['value_data']))
       {
           $value_ids = explode(',', $data['value_data']);
           $option_values = $em->getRepository('Library\Model\Product\OptionValue')->findBy(['id' => $value_ids]);


           // Check if there are enough option values
           if (count($option_values) < 2)
           {
               throw new \Exception("All options must have at least two values.");
           }
       }
       else
       {
           throw new \Exception("All options must have at least two values");
       }


       // Get existing values
       $option_value_relationships = $option->getOptionOptionValues();

       // Remove values that no longer exist
       if (!empty($option_value_relationships) && count($option_value_relationships) > 0)
       {
           foreach ($option_value_relationships as &$option_value_relationship)
           {
               $match = false;
               foreach ($option_values as $option_value)
               {
                   if ($option_value->getId() == $option_value_relationship->getOptionValue()->getId())
                   {
                       $match = true;
                       break;
                   }
               }

               if (!$match)
               {
                   // Check if this option value is being used by other skus
                   $soov = $em->getRepository('Library\Model\Relationship\SkuOptionOptionValue')->findOneBy(['option_option_value' => $option_value_relationship]);
                   if (is_null($soov))
                   {
                       // Delete the relationship
                       $em->remove($option_value_relationship);
                       $option_value_relationships->removeElement($option_value_relationship);
                   }
               }
           }
       }

       // Add new values
       foreach ($option_values as $option_value)
       {
           $match = false;
           if (!empty($option_value_relationships) && count($option_value_relationships) > 0)
           {
               foreach ($option_value_relationships as $option_value_relationship)
               {
                   if ($option_value->getId() == $option_value_relationship->getOptionValue()->getId())
                   {
                       $match = true;
                       break;
                   }
               }
           }

           if (!$match)
           {
               $option_option_value = new OptionOptionValue();
               $option_option_value->setOption($option);
               $option_option_value->setOptionValue($option_value);
               $em->persist($option_option_value);
           }
       }

       return $option;
   }

    /**
     * Saves or updates an option value
     *
     * @param array $data
     * @param int $option_value_id
     *
     * @return OptionValue
     * @throws \Exception
     */
    public function save_option_value($data, $option_value_id)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $name = strip_tags(trim($data['option_value_name']));

        // Get if this is an update or a new value
        if (!empty($option_value_id))
        {
            $option_value = $em->getRepository('Library\Model\Product\OptionValue')->findOneBy($option_value_id);
            if (!($option_value instanceof OptionValue))
            {
                throw new \Exception("The option value being edited cannot be found in the database.");
            }
        }
        else
        {
            $option_value = new OptionValue();
            $em->persist($option_value);
        }

        // Add values to option value
        $option_value->setName($name);

        return $option_value;
    }

    /**
     * Deletes the option value
     *
     * @param int $value_id
     * @throws \Exception
     */
    public function delete_option_value($value_id)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $option_value = $em->getRepository('Library\Model\Product\OptionValue')->findOneById($value_id);
        if ($option_value instanceof OptionValue)
        {
            // Check to see if the value is being used
            if (count($option_value->getOptionValueOptions()) > 0)
            {
                throw new \Exception("This value is being used by an option and cannot be deleted.");
            }

            $em->remove($option_value);
        }
    }

    /**
     * Updates the current option value
     *
     * @param array $data
     *
     * @throws \Exception
     */
    public function update_option_value($data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $option_value = $em->getRepository('Library\Model\Product\OptionValue')->findOneById($data['value_id']);
        if (!($option_value instanceof OptionValue))
        {
            throw new \Exception("The option value you are trying to modify no longer exists in the database.");
        }

        $option_value->setName($data['name']);
    }
}