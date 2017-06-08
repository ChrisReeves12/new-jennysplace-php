<?php
/**
* The BannerController class definition.
*
* This controller manages the creation, viewing and updating of banners
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\Banner\AddPhotos;
use Library\Form\Banner\CreateUpdate;
use Library\Model\Page\Banner;
use Library\Service\DB\EntityManagerSingleton;
use Zend\View\Model\JsonModel;

/**
 * Class BannerController
 * @package Backend\Controller
 */
class BannerController extends JPController
{
    protected $banner;
    protected $banner_form;
    protected $add_photos_form;
    protected $id;

    /**
     * The page for editing a single banner
     * @return array
     * @throws \Exception
     */
    public function singleAction()
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();
        $this->id = isset($_GET['id']) ? $_GET['id'] : null;

        // Load banner if applicable
        if (!empty($this->id))
            $this->banner = $em->getRepository('Library\Model\Page\Banner')->findOneById($this->id);

        // Load forms
        $this->banner_form = new CreateUpdate();
        $this->add_photos_form = new AddPhotos();

        // Handle post
        $response = $this->handle_post();
        if (!empty($response))
        {
            return new JsonModel($response);
        }

        // Get banner information
        if (isset($this->id))
        {
            $this->banner = $em->getRepository('Library\Model\Page\Banner')->findOneById($this->id);
            if (!($this->banner instanceof Banner))
            {
                throw new \Exception("The banner being edited cannot be found in the database");
            }

            $this->banner_form->get('label')->setValue($this->banner->getLabel());
            $this->banner_form->get('anim_speed')->setValue($this->banner->getAnimationSpeed());
            $this->banner_form->get('delay_time')->setValue($this->banner->getDelayTime());
            $this->banner_form->get('anim_type')->setValue($this->banner->getAnimationType());
            $this->banner_form->get('slide_direction')->setValue($this->banner->getSlideDirection());
            $this->banner_form->get('show_nav')->setValue($this->banner->getShowNavigation());
            $this->banner_form->get('show_arrows')->setValue($this->banner->getShowArrows());
        }

        // Attach javascript
        $this->getServiceLocator()->get('ViewRenderer')->headScript()->appendFile('/js/backend/banner.js');

        return [
            'banner' => $this->banner,
            'banner_form' => $this->banner_form,
            'add_photos_form' => $this->add_photos_form
        ];
    }

    /**
     * Handle all post requests
     * @return array
     * @throws \Exception
     */
    public function handle_post()
    {
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $files = $_FILES;
            $data = array_merge_recursive($data, $files);
            $task = $data['task'];
            unset($data['task']);

            // Process post according to the task
            switch ($task)
            {
                case 'save_banner':

                    // Save the banner
                    $data['id'] = $this->id;
                    $banner_service = $this->getServiceLocator()->get('banner');
                    $banner = $banner_service->save($data);
                    EntityManagerSingleton::getInstance()->flush();
                    return ['error' => false, 'banner_id' => $banner->getId()];
                    break;

                case 'save_banner_slide_images':

                    // Save images
                    $image_service = $this->getServiceLocator()->get('image');
                    $images = $image_service->save($data, 'banner_images', $this->add_photos_form->getInputFilter());
                    EntityManagerSingleton::getInstance()->flush();

                    // Get image array to send back to front end
                    $image_data = [];
                    foreach ($images as $image)
                    {
                        $image_data[$image->getId()] = $image->getUrl();
                    }

                    return ['error' => false, 'images' => $image_data];
                    break;
            }
        }

        return null;
    }
}