<?php

namespace fashop\utils\verifycode;

class Result
{
    private $imageBody;
    private $imageStr;

    function __construct($image, $str)
    {
        $this->imageBody = $image;
        $this->imageStr = $str;
    }

    /**
     * @return mixed
     */
    public function getImageBody()
    {
        return $this->imageBody;
    }

    /**
     * @return mixed
     */
    public function getImageStr()
    {
        return $this->imageStr;
    }

    function getMineType()
    {
        return 'image/png';
    }
}