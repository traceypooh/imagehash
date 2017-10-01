<?

class ImageHash
{
    const SIZE = 8;
    public function hasher($resource)
    {
        // For this implementation we create a 8x9 image.
        $width = static::SIZE + 1;
        $heigth = static::SIZE;

        // Resize the image.
        $resized = imagecreatetruecolor($width, $heigth);
        imagecopyresampled($resized, $resource, 0, 0, 0, 0, $width, $heigth, imagesx($resource), imagesy($resource));

        $hash = 0;
        $one = 1;
        for ($y = 0; $y < $heigth; $y++) {
            // Get the pixel value for the leftmost pixel.
            $rgb = imagecolorsforindex($resized, imagecolorat($resized, 0, $y));
            $left = floor(($rgb['red'] + $rgb['green'] + $rgb['blue']) / 3);

            for ($x = 1; $x < $width; $x++) {
                // Get the pixel value for each pixel starting from position 1.
                $rgb = imagecolorsforindex($resized, imagecolorat($resized, $x, $y));
                $right = floor(($rgb['red'] + $rgb['green'] + $rgb['blue']) / 3);

                // Each hash bit is set based on whether the left pixel is brighter than the right pixel.
                // http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
                if ($left > $right) {
                    $hash |= $one;
                }

                // Prepare the next loop.
                $left = $right;
                $one = $one << 1;
            }
        }

        // Free up memory.
        imagedestroy($resized);

        return $hash;
    }


    /**
     * Calculate a perceptual hash of an image file.
     *
     * @param  mixed $resource GD2 resource or filename
     * @return int
     */
    public function hash($resource)
    {
        $destroy = false;

        if (! is_resource($resource)) {
            $resource = imagecreatefromstring(file_get_contents($resource));
            $destroy = true;
        }

        $hash = $this->hasher($resource);

        if ($destroy)
            imagedestroy($resource);

        return $hash;
    }


    /**
     * Calculate the Hamming Distance.
     *
     * @param int $hash1
     * @param int $hash2
     * @return int
     */
    public function distance($hash1, $hash2)
    {
        if (extension_loaded('gmp')) {
            $dh = gmp_hamdist($hash1, $hash2);

        } else {
            $dh = 0;
            for ($i = 0; $i < 64; $i++) {
                $k = (1 << $i);
                if (($hash1 & $k) !== ($hash2 & $k)) {
                    $dh++;
                }
            }
        }

        return $dh;
    }
}
