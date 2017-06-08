<?php
/**
 * This discount gives the user 30% off order over $500, 20% off $200 and 10% off $100
 * This also guarantees handbags off
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

// Find all category ids that represent dozen pack categories
$dozen_pack_children = $em
    ->createQuery("SELECT c.id FROM Library\\Model\\Category\\Category c WHERE c.parent_category = :category")
    ->setParameters(['category' => $em->getReference('Library\Model\Category\Category', 19)])->getResult();

// Find all handbag ids
$handbags_children = $em
    ->createQuery("SELECT c.id FROM Library\\Model\\Category\\Category c WHERE c.parent_category = :category")
    ->setParameters(['category' => $em->getReference('Library\Model\Category\Category', 57)])->getResult();


$dozen_pack_ids[] = 19;
$handbag_ids[] = 57;
foreach ($dozen_pack_children as $dozen_pack_child)
{
    $dozen_pack_ids[] = $dozen_pack_child['id'];
}

foreach ($handbags_children as $handbags_child)
{
    $handbag_ids[] = $handbags_child['id'];
}

// Discount items, excluding dozen items, but always discount handbag items
$list_items = $shop_list->getShopListElements();
$discount_percentage = null;
$total_discount_amount = 0;
$sub_total = 0;
$handbag_total = 0;

/** @var ShopListElement $list_item */
foreach ($list_items as $list_item)
{
    // Exclude dozen pack items
    $is_dozen_pack = $is_handbag = false;
    $product = $list_item->getSku()->getProduct();
    $category_products = $product->getProductCategories();

    /** @var ProductCategory $category */
    foreach ($category_products as $category_product)
    {
        $cat_id = $category_product->getCategory()->getId();

        if (in_array($cat_id, $dozen_pack_ids))
            $is_dozen_pack = true;
        elseif (in_array($cat_id, $handbag_ids))
            $is_handbag = true;
    }

    if (!$is_dozen_pack)
        $sub_total += $list_item->getTotal();

    if ($is_handbag)
        $handbag_total += $list_item->getTotal();
}

// Determine discount amount based on sub-total
if ($sub_total >= 100 && $sub_total < 200)
{
    $discount_percentage = 0.1;
}
elseif ($sub_total >= 200.01 && $sub_total < 500)
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
        $product = $list_item->getSku()->getProduct();
        $category_products = $product->getProductCategories();

        /** @var ProductCategory $category */
        foreach ($category_products as $category_product)
        {
            if (in_array($category_product->getCategory()->getId(), $dozen_pack_ids)) $is_dozen_pack = true;
        }

        if ($is_dozen_pack) continue;

        // Take percentage off each item
        $discount_amount = $list_item->getTotal() * $discount_percentage;
        $total_discount_amount += $discount_amount;
    }
}
elseif ($handbag_total > 0)
{
    // Take 10% off any hand bags
    $total_discount_amount = $handbag_total * 0.10;
}

$shop_list->setDiscountAmount(number_format($total_discount_amount, 2, '.', ''));




