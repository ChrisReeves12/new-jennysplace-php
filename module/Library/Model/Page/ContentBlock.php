<?php
/**
* The ContentBlock class definition.
*
* The description of the class
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

/**
 * Class ContentBlock
 * @package Library\Model\Page
 */
class ContentBlock
{
    /**
     * @var string
     */
    protected $handle;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var \DateTime
     */
    protected $timestamp;

    /**
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param \DateTime $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
}