<?php
/**
* The ContentBlockController class definition.
*
* This controllers houses all the functions used to update and create content blocks
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\Controller;

use Library\Controller\JPController;
use Library\Form\ContentBlock\CreateUpdate;
use Library\Model\Page\ContentBlock;

/**
 * Class ContentBlockController
 * @package Backend\Controller
 */
class ContentBlockController extends JPController
{
    public function singleAction()
    {
        $handle = !empty($_GET['handle']) ? $_GET['handle'] : null;
        $content_block_service = $this->getServiceLocator()->get('contentBlock');

        // Hydrate form
        if (!is_null($handle))
        {
            $content_block = $content_block_service->findByHandle($handle);
            if (!($content_block instanceof ContentBlock))
            {
                throw new \Exception("The content block being queried does not exist.");
            }
        }

        // Create form
        $create_update = new CreateUpdate();

        // Handle posts
        if ($this->getRequest()->isPost())
        {
            $data = $this->getRequest()->getPost()->toArray();
            $create_update->setData($data);

            if ($create_update->isValid())
            {
                $new_data = $create_update->getData();

                if (!($content_block instanceof ContentBlock))
                {
                    $content_block = new ContentBlock();
                }

                $content_block->setHandle($new_data['handle']);
                $content_block->setContent($new_data['content']);

                $content_block = $content_block_service->save($content_block);
                return $this->redirect()->toUrl('?handle=' . $content_block->getHandle());
            }
        }

        // Hydrate form
        if (!is_null($content_block))
        {
            $content_block = $content_block_service->findByHandle($handle);
            if (!($content_block instanceof ContentBlock))
            {
                throw new \Exception("The content block being queried does not exist.");
            }

            $create_update->get('handle')->setValue($content_block->getHandle());
            $create_update->get('content')->setValue($content_block->getContent());
        }

        $this->getServiceLocator()->get('ViewRenderer')->headLink()->appendStylesheet('/css/backend/content_block.css');

        return ['create_update' => $create_update];
    }
}