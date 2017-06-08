<?php
/**
* The Page class definition.
*
* Pages are simply web pages that customers can visit
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Index;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="pages", indexes={@Index(name="idx_inactive_url", columns={"inactive", "url_handle"})})
 * @HasLifecycleCallbacks
 **/
class Page extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="title", type="string", length=500, nullable=false)
     * @var string
     */
    protected $title;

    /**
     * @Column(name="url_handle", type="string", length=255, unique=true, nullable=false)
     * @var string
     */
    protected $url_handle;

    /**
     * @Column(name="page_type", type="string", length=255, nullable=false)
     * @var string
     */
    protected $page_type;

    /**
     * @Column(name="access", type="integer", nullable=false)
     * @var int
     */
    protected $access;

    /**
     * @Column(name="inactive", type="boolean", nullable=false)
     * @bool
     */
    protected $inactive;

    /**
     * @Column(name="keywords", type="text", length=500, nullable=true)
     * @var string
     */
    protected $keywords;

    /**
     * @Column(name="description", type="text", length=500, nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @Column(name="head_scripts", type="text", nullable=true)
     * @var string
     */
    protected $head_scripts;

    /**
     * @Column(name="stylesheets", type="text", nullable=true)
     * @var string
     */
    protected $stylesheets;

    /**
     * @Column(name="footer_scripts", type="text", nullable=true)
     * @var string
     */
    protected $footer_scripts;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrlHandle()
    {
        return $this->url_handle;
    }

    /**
     * @param string $url_handle
     */
    public function setUrlHandle($url_handle)
    {
        $this->url_handle = $url_handle;
    }

    /**
     * @return int
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @param int $access
     */
    public function setAccess($access)
    {
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getHeadScripts()
    {
        return $this->head_scripts;
    }

    /**
     * @param string $head_scripts
     */
    public function setHeadScripts($head_scripts)
    {
        $this->head_scripts = $head_scripts;
    }

    /**
     * @return string
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * @return bool
     */
    public function getInactive()
    {
        return $this->inactive;
    }

    /**
     * @param bool $inactive
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    }


    /**
     * @param string $stylesheets
     */
    public function setStylesheets($stylesheets)
    {
        $this->stylesheets = $stylesheets;
    }

    /**
     * @return string
     */
    public function getFooterScripts()
    {
        return $this->footer_scripts;
    }

    /**
     * @param string $footer_scripts
     */
    public function setFooterScripts($footer_scripts)
    {
        $this->footer_scripts = $footer_scripts;
    }

    /**
     * @return string
     */
    public function getPageType()
    {
        return $this->page_type;
    }

    /**
     * @param string $page_type
     */
    public function setPageType($page_type)
    {
        $this->page_type = $page_type;
    }

    /**
     * Returns the full url of the page
     * @return string
     */
    public function getFullUrl()
    {
        $full_url = "";

        switch ($this->getPageType())
        {
            case 'category':
                $full_url = '/category/' . $this->getUrlHandle();
                break;

            case 'product':
                $full_url = '/product/' . $this->getUrlHandle();
                break;

            case 'home':
                $full_url = '/';
                break;

            case 'search':
                $full_url = '/search';
                break;
        }

        return $full_url;
    }
}