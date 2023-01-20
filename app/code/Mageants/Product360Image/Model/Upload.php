<?php
 /**
  * @category Mageants Product360Image
  * @package Mageants_Product360Image
  * @copyright Copyright (c) 2017 Mageants
  * @author Mageants Team <support@Mageants.com>
  */
namespace Mageants\Product360Image\Model;

use \Magento\MediaStorage\Model\File\UploaderFactory;
        
class Upload
{
    /**
     * Upload model factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    public $uploaderFactory;

    /**
     * constructor
     *
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        UploaderFactory $uploaderFactory
    ) {
        $this->uploaderFactory = $uploaderFactory;
    }

    /**
     * upload file
     *
     * @param $input
     * @param $destinationFolder
     * @param $data
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadFileAndGetName($input, $destinationFolder, $data)
    {
        try {
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                
                $uploader->setAllowRenameFiles(true);
                
                $uploader->setFilesDispersion(false);
                
                $uploader->setAllowCreateFolders(true);
                
                $result = $uploader->save($destinationFolder);
                
                return $result;
            }
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new \Magento\Framework\Exception\LocalizedException($e->getMessage());
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        
        return '';
    }
}
