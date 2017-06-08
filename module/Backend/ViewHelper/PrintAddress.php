<?php
/**
* The PrintAddress class definition.
*
* This view helper displays address
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Backend\ViewHelper;

use Library\Model\User\Address;
use Zend\View\Helper\AbstractHelper;

/**
 * Class PrintAddress
 * @package Backend\ViewHelper
 */
class PrintAddress extends AbstractHelper
{
    public function __invoke(Address $address, $disable_edit = false)
    {
        ob_start();

            if (!$disable_edit):
            ?>
            <ul data-id="<?php echo $address->getId(); ?>" class="address list-group">
                <?php if (!empty($address->getCompany())): ?>
                    <li class="list-group-item"><input placeholder="Company" type="text" value="<?php echo $address->getCompany(); ?>" class="company"/></li>
                <?php endif; ?>

                <li class="list-group-item"><input placeholder="First Name" style="width: 90px;" type="text" class="first-name" value="<?php echo $address->getFirstName(); ?>"/> <input placeholder="Last Name" style="width: 90px;" type='text' class='last-name' value='<?php echo $address->getLastName(); ?>'/></li>
                <li class="list-group-item"><input placeholder="Address Line 1" type="text" class="line1" value="<?php echo $address->getLine1(); ?>"/></li>
                <li class="list-group-item"><input placeholder="Address Line 2" type="text" class="line2" value="<?php echo $address->getLine2(); ?>"/></li>
                <li class="list-group-item"><input placeholder="City" style="width: 96px;" type="text" class="city" value="<?php echo $address->getCity(); ?>"/>, <input style="width: 45px;" value="<?php echo $address->getState(); ?>" class="state" type="text" /> <input style="width: 70px;" type="text" class="zipcode" value="<?php echo $address->getZipcode(); ?>"/></li>

                <?php if (!empty($address->getPhone())): ?>
                    <li class="list-group-item">Phone: <input placeholder="Phone Number" type="text" class="phone" value="<?php echo $address->getPhone(); ?>"/></li>
                <?php endif; ?>

                <?php if (!empty($address->getEmail())): ?>
                    <li class="list-group-item">Email: <input placeholder="Email" style="width: 270px;" type="text" value="<?php echo $address->getEmail(); ?>" class="email"/></li>
                <?php endif; ?>
            </ul>
        <?php
        else:
        ?>
        <ul data-id="<?php echo $address->getId(); ?>" class="address list-group">
            <?php if (!empty($address->getCompany())): ?>
                <li class="list-group-item"><?php echo $address->getCompany(); ?></li>
            <?php endif; ?>

            <li class="list-group-item"><?php echo $address->getFirstName(); ?></li>
            <li class="list-group-item"><?php echo $address->getLine1(); ?></li>
            <li class="list-group-item"><?php echo $address->getLine2(); ?></li>
            <li class="list-group-item"><?php echo $address->getCity(); ?>, <?php echo $address->getState(); ?> <?php echo $address->getZipcode(); ?></li>

            <?php if (!empty($address->getPhone())): ?>
                <li class="list-group-item">Phone: <?php echo $address->getPhone(); ?></li>
            <?php endif; ?>

            <?php if (!empty($address->getEmail())): ?>
                <li class="list-group-item">Email: <?php echo $address->getEmail(); ?></li>
            <?php endif; ?>
        </ul>
        <?php
            endif;
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}