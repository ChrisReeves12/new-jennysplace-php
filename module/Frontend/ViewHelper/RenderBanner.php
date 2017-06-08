<?php
/**
 * The RenderBanner class definition.
 *
 * This helper draws banners on pages in the frontend
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Frontend\ViewHelper;

use Library\Model\Page\Banner;
use Library\Service\DB\EntityManagerSingleton;
use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class RenderBanner
 * @package Frontend\ViewHelper
 */
class RenderBanner extends AbstractHelper
{
    public function __invoke($banner_label)
    {
        $output = "";
        $em = EntityManagerSingleton::getInstance();

        $banner = $em->getRepository('Library\Model\Page\Banner')->findOneBy(['label' => $banner_label]);
        if ($banner instanceof Banner)
        {
            $banner_slides = $banner->getBannerSlides()->toArray();

            if (!empty($banner_slides))
            {
                ob_start();
                ?>
                <div class="flexslider"
                     data-slide-direction="<?php echo $banner->getSlideDirection(); ?>"
                     data-show-arrows="<?php echo $banner->getShowArrows(); ?>"
                     data-show-nav="<?php echo $banner->getShowNavigation(); ?>"
                     data-anim-type="<?php echo $banner->getAnimationType(); ?>"
                     data-delay="<?php echo $banner->getDelayTime() * 1000; ?>"
                     data-anim-speed="<?php echo $banner->getAnimationSpeed(); ?>">
                    <ul class="slides">
                        <?php foreach ($banner_slides as $banner_slide): ?>
                            <li>
                                <a <?php if (!empty($banner_slide->getUrl())): echo "href='{$banner_slide->getUrl()}'"; endif; ?>>
                                    <img src="/img/banner_images/<?php echo $banner_slide->getImage()->getUrl(); ?>"/>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php
                $output = ob_get_contents();
                ob_end_clean();
            }
        }

        return $output;
    }
}