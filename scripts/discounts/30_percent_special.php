<?php
/**
 * This discount gives the user 30% off order over $500, 20% off $200 and 10% off $100
 * This offer excludes dozen packs
 */

use Doctrine\ORM\EntityManager;
use Library\Model\Relationship\ProductCategory;
use Library\Model\Shop\Discount;
use Library\Model\Shop\ShopList;
use Library\Model\Shop\ShopListElement;

/**
 * @var ShopList $shop_list
 * @var Discount $discount
 * @var EntityManager $em
 */

// Find all category ids that represent dozen packs
$dozen_pack_children = $em
    ->createQuery("SELECT c.id FROM Library\\Model\\Category\\Category c WHERE c.parent_category = :category")
    ->setParameters(['category' => $em->getReference('Library\Model\Category\Category', 19)])->getResult();

$dozen_pack_ids[] = 19;
foreach ($dozen_pack_children as $dozen_pack_child)
{
    $dozen_pack_ids[] = $dozen_pack_child['id'];
}

// Find all category ids that represent general merch
$general_category_ids = [8,77,196,66,65,10,197,13,269,268,1000,267,10001,10002,10003,10004,10005,
    10006,266,68,137,70,69,87,25,207,76,10012,10014,10015,
    10016,57,96,208,210,211,212,272,131,213,214,153,141,216,59,60,139,64,218,104,219,144,145,221,220,14,154,222,226,223,224,225,155,228];

// Discount items, excluding dozen items
$list_items = $shop_list->getShopListElements();
$discount_percentage = null;
$total_discount_amount = 0;
$sub_total = 0;

/** @var ShopListElement $list_item */
foreach ($list_items as $list_item)
{
    // Exclude dozen pack items
    $is_dozen_pack = false;
    $product = $list_item->getSku()->getProduct();
    $category_products = $product->getProductCategories();

    /** @var ProductCategory $category */
    foreach ($category_products as $category_product)
    {
        if (in_array($category_product->getCategory()->getId(), $dozen_pack_ids))
            $is_dozen_pack = true;
    }

    if (!$is_dozen_pack)
        $sub_total += $list_item->getTotal();
}

// Determine discount amount based on sub-total
if ($sub_total >= 100 && $sub_total < 300)
{
    $discount_percentage = 0.1;
}
elseif ($sub_total >= 300.00 && $sub_total < 500)
{
    $discount_percentage = 0.2;
}
elseif ($sub_total > 500)
{
    $discount_percentage = 0.3;
}

/** @var ShopListElement $list_item */
if (!empty($discount_percentage))
{
    foreach ($list_items as $list_item)
    {
        // Exclude dozen pack items
        $is_dozen_pack = false;

        // General merch should get 10 percent off only
        $is_general_merch = false;

        $product = $list_item->getSku()->getProduct();
        $category_products = $product->getProductCategories();

        /** @var ProductCategory $category */
        foreach ($category_products as $category_product)
        {
            if (in_array($category_product->getCategory()->getId(), $dozen_pack_ids))
                $is_dozen_pack = true;
            elseif(in_array($category_product->getCategory()->getId(), $general_category_ids))
                $is_general_merch = true;
        }

        // Skip dozen packs
        if ($is_dozen_pack)
            continue;

        // Reset to 10 percent on general merch
        if ($is_general_merch)
            $discount_percentage = 0.1;

        // Take percentage off each item
        $discount_amount = $list_item->getTotal() * $discount_percentage;
        $total_discount_amount += $discount_amount;
    }
}
else
{
    $discount_amount = 0;
}

$shop_list->setDiscountAmount(number_format($total_discount_amount, 2, '.', ''));




