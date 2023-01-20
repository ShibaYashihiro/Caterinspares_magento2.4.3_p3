<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mageants\Product360Image\Model\Source\Import;

/**
 * Source import entity model
 *
 * @api
 * @since 100.0.2
 */
class Entity implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\ImportExport\Model\Import\ConfigInterface
     */
    public $importConfig;

    /**
     * @param \Magento\ImportExport\Model\Import\ConfigInterface $importConfig
     */
    public function __construct(\Magento\ImportExport\Model\Import\ConfigInterface $importConfig)
    {
        $this->importConfig = $importConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        $options[] = ['label' => __('-- Please Select --'), 'value' => ''];
        $options[] = ['label' => 'Prduct360', 'value' => 'prduct360'];
        foreach ($this->importConfig->getEntities() as $entityName => $entityConfig) {
            $options[] = ['label' => __($entityConfig['label']), 'value' => $entityName];
        }
        return $options;
    }
}
