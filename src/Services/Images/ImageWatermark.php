<?php
namespace HZ\Illuminate\Mongez\Services\Images;

Use File;
use Image;

class ImageWatermark extends BaseImage {


    /**
     * Watermark image path
     *
     * @var string
     */
    protected $watermarkImagePath;

    /**
     * {@inheritDoc}
     */
    public function __construct($imagePath)
    {
        parent::__construct($imagePath);
        $this->watermarkImagePath = public_path($this->pathToImageFolder .$this->imageName .'-watermark.' .$this->imageExtension);
    }

    /**
     * Set Watermark
     *
     * @param string $watermarkImagePath
     * @param string $position
     * @param int    $xAxis
     * @param int    $yAxis
     * @return string
     */
    public function setWatermark($watermarkImagePath, $position, $xAxis = 0 ,$yAxis = 0)
    {
        if (! $this->imageHasWatermark()) {
            $watermarkImage = $this->getImageObject(public_path($watermarkImagePath));
            $imageWithWatermark = $this->imageObject->insert(
                $watermarkImage,
                $position,
                $xAxis,
                $yAxis
            );
            $imageWithWatermark->save($this->watermarkImagePath);
        }
        return $this->watermarkImagePath;
    }

    /**
     * Check if watermark image has been existed
     *
     * @param string $waterImagePath
     * @return bool
     */
    protected function imageHasWatermark()
    {
        return file_exists($this->watermarkImagePath);
    }
}
