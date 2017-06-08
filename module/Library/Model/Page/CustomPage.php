<?php
/**
* The CustomPage class definition.
*
* The page class represents arbitrary pages that admins can make
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Model\Page;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Library\Model\AbstractModel;
use Library\Model\Traits\StandardModelTrait;

/**
 * Class CustomPage
 * @package Library\Model\Page
 */

/**
 * @Entity
 * @Table(name="custom_pages")
 * @HasLifecycleCallbacks
 */
class CustomPage extends AbstractModel
{
    use StandardModelTrait;

    /**
     * @Column(name="content", type="text", nullable=false)
     * @var string
     */
    protected $content;

    /**
     * @ManyToOne(targetEntity="Library\Model\Page\Page", cascade={"remove", "persist"})
     * @JoinColumn(name="page_id", referencedColumnName="id")
     * @var Page
     */
    protected $page;

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
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}