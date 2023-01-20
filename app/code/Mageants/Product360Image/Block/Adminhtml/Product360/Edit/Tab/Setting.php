<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author    Mageants Team <info@mageants.com>
  */
namespace Mageants\Product360Image\Block\Adminhtml\Product360\Edit\Tab;

use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Data\FormFactory;
use \Magento\Config\Model\Config\Source\Yesno;
use \Mageants\Product360Image\Helper\Data;
use \Mageants\Product360Image\Model\Source\ImageMode;
use \Mageants\Product360Image\Model\Source\Status;
use \Mageants\Product360Image\Model\ResourceModel\Image;
use \Magento\Backend\Block\Widget\Tab\TabInterface as TabInterface;

class Setting extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Mageants\Product360Image\Model\Source\ImageMode
     *
     */
    public $yesNo;
    
    /**
     * @var \Mageants\Product360Image\Model\Source\ImageMode
     *
     */
    public $imageMode;
    
    /**
     * @var \Mageants\Product360Image\Model\ResourceModel\Image
     *
     */
    public $imageModel;
    
    /**
     * Default Helper options
     *
     */
    public $helper;
    
    /**
     * constructor
     *
     * @param  Context $context,
     * @param  Registry $registry,
     * @param  FormFactory $formFactory,
     * @param  Yesno $yesNo,
     * @param  Image $imageModel,,
     * @param  Data $helper,
     * @param  array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Status $status,
        ImageMode $imageMode,
        Image $imageModel,
        Data $helper,
        array $data = []
    ) {
        $this->_status                   = $status;
        
        $this->imageMode            = $imageMode;
        
        $this->imageModel           = $imageModel;
        
        $this->helper                   = $helper;
        
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $product_id = $this->getRequest()->getParam('productid');
        $id = $this->getRequest()->getParam('id');
        
        /** @var \Mageants\Product360Image\Model\Product360 $product360 */
        $product360 = $this->_coreRegistry->registry('mageants_product360image');
        
        $form = $this->_formFactory->create();
        
        $form->setHtmlIdPrefix('product360setting_');
        $form->setFieldNameSuffix('product360setting');
        
         $fieldset = $form->addFieldset(
             'base_fieldset',
             [
                'legend' => __('Product360 Setting'),
                'class'  => 'fieldset-wide'
             ]
         );
        
        if ($id) {
            $fieldset->addField(
                'id',
                'hidden',
                [
                    'name'      => 'setting[id]',
                    'value' => $id
                ]
            );
        }
         
        $fieldset->addField(
            'product_id',
            'hidden',
            [
                'name'      => 'setting[product_id]',
                'value' => $product_id
            ]
        );
         
        $fieldset->addField(
            'height',
            'text',
            [
                'name'  => 'setting[height]',
                'label' => __('Height'),
                'title' => __('Height'),
                "class"=>"validate-digits",
                 'required' => true,
                'note' => " Actual display height of 360 view"
            ]
        );
         
        $fieldset->addField(
            'width',
            'text',
            [
                'name'  => 'setting[width]',
                'label' => __('Width'),
                'title' => __('Width'),
                "class"=>"validate-digits",
                 'required' => true,
                'note' => "Actual display width of 360 view"
            ]
        );
        $fieldset->addField(
            'frameTime',
            'text',
            [
                'name'  => 'setting[frameTime]',
                'label' => __('Autoplay Speed '),
                'title' => __('Autoplay Speed'),
                "class"=>"validate-digits",
                 'required' => true,
                'note' => "Time in ms between updates. 40 is exactly 25 FPS"
            ]
        );
        
        $fieldset->addField(
            'enable_fullscreen',
            'select',
            [
                'name'  => 'setting[enable_fullscreen]',
                'label' => __('Fulscreen '),
                'title' => __('Fulscreen '),
                'required' => true,
                'values' => $this->_status->toOptionArray(),
                'note' => 'Fullscreen on/off'
            ]
        );
        
        $fieldset->addField(
            'enable_ease_blur',
            'select',
            [
                'name'  => 'setting[enable_ease_blur]',
                'label' => __('Ease And Blur'),
                'title' => __('Ease And Blur'),
                'required' => true,
                'values' => $this->_status->toOptionArray(),
                'note' => 'Enable / Disable ease and blur effect'
            ]
        );
        
        $fieldset->addField(
            'images_mode',
            'select',
            [
                'name'  => 'setting[images_mode]',
                'label' => __('Images Mode'),
                'title' => __('Images Mode'),
                'values' => $this->imageMode->toOptionArray(),
                'note' => " multi image  &nbsp;: You need to upload 
                multiple sequence image of product<br>single image : 
                You need to upload single image which contains row 
                of product images.<br>Ex.<br><br><img src=
                '".$this->imageModel->getSheetExampleImage()."' height='200' height='400'>.
								"
            ]
        );
         
        $defaultConfig = $this->helper->getDefaultSetting();
        
        $product360->addData($defaultConfig['setting']);
        
        $product360Data = $this->_session->getData('mageants_product360image_product360_data', true);
        
        if ($product360Data) {
            $product360->addData($product360Data);
        } else {
            if ($product360->getId()) {
                $settingData = $this->helper->unserializeSetting($product360->getSetting());
                
                $product360->addData($settingData['setting']);
            }
        }
        
        $formData = $product360->getData();
        
        $form->addValues($formData);
        
        $form = $this->addImageModeFieldset($form, $formData);
        
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    /**
     * Add Image Mode Fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @param array $formData
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addImageModeFieldset($form, $formData)
    {
        $imageModeFieldset = $form->addFieldset('image_mode_fieldset', []);
        
        $imageModeFieldset->addField(
            'frames',
            'text',
            [
                'name'  => 'setting[frames]',
                'label' => __('Number Of Frames'),
                'title' => __('Number Of Frames'),
                'value'=>$formData['frames'],
                'class'=>"validate-digits",
                'note'  => 'Total Number Of frame in single sheet'
            ]
        );
        
        $imageModeFieldset->addField(
            'framesX',
            'text',
            [
                'name'  => 'setting[framesX]',
                'label' => __('Number Of Frame in Row'),
                'title' => __('Number Of Frame in Row'),
                'after_element_js' => $this->getImageModeJs(),
                'value'=>$formData['framesX'],
                'class'=>"validate-digits",
                'note'  => 'Total Number Of frame in single row'
            ]
        );
 
        return $form;
    }
    
    /**
     * Retrive js code for CategoryIds input field
     *
     * @return string
     */
    private function getImageModeJs()
    {
        return <<<HTML
    <script type="text/javascript">
		require(['jquery'],function($){
			jQuery("#product360setting_images_mode").on('change',function(){
				var val = jQuery(this).val();
				if(val == 1)
				{
					jQuery("#product360setting_image_mode_fieldset").show()
				}
				else
				{
					jQuery("#product360setting_image_mode_fieldset").hide()
				}
				
			})
			jQuery("#product360setting_images_mode").change()
		})
		
    </script>
HTML;
    }
    /**
     * Prepare Product360 for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Setting');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
