<?php
/**
 * The BannerService class definition.
 *
 * This service performs the CRUD functions on banners
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\Page\Banner;
use Library\Model\Page\BannerSlide;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class BannerService
 * @package Library\Service
 */
class BannerService extends AbstractService
{
    /**
     * Saves a banner and all of its information to the database
     *
     * @param array $data
     *
     * @return Banner
     * @throws \Exception
     */
    public function save($data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // See if banner is being edited
        if (!empty($data['id']))
        {
            $banner = $em->getRepository('Library\Model\Page\Banner')->findOneById($data['id']);
            if (!($banner instanceof Banner))
            {
                throw new \Exception("The banner being edited cannot be found in the database.");
            }
        }
        else
        {
            $banner = new Banner();
            $banner->setDateCreated(new \DateTime());
            $banner->setDateModified(new \DateTime());
            $em->persist($banner);
        }

        // Save info to banner
        $banner->setLabel($data['banner_info']['label']);
        $banner->setAnimationSpeed($data['banner_info']['anim_speed']);
        $banner->setAnimationType($data['banner_info']['anim_type']);
        $banner->setDelayTime($data['banner_info']['delay_time']);
        $banner->setShowArrows($data['banner_info']['show_arrows']);
        $banner->setShowNavigation($data['banner_info']['show_nav']);
        $banner->setSlideDirection($data['banner_info']['slide_direction']);

        // Remove banner slides and add new ones
        $banner_slides = $banner->getBannerSlides();
        if (count($banner_slides) > 0)
        {
            foreach ($banner_slides as $banner_slide)
            {
                $banner_slides->removeElement($banner_slide);
                $em->remove($banner_slide);
            }
        }

        // Get images
        $image_ids = [];
        $image_map = [];
        foreach ($data['slide_data'] as $slide)
        {
            $image_ids[] = $slide['image_id'];
        }

        $images = $em->getRepository('Library\Model\Media\Image')->findBy(['id' => $image_ids]);

        foreach ($images as $image)
        {
            $image_map[$image->getId()] = $image;
        }

        $counter = count($data['slide_data']);
        foreach ($data['slide_data'] as $slide)
        {
            $banner_slide = new BannerSlide();
            $banner_slide->setUrl($slide['url']);
            $banner_slide->setImage($image_map[$slide['image_id']]);
            $banner_slide->setBanner($banner);
            $banner_slide->setDateCreated(new \DateTime());
            $banner_slide->setDateModified(new \DateTime());
            $banner_slide->setSortOrder($counter);
            $em->persist($banner_slide);
            $counter--;
        }

        return $banner;
    }
}