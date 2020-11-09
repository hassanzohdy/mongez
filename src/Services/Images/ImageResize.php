<?php
namespace HZ\Illuminate\Mongez\Services\Images;

Use File;
use Image;

class ImageResize {

    /**
     * Image path
     *
     * @var string
     */
    protected $imagePath;

    /**
     * Image name
     *
     * @var string
     */
    protected $imageName;

    /**
     * Path to image folder
     *
     * @var string
     */
    protected $pathToImageFolder;

    /**
     * Image extension
     *
     * @var string
     */
    protected $imageExtension;

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
     * Constructor
     *
     * @param string $imagePath
     */
    public function __construct($imagePath)
    {
        $this->imagePath = public_path() .$imagePath;
        $this->imageExtension = pathinfo($this->imagePath, PATHINFO_EXTENSION);
        $this->imageName = str_replace('.' .$this->imageExtension, '', basename($imagePath));
        $this->pathToImageFolder = str_replace(basename($imagePath), '' ,$imagePath);
    }

    /**
     * Resize given image to specific dimensions
     *
     * @param int $width
     * @param int $height
     * @return string
     */
    public function resize($width, $height)
    {
        $this->width = $width;
        $this->height = $height;
        if (! $this->imageHasResized()) {
            $resizedImage = Image::make($this->imagePath)->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            })->encode($this->imageExtension);
            File::put(public_path() .$this->pathToImageFolder .$this->resizedImageName, $resizedImage->__toString());
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
        return file_exists(public_path() .$this->pathToImageFolder .$this->resizedImageName);
    }
}
