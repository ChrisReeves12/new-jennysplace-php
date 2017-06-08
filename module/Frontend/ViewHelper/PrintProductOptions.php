<?php
/**
* The PrintProductOptions class definition.
*
* Outputs the product options on the product screen
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class PrintProductOptions
 * @package Frontend\ViewHelper
 */
class PrintProductOptions extends AbstractHelper
{
    /**
     * Prints product options for user to select on product screen
     * @param mixed $option_value_map[]
     *
     * @return string
     */
    public function __invoke($option_value_map)
    {
        ob_start();
        if (!is_null($option_value_map))
        {
            foreach ($option_value_map as $option_id => $value_info)
            {
                $option = $value_info[$option_id];
                $option_choices = $value_info['values'];

                echo "<div class='product_option inline'>";
                echo "<h5>".$option->getName()."</h5>";
                if (count($option_choices) > 0)
                {
                    foreach ($option_choices as $option_choice)
                    {
                        ?>
                        <input value="<?php echo $option_choice->getId(); ?>" type="radio" name="<?php echo $option->getId(); ?>"/> <?php echo $option_choice->getName(); ?>
                        <br/>
                    <?php
                    }
                }
                echo "</div>";
            }
        }
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}