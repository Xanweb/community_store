<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Tax;

use Concrete\Core\Support\Facade\DatabaseORM as dbORM;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Product\Product as StoreProduct;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Wholesale;
use Database;
use Config;

class Tax
{
    public static function getTaxRates()
    {
        if(Wholesale::isUserWholesale()){
            return $taxRates = [];
        }
        $em = dbORM::entityManager();
        $taxRates = $em->createQuery('select tr from \Concrete\Package\CommunityStore\Src\CommunityStore\Tax\TaxRate tr')->getResult();

        return $taxRates;
    }

    public static function getTaxes($format = false)
    {
        $taxRates = self::getTaxRates();
        $taxes = [];
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate->isTaxable()) {
                    $taxAmounts = $taxRate->calculate();

                    $productTaxAmount = $taxAmounts['producttax'];
                    $shippingTaxAmount = $taxAmounts['shippingtax'];
                    $taxAmount = $productTaxAmount + $shippingTaxAmount;

                    if ($productTaxAmount > 0 || $shippingTaxAmount > 0) {
                        $tax = true;
                    } else {
                        $tax = false;
                    }
                    if (true == $format) {
                        $taxAmount = StorePrice::format($taxAmount);
                    }
                    $taxes[] = [
                        'name' => $taxRate->getTaxLabel(),
                        'producttaxamount' => $productTaxAmount,
                        'shippingtaxamount' => $shippingTaxAmount,
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                        'taxed' => $tax,
                        'id' => $taxRate->getID(),
                    ];
                }
            }
        }

        return $taxes;
    }

    public static function getTaxForProduct($cartItem)
    {
        $product = StoreProduct::getByID($cartItem['product']['pID']);

        if ($cartItem['product']['variation']) {
            $product->shallowClone = true;
            $product = clone $product;
            $product->setVariation($cartItem['product']['variation']);
        }

        $qty = $cartItem['product']['qty'];
        $taxRates = self::getTaxRates();
        $taxes = [];
        if (count($taxRates) > 0) {
            foreach ($taxRates as $taxRate) {
                if ($taxRate->isTaxable()) {
                    $taxAmount = $taxRate->calculateProduct($product, $qty);
                    $taxes[] = [
                        'name' => $taxRate->getTaxLabel(),
                        'taxamount' => $taxAmount,
                        'based' => $taxRate->getTaxBasedOn(),
                    ];
                }
            }
        }

        return $taxes;
    }
}
