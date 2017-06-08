<?php
/**
* The PrintContentBlock class definition.
*
* This view helper prints a content block from handle
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Library\Model\Page\ContentBlock;
use Zend\Form\View\Helper\AbstractHelper;
use Zend\Config\Reader\Xml as XmlReader;

/**
 * Class PrintContentBlock
 * @package Frontend\ViewHelper
 */
class PrintContentBlock extends AbstractHelper
{
    public function __invoke($handle)
    {
        $content_block = new ContentBlock();
        $handle = strtolower($handle);

        // Get file to save contents to
        $file = getcwd() . '/content.xml';

        // Create a file reader
        $reader = new XmlReader();
        $contents_array_xml = $reader->fromFile($file);
        if (isset($contents_array_xml[$handle]))
        {
            $content = $contents_array_xml[$handle];
            $content_block->setHandle($handle);
            $content_block->setContent($content['content']);
            $content_block->setTimestamp(new \DateTime($content['timestamp']));
        }

        // Get content block
        if ($content_block instanceof ContentBlock)
        {
            return $content_block->getContent();
        }
        else
        {
            return '';
        }
    }
}