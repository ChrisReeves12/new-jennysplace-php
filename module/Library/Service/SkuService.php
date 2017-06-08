<?php
/**
* The SkuService class definition.
*
* This service administers the creating, saving, deleting and other things for Skus.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Product\Product;
use Library\Model\Product\Sku;
use Library\Model\Relationship\SkuOptionOptionValue;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class SkuService
 * @package Library\Service
 */
class SkuService extends AbstractService
{
    const FLAG_REFRESH_PAGE = 1;
    const FLAG_DONT_REFRESH_PAGE = 2;

    /**
     * Saves or updates skus connect to the passed in product id
     *
     * @param array $data
     * @param int $product_id
     * @return int
     *
     * @throws \Exception
     */
    public function saveToProduct($data, $product_id)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Get the product
        $product = $em->getRepository('Library\Model\Product\Product')->findOneById($product_id);

        if (false === ($product instanceof Product))
        {
            throw new \Exception("The product being updated does not exist.");
        }

        // Delete the skus that should be deleted first
        if (!empty($data['delete_list']))
        {
            $skus_to_delete = $em->getRepository('Library\Model\Product\Sku')->findBy(['id' => $data['delete_list']]);
            foreach ($skus_to_delete as $key => $sku_to_delete)
            {
                $em->remove($sku_to_delete);
            }
        }

        unset($data['delete_list']);

        if (count($data) == 0)
        {
            // If there are no skus, use the default sku
            $default_sku = $product->getDefaultSku();
            if (!($default_sku instanceof Sku))
            {
                $default_sku = new Sku();
                $default_sku->setStatus($product->getStatusOverride());
                $default_sku->setQuantity(0);
                $default_sku->setIsDefault(true);
                $default_sku->setProduct($product);
                $em->persist($default_sku);
            }
        }

        if (count($data) == 1)
        {
            throw new \Exception("When adding additional skus to products, you must add at least 2, otherwise there is no point in adding additional skus as the system will always generate a default sku for single option products.");
        }

        // Separate skus to update and new skus
        $sku_ids_to_add = [];
        $sku_ids_to_update = [];

        if (!empty($data))
        {
            // First delete the default sku if it exists
            $default_sku = $product->getDefaultSku();

            if ($default_sku instanceof Sku)
                $em->remove($default_sku);

            foreach ($data as $sku_info)
            {
                $id = $sku_info['id'];
                if (strstr($id, 'new'))
                {
                    $sku_ids_to_add[] = $id;
                } else
                {
                    $sku_ids_to_update[] = $id;
                }
            }
        }

        // Handle adding new skus
        if (!empty($sku_ids_to_add))
        {
            foreach ($sku_ids_to_add as $sku_id_to_add)
            {
                $sku_info = null;
                foreach ($data as $sku_info)
                {
                    if ($sku_info['id'] == $sku_id_to_add)
                        break;
                }

                if (!is_null($sku_info))
                {
                    $sku_obj_to_add = new Sku();
                    $sku_obj_to_add->setIsDefault(false);
                    $sku_obj_to_add->setProduct($product);
                    $sku_status = $em->getRepository('Library\Model\Product\Status')->findOneById($sku_info['status']);
                    $sku_obj_to_add->setStatus($sku_status);
                    $sku_obj_to_add->setQuantity($sku_info['qty']);
                    $sku_obj_to_add->setNumber($sku_info['sku_number']);

                    // Add image if available
                    $image = $em->getRepository('Library\Model\Media\Image')->findOneById($sku_info['image_id']);
                    $sku_obj_to_add->setImage($image);

                    foreach ($sku_info['option_values'] as $option_id => $option_value_id)
                    {
                        $option_option_value = $em->getRepository('Library\Model\Relationship\OptionOptionValue')->findOneByIdAndValueId($option_id, $option_value_id);

                        $new_soov = new SkuOptionOptionValue();
                        $new_soov->setSku($sku_obj_to_add);
                        $new_soov->setOptionOptionValue($option_option_value);
                        $em->persist($new_soov);
                    }

                    $em->persist($sku_obj_to_add);
                }
            }
        }

        // Handle updates of existing skus
        if (!empty($sku_ids_to_update))
        {
            $skus_to_update_objs = $em->getRepository('Library\Model\Product\Sku')->findBy(['id' => $sku_ids_to_update]);
            $skus_to_update = [];
            foreach ($skus_to_update_objs as $sku_to_update_obj)
            {
                $skus_to_update[$sku_to_update_obj->getId()] = $sku_to_update_obj;
            }

            foreach ($data as $sku_info)
            {
                $sku_id = $sku_info['id'];
                $sku_obj_to_update = $skus_to_update[$sku_id];

                if (is_null($sku_obj_to_update))
                    continue;

                // Delete all option references from sku
                $soovs = $sku_obj_to_update->getSkuOptionOptionValues();
                foreach ($soovs as $soov)
                {
                    $em->remove($soov);
                }

                // Update the sku
                $sku_status = $em->getRepository('Library\Model\Product\Status')->findOneById($sku_info['status']);
                $sku_obj_to_update->setStatus($sku_status);
                $sku_obj_to_update->setQuantity($sku_info['qty']);
                $sku_obj_to_update->setNumber($sku_info['sku_number']);

                // Add image if available
                $image = $em->getRepository('Library\Model\Media\Image')->findOneById($sku_info['image_id']);
                $sku_obj_to_update->setImage($image);


                foreach ($sku_info['option_values'] as $option_id => $option_value_id)
                {
                    $option_option_value = $em->getRepository('Library\Model\Relationship\OptionOptionValue')->findOneByIdAndValueId($option_id, $option_value_id);

                    $new_soov = new SkuOptionOptionValue();
                    $new_soov->setSku($sku_obj_to_update);
                    $new_soov->setOptionOptionValue($option_option_value);
                    $em->persist($new_soov);
                }
            }
        }
    }
}