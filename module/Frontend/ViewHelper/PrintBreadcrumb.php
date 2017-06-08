<?php
/**
 * The PrintBreadcrumb class definition.
 *
 * Prints out the navigation breadcrumb
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Frontend\ViewHelper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class PrintBreadcrumb
 * @package Frontend\ViewHelper
 */
class PrintBreadcrumb extends AbstractHelper
{
    /**
     * @param array $breadcrumb_data
     * @return string
     */
    public function __invoke($breadcrumb_data)
    {
        ob_start();
        
        if (!empty($breadcrumb_data['first'])): echo '<a href="' . $breadcrumb_data['first']['url'] . '">' . $breadcrumb_data['first']['title'] . '</a> <i class="fa fa-angle-double-right"></i> '; endif;
        echo '<a href="'.$breadcrumb_data['whence']['url'].'">' . $breadcrumb_data['whence']['title'] . '</a> <i class="fa fa-angle-double-right"></i> ' . $breadcrumb_data['current']['title'];

        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}