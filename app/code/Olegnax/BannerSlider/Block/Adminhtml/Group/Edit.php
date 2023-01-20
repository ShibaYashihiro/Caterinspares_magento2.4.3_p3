<?php

namespace Olegnax\BannerSlider\Block\Adminhtml\Group;

class Edit extends \Magento\Backend\Block\Widget\Form\Container {

	protected $_coreRegistry = null;

	public function __construct(
	\Magento\Framework\Registry $registry, \Magento\Backend\Block\Widget\Context $context, array $data = []
	) {
		$this->_coreRegistry = $registry;
		parent::__construct($context, $data);
	}

	protected function _construct() {
		$this->_blockGroup = 'Olegnax_BannerSlider';
		$this->_controller = 'adminhtml_group';
		parent::_construct();
		if ($this->_isAllowedAction('Olegnax_BannerSlider::Group')) {
			$this->buttonList->remove('reset');
			$this->buttonList->update('save', 'label', __('Save Group'));
			$this->buttonList->add(
					'saveandcontinue',
					[
				'label' => __('Save and Continue Edit'),
				'class' => 'save',
				'data_attribute' => [
					'mage-init' => [
						'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
					],
				]
					], -100
			);
			$this->buttonList->update('delete', 'label', __('Delete Group'));
		} else {
			$this->buttonList->remove('save');
			$this->buttonList->remove('delete');
		}
	}

	protected function _isAllowedAction($resourceId) {
		return $this->_authorization->isAllowed($resourceId);
	}

	public function getHeaderText() {
		$id = $this->_coreRegistry->registry('olegnax_bannerslider_group')->getId();
		if ($id) {
			return __("Edit Group '%1'", $this->escapeHtml($id));
		} else {
			return __('New Group');
		}
	}

	protected function _toHtml() {
		$this->_eventManager->dispatch('adminhtml_block_html_before',
				['block' => $this]);
		return parent::_toHtml();
	}

}
