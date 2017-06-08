<?php
/**
* The PrintSkuDialog class definition.
*
* This view helper prints the sku dialog in the product section of the backend.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\ViewHelper;

use Zend\View\Helper\AbstractHelper;

class PrintSkuDialog extends AbstractHelper
{
    public function __invoke($sku, $product_options, $status_options)
    {
        // For new skus
        if (is_null($sku))
        {
            $sku_id = '';
            $sku_qty = 0;
            $sku_number = '';
        }
        else
        {
            $sku_id = $sku->getId();
            $sku_qty = $sku->getQuantity();
            $sku_number = $sku->getNumber();
        }

        ob_start();
        ?>

        <div data-id="<?php echo $sku_id; ?>" class="sku">
            <div class="delete"><span class="fa fa-close"> </span></div>
            <div class="inline image">
                <div class="delete-image"><span class="fa fa-close"> </span></div>
                <?php
                // Load image
                if (!is_null($sku))
                {
                    $image = $sku->getImage();
                    if (!empty($image))
                    {
                        ?>
                        <a data-img-id="<?php echo $image->getId(); ?>" class="fancybox image-link" href="/img/product_images/<?php echo $image->getUrl(); ?>">
                            <img src="/img/product_images/<?php echo $image->getUrl(); ?>" alt="<?php echo $image->getAlt() ?>" title="<?php echo $image->getTitle(); ?>"/>
                        </a>
                    <?php
                    }
                }
                ?>
            </div>
            <div class="inline image-upload">
                <input type="file" name="sku_image[<?php echo $sku_id; ?>]"/>
                <div class="sku-option-values">
                    <table>
                        <tr>
                            <th>Option Name</th>
                            <th>Option Value</th>
                            <th> </th>
                        </tr>
                        <?php
                        // Get options if the sku isn't set
                        if (is_null($sku))
                        {
                            if (count($product_options) > 0)
                            {
                                foreach ($product_options as $product_option)
                                {
                                    ?>
                                    <tr class="sku-option" data-option-id="<?php echo $product_option->getId(); ?>">
                                        <td><?php echo $product_option->getName(); ?></td>
                                        <td>
                                            <select class="sku-option-value">
                                            <?php
                                                // Get product option values
                                                $option_value_rels = $product_option->getOptionOptionValues();
                                                if (count($option_value_rels) > 0)
                                                {
                                                    foreach ($option_value_rels as $option_value_rel)
                                                    {
                                                        $option_value = $option_value_rel->getOptionValue();
                                                        ?>
                                                        <option value="<?php echo $option_value->getId(); ?>"><?php echo $option_value->getName(); ?></option>
                                                        <?php
                                                    }
                                                }
                                            ?>
                                            </select>
                                        </td>
                                        <td><a class="add-new-value" href="">[Add New Value]</a></td>
                                    </tr>
                                <?php
                                }
                            }
                        }

                        // If the sku is set, get options from skus
                        else
                        {
                            $sku_options = $sku->getSkuOptionOptionValues();

                            if (count($sku_options) > 0)
                            {
                                foreach ($sku_options as $sku_option)
                                {
                                    ?>
                                    <tr class="sku-option"
                                        data-option-id="<?php echo $sku_option->getOptionOptionValue()->getOption()->getId(); ?>">
                                        <td>
                                            <?php echo $sku_option->getOptionOptionValue()->getOption()->getName(); ?>
                                        </td>
                                        <td>
                                            <select class="sku-option-value">
                                                <?php
                                                $option_values = $sku_option->getOptionOptionValue()->getOption()->getOptionOptionValues();
                                                if (count($option_values) > 0)
                                                {
                                                    foreach ($option_values as $option_value)
                                                    {
                                                        $selected_option_value_id = $sku_option->getOptionOptionValue()->getOptionValue()->getId();

                                                        if ($selected_option_value_id == $option_value->getOptionValue()->getId())
                                                        {
                                                            $selected = "selected='selected'";
                                                        } else
                                                        {
                                                            $selected = "";
                                                        }

                                                        echo "<option value='" . $option_value->getOptionValue()->getId() . "' $selected>";
                                                        echo $option_value->getOptionValue()->getName();
                                                        echo "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td><a class="add-new-value" href="">[Add New Value]</a></td>
                                    </tr>
                                <?php
                                }
                            }
                        }
                        ?>
                    </table>
                </div>
            </div>
            <div class="inline sku-attr-section">
                <input class="qty" type="text" value="<?php echo $sku_qty; ?>" placeholder="Quantity" name="sku_qty[<?php echo $sku_id; ?>]"/>
                <input class="sku_number" type="text" value="<?php echo $sku_number; ?>" placeholder="Sku Number" name="sku_qty[<?php echo $sku_id; ?>]"/>
                <select class="status" name="sku_status[<?php echo $sku_id; ?>]">
                    <?php
                    // Load status options
                    if (!empty($status_options))
                    {
                        foreach ($status_options as $key => $value)
                        {
                            if (!is_null($sku))
                            {
                                $selected = ($sku->getStatus()->getId() == $key) ? "selected = 'selected'" : '';
                            }
                            else
                            {
                                $selected = '';
                            }

                            echo "<option $selected value='$key'>$value</option>";

                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}