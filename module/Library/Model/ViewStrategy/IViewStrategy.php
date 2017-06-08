<?php
/**
 * The IViewStrategy class definition.
 *
 * This is the interface that all view strategies must implement
 * @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
 **/

namespace Library\Model\ViewStrategy;
use Library\Controller\JPController;


/**
 * Interface IViewStrategy
 * @package Library\Model\ViewStrategy
 */
interface IViewStrategy
{
    /**
     * Renders the output of the page
     *
     * @param JPController $controller
     * @return mixed
     */
    public function render(JPController $controller);
}