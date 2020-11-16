<?php
namespace HZ\Illuminate\Mongez\Services\Images;

use Image;

class BaseImage {

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
     * Image Object
     */
    protected $imageObject;

    /**
     * Constructor
     *
     * @param string $imagePath
     */
    public function __construct($imagePath)
    {
        $this->imagePath = public_path() .'/' .$imagePath;
        $this->imageExtension = pathinfo($this->imagePath, PATHINFO_EXTENSION);
        $this->imageName = str_replace('.' .$this->imageExtension, '', basename($imagePath));
        $this->pathToImageFolder = str_replace(basename($imagePath), '' ,$imagePath);
        $this->imageObject = $this->getImageObject($this->imagePath);
    }

    /**
     * Get image object
     *
     * @param string $imagePath
     * @return void
     */
    public function getImageObject($imagePath)
    {
        return Image::make($imagePath);
    }
}
