<?php
namespace HZ\Illuminate\Mongez\Services\Images;

Use File;
use Image;

class ImageResize extends BaseImage {

    /**
     * Width
     *
     * @var int
     */
    protected $width;

    /**
     * Height
     *
     * @var int
     */
    protected $height;

    /**
     * Resized Image name
     *
     * @var string
     */
    protected $resizedImageName;

    /**
     * Resize given image to specific dimensions
     *
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resize($width, $height, $quality = 100)
    {
        $this->width = $width;
        $this->height = $height;
        if (! $this->imageHasResized()) {
            $resizedImage = $this->imageObject->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->encode($this->imageExtension, $quality);
            File::put(public_path('/' .$this->pathToImageFolder .$this->resizedImageName), $resizedImage->__toString());
        }
        return $this->pathToImageFolder .'/' .$this->resizedImageName;
    }

    /**
     * Check if this image has been resized before.
     *
     * @return bool
     */
    protected function imageHasResized()
    {
        $this->resizedImageName = $this->imageName .'-' .$this->width * $this->height .'.' .$this->imageExtension;
        return file_exists(public_path('/' .$this->pathToImageFolder .$this->resizedImageName));
    }
}
