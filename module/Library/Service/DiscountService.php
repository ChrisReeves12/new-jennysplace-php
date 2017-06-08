<?php
/**
* The DiscountService class definition.
*
* This service administers and manages the editing of discounts and discount actions
* @author Christopher Lee Reeves <ChrisReeves12@yahoo.com>
**/

namespace Library\Service;

use Library\Model\Shop\Discount;
use Library\Model\Shop\DiscountAction;
use Library\Model\Shop\ShippingMethod;
use Library\Model\Shop\ShopList;
use Library\Service\DB\EntityManagerSingleton;

/**
 * Class DiscountService
 * @package Library\Service
 */
class DiscountService extends AbstractService
{
    /**
     * Save a discount action or update it.
     *
     * @param array $data
     *
     * @return DiscountAction
     * @throws \Exception
     */
    public function save_action($data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Check if we make a new action or save
        if (!empty($data['discountaction']))
        {
            $discount_action = $em->getRepository('Library\Model\Shop\DiscountAction')->findOneById($data['discountaction']);
            if (!($discount_action instanceof DiscountAction))
            {
                throw new \Exception("The discount action ID passed in does not exist in the database.");
            }
        }
        else
        {
            $discount_action = new DiscountAction();
            $em->persist($discount_action);
        }

        // Set up the action
        $discount_action->setName($data['action_name']);
        $discount_action->setShippingDiscount($data['shipping_discount']);
        $discount_action->setShipDiscountType($data['shipping_discount_type']);
        $discount_action->setTotalDiscountType($data['product_discount_type']);
        $discount_action->setTotalDiscount($data['product_total_discount']);

        // Check for shipping method
        if (!empty($data['shipping_method']) && $data['shipping_method'] > 0)
        {
            $shipping_method = $em->getRepository('Library\Model\Shop\ShippingMethod')->findOneById($data['shipping_method']);
            if ($shipping_method instanceof ShippingMethod)
            {
                $discount_action->setShippingMethod($shipping_method);
            }
            else
            {
                throw new \Exception("The shipping method used does not exist.");
            }
        }

        return $discount_action;
    }

    /**
     * Saves or creates a discount
     *
     * @param $data
     *
     * @return Discount
     * @throws \Exception
     *
     */
    public function save_discount($data)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        // Check for updating or saving
        if (!empty($data['discount']))
        {
            $discount = $em->getRepository('Library\Model\Shop\Discount')->findOneById($data['discount']);
            if (!($discount instanceof Discount))
            {
                throw new \Exception("The discount ID being edited cannot be found in the database.");
            }
        }
        else
        {
            $discount = new Discount();
            $em->persist($discount);
        }

        // Save discount information
        $discount->setName($data['discount_name']);
        $discount->setCode($data['discount_code']);
        $discount->setScriptName($data['discount_script']);
        $discount->setStartDate(new \DateTime($data['start_date']));
        $discount->setEndDate(new \DateTime($data['end_date']));
        $discount->setDollarHurdle($data['dollar_hurdle']);
        $discount->setIsInactive($data['inactive']);

        // Check if script exists
        if (!empty($discount->getScriptName()))
        {
            if (!file_exists(getcwd() . '/scripts/discounts/' . $discount->getScriptName() . '.php'))
                throw new \Exception('The script for this discount cannot be found.');
        }

        // Remove discount actions to update with new ones
        $discount_action_relationships = $discount->getDiscountDiscountActions();
        if (count($discount_action_relationships) > 0)
        {
            foreach ($discount_action_relationships as $discount_action_relationship)
            {
                $em->remove($discount_action_relationship);
            }
        }

        // Add discount actions
        $discount_action_ids = explode(',', $data['discount_action_info']);
        if (count($discount_action_ids) > 0 || !empty($discount->getScriptName()))
        {
            if (empty($discount->getScriptName()))
            {
                foreach ($discount_action_ids as $discount_id)
                {
                    $discount_action = $em->getRepository('Library\Model\Shop\DiscountAction')->findOneById($discount_id);
                    if (!($discount_action instanceof DiscountAction))
                    {
                        throw new \Exception("Discount action ID " . $discount_id . " cannot be found in the database.");
                    }

                    $discount->addDiscountAction($discount_action);
                    $em->persist($discount_action);
                }
            }
        }
        else
        {
            throw new \Exception("Discounts must have at least one discount action or be regulated by a script.");
        }

        return $discount;
    }

    /**
     * Processes the discounts that are on the passed in shop list
     * @param ShopList $shop_list
     */
    public function processShopListDiscounts(ShopList $shop_list)
    {
        // Get entity manager
        $em = EntityManagerSingleton::getInstance();

        $shop_list->setDiscountAmount(0);
        $shop_list->setShippingCostOverride(null);

        // Process global discount
        if ($shop_list->getSubTotal() > 0)
        {
            $global_discount_id = Settings::get('global_discount');
            if ($global_discount_id != '0')
            {
                $global_discount = $em->getRepository('Library\Model\Shop\Discount')->findOneById($global_discount_id);
                if ($global_discount instanceof Discount)
                {
                    $this->processSingleShopListDiscount($shop_list, $global_discount);
                }
            }

            // Process list discounts
            $discount_rels = $shop_list->getShopListDiscounts();

            if (count($discount_rels) > 0)
            {
                foreach ($discount_rels as $discount_rel)
                {
                    $discount = $discount_rel->getDiscount();
                    if (false == $this->processSingleShopListDiscount($shop_list, $discount))
                        continue;
                }
            }
        }

    }

    /**
     * Processes a single discount against the shop list passed in
     * @param ShopList $shop_list
     * @param Discount $discount
     * @return bool
     */
    public function processSingleShopListDiscount(ShopList $shop_list, Discount $discount)
    {
        $discount_action_rels = $discount->getDiscountDiscountActions();
        $em = $this->getServiceManager()->get('entity_manager');

        // Check date range
        $start_date = $discount->getStartDate();
        $end_date = $discount->getEndDate();
        $now = new \DateTime();

        if ($now < $start_date || $now > $end_date)
            return false;

        // Run script if it is present
        if (!empty($discount->getScriptName()))
        {
            if (file_exists(getcwd() . '/scripts/discounts/' . $discount->getScriptName() . '.php'))
                include getcwd() . '/scripts/discounts/' . $discount->getScriptName() . '.php';
        }
        else
        {
            // Check dollar hurdle
            if (!is_null($discount->getDollarHurdle()))
            {
                if ($shop_list->getSubTotal() < $discount->getDollarHurdle())
                    return false;
            }

            // Process each action
            if (count($discount_action_rels) > 0)
            {
                foreach ($discount_action_rels as $discount_action_rel)
                {
                    $discount_action = $discount_action_rel->getDiscountAction();

                    // Process total discount actions
                    if (!is_null($discount_action->getTotalDiscount()) && $discount_action->getTotalDiscount() > 0)
                    {
                        $total_discount = $discount_action->getTotalDiscount();
                        $total_discount_type = $discount_action->getTotalDiscountType();

                        if ($total_discount_type == 'percent')
                        {
                            $sub_total = $shop_list->getSubTotal();
                            $discount_amount = $sub_total * ($total_discount * 0.01);
                            $discount_amount = number_format($discount_amount, 2, '.', '');

                            $shoplist_discount_amount = $shop_list->getDiscountAmount() + $discount_amount;
                            $shop_list->setDiscountAmount($shoplist_discount_amount);
                        }
                        elseif ($total_discount_type == 'dollar')
                        {
                            $shoplist_discount_amount = $shop_list->getDiscountAmount() + $total_discount;
                            $shop_list->setDiscountAmount($shoplist_discount_amount);
                        }
                    }

                    // Process shipping discount actions
                    if (!is_null($discount_action->getShippingDiscount()))
                    {
                        if (is_null($shop_list->getShippingMethod()) || is_null($discount_action->getShippingMethod()))
                            continue;

                        if ($shop_list->getShippingMethod()->getId() == $discount_action->getShippingMethod()->getId())
                        {
                            $shipping_discount = $discount_action->getShippingDiscount();
                            $shipping_discount_type = $discount_action->getShipDiscountType();
                            $current_ship_rate = $shop_list->getShippingCost();

                            // Process shipping discount
                            $discount_amount = 0;

                            if ($shipping_discount_type == 'percent')
                            {
                                $discount_amount = $current_ship_rate * ($shipping_discount * 0.01);
                                $discount_amount = number_format($discount_amount, 2, '.', '');
                            }
                            elseif ($shipping_discount_type == 'dollar')
                            {
                                $discount_amount = $shipping_discount;
                            }

                            if ($shipping_discount_type != 'rate')
                            {
                                $shoplist_discount_amount = $shop_list->getDiscountAmount() + $discount_amount;
                                $shop_list->setDiscountAmount($shoplist_discount_amount);
                            }
                            else
                            {
                                // If a rate has been specified, use that as a flat rate
                                $shop_list->setShippingCostOverride($shipping_discount);
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Applies a shipping method to a discount action
     * 
     * @param DiscountAction $discount_action
     * @param int $shipping_method_carrier_id
     * @param float $current_ship_rate
     * @param string $shipping_carrier
     *
*@return float
     */
    public function getDiscountShippingMethodPrice($discount_action, $shipping_method_carrier_id, $shipping_carrier, $current_ship_rate)
    {
        $new_rate = null;

        // Process shipping discount actions
        if (!is_null($discount_action->getShippingDiscount()))
        {
            // Find correct shipping method
            /** @var ShippingMethod $shipping_method */
            $shipping_method = EntityManagerSingleton::getInstance()->getRepository('Library\Model\Shop\ShippingMethod')->findOneBy(['carrier' => $shipping_carrier,
                'carrier_id' => $shipping_method_carrier_id]);

            if ((!is_null($shipping_method)) && !is_null($discount_action->getShippingMethod()))
            {
                if ($shipping_method->getId() == $discount_action->getShippingMethod()->getId())
                {
                    $new_rate = $current_ship_rate;
                    $shipping_discount = $discount_action->getShippingDiscount();
                    $shipping_discount_type = $discount_action->getShipDiscountType();

                    // Process shipping discount
                    if ($shipping_discount_type == 'percent')
                    {
                        $discount_amount = $current_ship_rate * ($shipping_discount * 0.01);
                        $discount_amount = number_format($discount_amount, 2, '.', '');
                        $new_rate = $current_ship_rate - $discount_amount;
                    }
                    elseif ($shipping_discount_type == 'dollar')
                    {
                        $new_rate = $current_ship_rate - $shipping_discount;
                    }
                    elseif ($shipping_discount_type == 'rate')
                    {
                        $new_rate = $shipping_discount;
                    }
                }
            }
        }

        return $new_rate;
    }
}