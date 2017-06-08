<?php
/**
* The Money class definition.
*
* This view helper formats the incoming text into money format.
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Frontend\ViewHelper;

use Zend\Form\View\Helper\AbstractHelper;

/**
 * Class Money
 * @package Frontend\ViewHelper
 */
class Money extends AbstractHelper
{
    public function __invoke($input)
    {
        return '$' . number_format($input, 2, '.', ',');
    }
}