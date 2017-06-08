<?php
/**
* The ProductName class definition.
*
* This view helper displays a link of a product's name
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class ProductName
 * @package Frontend\ViewHelper
 */
class ProductName extends AbstractHelper
{
    /**
     * @param \Library\Model\Product\Product $product
     * @return string
     */
    public function __invoke($product)
    {
        ob_start();
        ?>
            <a title="<?php echo $product->getName(); ?>" href="<?php echo $this->view->url('product', ['handle' => $product->getPage()->getUrlHandle()]); ?>">
                <?php echo $product->getName(); ?>
            </a>
        <?php
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}