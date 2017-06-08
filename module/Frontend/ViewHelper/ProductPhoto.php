<?php
/**
* The ProductPhoto class definition.
*
* This view helper displays a linked photo of the product
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Library\Service\Settings;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class ProductPhoto
 * @package Frontend\ViewHelper
 */
class ProductPhoto extends AbstractHelper
{
    /**
     * @param \Library\Model\Product\Product $product
     * @return string
     */
    public function __invoke($product)
    {
        $image_path = Settings::get('image_path');

        ob_start();
        ?>
            <a title="<?php echo $product->getName(); ?>" href="<?php echo $this->view->url('product', ['handle' => $product->getPage()->getUrlHandle()]); ?>">
                <?php if (!empty($product->getDefaultImage())): ?>
                    <img alt="<?php echo $product->getName(); ?>" src="<?php echo $image_path; ?>/product_images/<?php echo $product->getDefaultImage()->getUrl(); ?>"/>
                <?php else: ?>
                    <img alt="<?php echo $product->getName(); ?>" src="<?php echo $image_path; ?>/layout_images/no_photo.jpg"/>
                <?php endif; ?>
            </a>
        <?php
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }
}