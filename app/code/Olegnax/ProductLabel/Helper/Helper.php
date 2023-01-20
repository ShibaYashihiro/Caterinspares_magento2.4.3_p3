<?php

/**
 * Olegnax ProductLabel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_ProductLabel
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductLabel\Helper;

use DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory;
use Olegnax\Core\Helper\Helper as CoreHelperHelper;

class Helper extends CoreHelperHelper
{
    const CONFIG_MODULE = 'olegnax_productlabel';

    protected $dateTime;
    protected $reportBestsellers;
    private $_bestsellerIds;
    private $storeCode;

    public function __call($method, $args)
    {
        if (preg_match('/^showLabel(.+)$/', $method, $matches)) {
            $label = strtolower($matches[1]);
            $product = isset($args[0]) ? $args[0] : null;
            return $this->getLabel($product, $label);
        }
        if (preg_match('/^isEnabledLabel(.+)$/', $method, $matches)) {
            $label = strtolower($matches[1]);
            return $this->isEnabledLabel($label);
        }

        throw new LocalizedException(
            new Phrase('Invalid method %1::%2', [get_class($this), $method])
        );
    }

    public function getLabel($product, $label)
    {
        if (!$this->isEnabledLabel($label) || !$this->isCondition($product, $label)) {
            return '';
        }

        $style = $this->prepareStyle([
            'color' => $this->getLabelAttr($label, 'color'),
            'background-color' => $this->getLabelAttr($label, 'background_color')
        ]);
        if (!empty($style)) {
            $style = ' style="' . $style . '"';
        }

        $text = $this->getLabelAttr($label, 'text');
        if (false !== strpos($text, '{{')) {
            $text = $this->prepareText($text, $product);
        }
        $text = trim($text);

        if (empty($text)) {
            return '';
        }

        return '<span class="ox-product-label-' . $label . '"' . $style . '>' . $text . '</span>';
    }

    public function isEnabledLabel($label)
    {
        return (bool)$this->isEnabled() && $this->getModuleConfig('label/' . $label);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getModuleConfig('general/enable');
    }

    public function getModuleConfig($path = '', $storeCode = null)
    {
        return parent::getModuleConfig($path, $this->getStoreId());
    }

    private function getStoreId()
    {
        if (empty($this->storeCode)) {
            $this->storeCode = $this->getStore()->getId();
        }
        return $this->storeCode;
    }

    protected function isCondition($product, $label)
    {
        $method = 'isCondition' . ucfirst($label);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $product);
        }

        return false;
    }

    /**
     * @param array $style
     * @param string $separatorValue
     * @param string $separatorAttribute
     * @return string
     */
    public function prepareStyle(array $style, $separatorValue = ': ', $separatorAttribute = ';')
    {
        $style = array_filter($style);
        if (empty($style)) {
            return '';
        }
        foreach ($style as $key => &$value) {
            $value = $key . $separatorValue . $value;
        }
        $style = implode($separatorAttribute, $style);

        return $style;
    }

    protected function getLabelAttr($label, $attribute = '')
    {
        return $this->getModuleConfig(sprintf('label/%s_%s', $label, $attribute));
    }

    protected function prepareText($text, $product)
    {
        $search = [];
        $replace = [];

        $templates = $this->templateReplace($product);
        $index = 0;
        foreach ($templates as $key => $value) {
            $search[$index] = '{{' . $key . '}}';
            $replace[$index] = $value;
            $index++;
        }

        if (preg_match_all('/\{\{attribute\:([^\:\}]+)\}\}/im', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $matche) {
                $search[$index] = $matche[0];
                $replace[$index] = $product->getData($matche[1]);
                $index++;
            }
        }

        return str_replace($search, $replace, $text);
    }

    public function templateReplace($product)
    {
        $discount = '';
        $discount_percent = '';
        $special_price = '';

        $productType = $product->getTypeID();
        switch ($productType) {
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                $_discount = [];
                $_discount_percent = [];
                $_special_price = [];
                foreach ($childs as $child) {
                    $specialPrice = $child->getSpecialPrice();
                    if (!empty($specialPrice)) {
                        $_special_price[] = $specialPrice;
                        $_discount[] = $child->getPrice() - $specialPrice;
                        $_discount_percent[] = round((1 - $specialPrice / $child->getPrice()) * 100);
                    }
                }
                if (1 == count($_discount)) {
                    $discount = array_shift($_discount);
                    $discount = $this->convertValute($discount);
                    $discount_percent = array_shift($_discount_percent) . '%';
                    $special_price = array_shift($_special_price);
                } elseif (1 < count($_discount)) {
                    $minDiscount = min($_discount);
                    $maxDiscount = max($_discount);
                    $minDiscount = $this->convertValute($minDiscount);
                    $maxDiscount = $this->convertValute($maxDiscount);
                    if ($minDiscount !== $maxDiscount) {
                        $discount = $minDiscount . '-' . $maxDiscount;
                    } else {
                        $discount = $maxDiscount;
                    }
                    $minDiscount = min($_discount_percent);
                    $maxDiscount = max($_discount_percent);
                    if ($minDiscount !== $maxDiscount) {
                        $discount_percent = min($_discount_percent) . '%-' . max($_discount_percent) . '%';
                    } else {
                        $discount_percent = max($_discount_percent) . '%';
                    }
                    $special_price = min($_special_price);
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                $_discount = [];
                $_discount_percent = [];
                $_special_price = [];
                foreach ($childs as $child) {
                    $specialPrice = $child->getSpecialPrice();
                    if (!empty($specialPrice)) {
                        $_special_price[] = $specialPrice;
                        $_discount[] = $child->getPrice() - $specialPrice;
                        $_discount_percent[] = round((1 - $specialPrice / $child->getPrice()) * 100);
                    }
                }
                if (1 == count($_discount)) {
                    $discount = array_shift($_discount);
                    $discount = $this->convertValute($discount);
                    $discount_percent = array_shift($_discount_percent) . '%';
                    $special_price = array_shift($_special_price);
                } elseif (1 < count($_discount)) {
                    $minDiscount = min($_discount);
                    $maxDiscount = max($_discount);
                    $minDiscount = $this->convertValute($minDiscount);
                    $maxDiscount = $this->convertValute($maxDiscount);
                    if ($minDiscount !== $maxDiscount) {
                        $discount = $minDiscount . '-' . $maxDiscount;
                    } else {
                        $discount = $maxDiscount;
                    }
                    $minDiscount = min($_discount_percent);
                    $maxDiscount = max($_discount_percent);
                    if ($minDiscount !== $maxDiscount) {
                        $discount_percent = $minDiscount . '%-' . $maxDiscount . '%';
                    } else {
                        $discount_percent = $maxDiscount . '%';
                    }
                    $special_price = min($_special_price);
                }
                break;
            case 'bundle':
                $specialPrice = $product->getSpecialPrice();
                if (!empty($specialPrice)) {
                    $discount_percent = round($specialPrice) . '%';
                }
                break;
            case 'simple':
            default:
                $specialPrice = $product->getSpecialPrice();
                if (!empty($specialPrice)) {
                    $special_price = $specialPrice;
                    $discount = $product->getPrice() - $specialPrice;
                    $discount = $this->convertValute($discount);

                    if ($product->getPrice() > 0) {
                        $discount_percent = round((1 - $specialPrice / $product->getPrice()) * 100) . '%';
                    }
                }
        }
        return [
            'discount' => $discount,
            'discount_percent' => $discount_percent,
            'special_price' => $this->convertValute($special_price),
        ];
    }

    private function convertValute($value)
    {
        $value = floatval($value);
        $value = number_format($value, 2, ',', ' ');
        return $value;
    }

    public function isConditionNew($product)
    {
        $nowStart = $this->getDate();
        $nowEnd = $this->getDate('23:59:59');
        $productFrom = $product->getNewsFromDate();
        $productTo = $product->getNewsToDate();

        if (!empty($productFrom) && !empty($productTo)) {
            return ($nowEnd >= $productFrom && $nowStart <= $productTo);
        } elseif (!empty($productFrom) && empty($productTo)) {
            return ($nowEnd >= $productFrom);
        } elseif (empty($productFrom) && !empty($productTo)) {
            return ($nowStart <= $productTo);
        }

        $productType = $product->getTypeID();
        switch ($productType) {
            case 'bundle':
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionNew($child)) {
                        return true;
                    }
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionNew($child)) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    protected function getDate($time = '0:0:0')
    {
        return $this->getDateTime()->date(null, $time);
    }

    protected function getDateTime()
    {
        if (!$this->dateTime) {
            $this->dateTime = $this->_loadObject(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        }

        return $this->dateTime;
    }

    public function isConditionSale($product, $isChild = false)
    {
        $specialPrice = $product->getSpecialPrice();
        $productType = $product->getTypeID();
        if ((!in_array($productType, ['grouped', 'configurable']) || $isChild) && empty($specialPrice)) {
            return false;
        }
        $nowStart = $this->getDate();
        $nowEnd = $this->getDate('23:59:59');
        $productFrom = $product->getSpecialFromDate();
        $productTo = $product->getSpecialToDate();
        if (!empty($productFrom) && !empty($productTo)) {
            return ($nowEnd >= $productFrom && $nowStart <= $productTo);
        } elseif (!empty($productFrom) && empty($productTo)) {
            return ($nowEnd >= $productFrom);
        } elseif (empty($productFrom) && !empty($productTo)) {
            return ($nowStart <= $productTo);
        }

        switch ($productType) {
            case 'grouped':
                $childs = $product->getTypeInstance()->getAssociatedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionSale($child, true)) {
                        return true;
                    }
                }
                break;
            case 'configurable':
                $childs = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childs as $child) {
                    if ($this->isConditionSale($child, true)) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    public function isConditionFeatured($product)
    {
        return (bool)$product->getData('ox_featured') || $product->getData('thm_featured');
    }

    public function isConditionCustom()
    {
        return true;
    }

    public function isConditionBestseller($product)
    {
        return in_array($product->getId(), $this->getBestsellerIds());
    }

    protected function getBestsellerIds()
    {
        if (empty($this->_bestsellerIds)) {

            $this->_bestsellerIds = [];
            $collection = $this->getReportBestsellers()->create()->setPeriod('year');
            foreach ($collection as $item) {
                $this->_bestsellerIds[] = $item->getProductId();
            }
        }

        return $this->_bestsellerIds;
    }

    protected function getReportBestsellers()
    {
        if (!$this->reportBestsellers) {
            $this->reportBestsellers = $this->_loadObject(CollectionFactory::class);
        }

        return $this->reportBestsellers;
    }

    public function showLabelsProduct()
    {
        $labelsPosition = 'ox-product-labels--' . $this->getModuleConfig('label/label_position_product');
        $product = $this->getProduct();
        if ($product) {
            return $this->showLabels($product, $labelsPosition);
        }

        return '';
    }

    protected function getProduct()
    {
        $register = $this->_loadObject(Registry::class);
        $product = $register->registry('product');
        if ($product) {
            return $product;
        }
        $product = $register->registry('current_product');
        if ($product) {
            return $product;
        }

        return null;
    }

    public function showLabels($product, $labelsPosition = null)
    {
        if (!$this->isEnabled()) {
            return '';
        }
        $_labelsPosition = $labelsPosition;
        $html = $this->getCacheHtml($product, $_labelsPosition);
        if (!empty($html)) {
            return $html;
        }
        $labels = [];
        foreach (['new', 'sale', 'featured', 'bestseller', 'custom'] as $label) {
            $labels[] = $this->getLabel($product, $label);
        }
        if (empty($labelsPosition)) {
            $labelsPosition = 'ox-product-labels--' . $this->getModuleConfig('label/label_position');
        }
        $labelsOutput = implode('', $labels);
        if ($labelsOutput) {
            $labelsOutput = '<div class="ox-product-labels-wrapper ' . $labelsPosition . '">' . $labelsOutput . '</div>';
        }
        $this->setCacheHtml($labelsOutput, $product, $_labelsPosition);

        return $labelsOutput;
    }

    protected function getCacheHtml($product, $labelsPosition)
    {
        /** @var Cache $cache */
        $cache = $this->_loadObject(Cache::class);
        $cache = $cache->load($cache->getId('showLabels', [$product->getId(), $labelsPosition, $this->getStoreId()]));
        return empty($cache) ? "" : $cache;
    }

    protected function setCacheHtml($html, $product, $labelsPosition)
    {
        /** @var Cache $cache */
        $cache = $this->_loadObject(Cache::class);
        return $cache->save($html, $cache->getId('showLabels', [$product->getId(), $labelsPosition, $this->getStoreId()]));
    }

    protected function convDate($date)
    {
        return DateTime::createFromFormat('Y-m-d H:i:s', $date);
    }

}
