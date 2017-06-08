<?php
/**
 * The ContentBlockService class definition.
 *
 * The description of the class
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Service;

use Library\Model\AbstractModel;
use Library\Model\Page\ContentBlock;
use Zend\Config\Reader\Xml as XmlReader;
use Zend\Config\Writer\Xml as XmlWriter;

/**
 * Class ContentBlockService
 * @package Library\Service
 */
class ContentBlockService extends AbstractService
{
    /**
     * Saves or updates a content block
     *
     * @param ContentBlock $content_block
     *
     * @return ContentBlock
     * @throws \Exception
     */
    public function save(ContentBlock $content_block)
    {
        if (!($content_block instanceof ContentBlock))
        {
            throw new \Exception("The 'Save' function of the Content Block service requires a content block to be passed in.");
        }
        elseif (empty($content_block->getHandle()))
        {
            throw new \Exception("The content block being passed in requires a handle.");
        }

        if (empty($content_block->getContent()))
            $content_block->setContent("<span></span>");

        // Get file to save contents to
        $file = getcwd() . '/content.xml';

        // Create a file reader
        $reader = new XmlReader();
        $writer = new XmlWriter();

        // Get contents of file from XML
        $contents_array_xml = $reader->fromFile($file);

        // Save content to handle in file
        $timestamp = new \DateTime();
        $contents_array_xml[strtolower($content_block->getHandle())]['content'] = $content_block->getContent();
        $contents_array_xml[strtolower($content_block->getHandle())]['timestamp'] = $timestamp->format('Y-m-d H:i:s');

        // Save
        $result = $writer->processConfig($contents_array_xml);
        file_put_contents($file, $result);

        // Return the new persisted content block
        $content_block->setTimestamp($timestamp);
        return $content_block;
    }

    /**
     * Find a content block by handle
     *
     * @param $handle
     *
     * @return ContentBlock|null
     */
    public function findByHandle($handle)
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
            return $content_block;
        }
        else
        {
            return null;
        }
    }

    /**
     * Delete handles from the content block storage
     *
     * @param array $handles
     */
    public function delete($handles)
    {
        // Create a file reader
        $reader = new XmlReader();
        $writer = new XmlWriter();

        // Get file to save contents to
        $file = getcwd() . '/content.xml';
        $contents_array_xml = $reader->fromFile($file);

        if (is_array($handles))
        {
            if (count($handles) > 0)
            {
                foreach ($handles as $handle)
                {
                    unset($contents_array_xml[$handle]);
                }
            }
        }
        elseif (is_string($handles))
        {
            $handle = $handles;
            unset($contents_array_xml[$handle]);
        }

        // Save new data
        $result = $writer->processConfig($contents_array_xml);
        file_put_contents($file, $result);
    }

    /**
     * @param int[] $ids
     * @param AbstractModel $entity
     */
    public function deleteByIds($ids, $entity)
    {
        $this->delete($ids);
    }
}