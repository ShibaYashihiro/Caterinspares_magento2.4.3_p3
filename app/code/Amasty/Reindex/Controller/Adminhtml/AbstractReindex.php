<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reindex
 */


namespace Amasty\Reindex\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractReindex extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magento_Indexer::index';

    /**
     * @var \Symfony\Component\Process\PhpExecutableFinder
     */
    private $phpExecutableFinder;

    /**
     * @var \Magento\Framework\Shell
     */
    private $shell;

    public function __construct(
        \Symfony\Component\Process\PhpExecutableFinder $phpExecutableFinder,
        \Magento\Framework\Shell $shell,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->phpExecutableFinder = $phpExecutableFinder;
        $this->shell = $shell;
    }

    /**
     * @param array $indexers
     */
    protected function run($indexers = [])
    {
        $phpPath = $this->phpExecutableFinder->find() ?: 'php';
        try {
            $this->shell->execute(
                $phpPath . ' %s indexer:reindex' . str_repeat(' %s', count($indexers)) . ' > /dev/null &',
                array_merge(
                    [BP . '/bin/magento'],
                    $indexers
                )
            );
            $this->messageManager->addSuccessMessage(__('Reindex process has been started in the background.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
