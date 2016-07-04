<?php

class ImageUtils {

    public static function saveImage($source_path, $dest_path) {
        list( $source_width, $source_height, $source_type ) = getimagesize($source_path);
        switch ($source_type) {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_path);
                imagegif($source_gdim, $dest_path);
                break;

            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_path);
                imagejpeg($source_gdim, $dest_path, 90);
                break;

            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_path);
                imagealphablending($source_gdim, false);
                imagesavealpha($source_gdim, true);
                imagepng($source_gdim, $dest_path);
                break;
        }

        imagedestroy($source_gdim);
    }

    public static function imageGrayscale($source_path, $dest_path) {
        list( $source_width, $source_height, $source_type ) = getimagesize($source_path);
        switch ($source_type) {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_path);
                imagefilter($source_gdim, IMG_FILTER_GRAYSCALE);
                imagegif($source_gdim, $dest_path);
                break;

            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_path);
                imagefilter($source_gdim, IMG_FILTER_GRAYSCALE);
                imagejpeg($source_gdim, $dest_path, 90);
                break;

            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_path);
                imagealphablending($source_gdim, false);
                imagesavealpha($source_gdim, true);
                imagefilter($source_gdim, IMG_FILTER_GRAYSCALE);
                imagepng($source_gdim, $dest_path);
                break;
        }

        imagedestroy($source_gdim);
    }

    /**
     *
     * @param int $nw
     * @param int $nh
     * @param string $source_path
     * @param string $dest_path
     * @param int $type
     */
    public static function resizeProportionalImage($nw, $nh, $source_path, $dest_path, $minimum_size = 1, $crop = 0) {

        $icc_prof = null;
        // Add file validation code here
        list( $source_width, $source_height, $source_type ) = getimagesize($source_path);

        switch ($source_type) {
            case IMAGETYPE_GIF:
                $source_gdim = imagecreatefromgif($source_path);
                break;

            case IMAGETYPE_JPEG:
                $source_gdim = imagecreatefromjpeg($source_path);
                $icc_prof = new JPEG_ICC();
                $icc_prof->LoadFromJPEG($source_path);

                if ($icc_prof->GetProfile() == null)
                    $icc_prof = null;
                break;

            case IMAGETYPE_PNG:
                $source_gdim = imagecreatefrompng($source_path);
                imagealphablending($source_gdim, true);

                break;
        }

        $source_aspect_ratio = $source_width / $source_height;
        $desired_aspect_ratio = $nw / $nh;

        if ($minimum_size == 1) {
            if ($source_aspect_ratio > $desired_aspect_ratio) {
                // Triggered when source image is wider
                $temp_height = $nh;
                $temp_width = (int) ( $nh * $source_aspect_ratio );
            } else {
                // Triggered otherwise (i.e. source image is similar or taller)
                $temp_width = $nw;
                $temp_height = (int) ( $nw / $source_aspect_ratio );
            }
        } else {
            if ($source_aspect_ratio < $desired_aspect_ratio) {
                // Triggered when source image is wider
                $temp_height = $nh;
                $temp_width = (int) ( $nh * $source_aspect_ratio );
            } else {
                // Triggered otherwise (i.e. source image is similar or taller)
                $temp_width = $nw;
                $temp_height = (int) ( $nw / $source_aspect_ratio );
            }
        }

        $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
        if ($source_type == IMAGETYPE_PNG) {
            //imagealphablending($temp_gdim, false);
            //imagesavealpha( $temp_gdim, true );
            $backgroundColor = imagecolorallocate($temp_gdim, 255, 255, 255);
            imagefill($temp_gdim, 0, 0, $backgroundColor);
        }

        imagecopyresampled($temp_gdim, $source_gdim, 0, 0, 0, 0, $temp_width, $temp_height, $source_width, $source_height);


        if ($crop) {
            $x0 = ( $temp_width - $nw ) / 2;
            $y0 = ( $temp_height - $nh ) / 2;
            $desired_gdim = imagecreatetruecolor($nw, $nh);
            imagecopy($desired_gdim, $temp_gdim, 0, 0, $x0, $y0, $nw, $nh);
        } else {
            $desired_gdim = $temp_gdim;
        }

        // Render the image
        switch ($source_type) {
            case IMAGETYPE_GIF:
                imagegif($desired_gdim, $dest_path);
                break;

            case IMAGETYPE_JPEG:
                imageinterlace($desired_gdim, true);
                imagejpeg($desired_gdim, $dest_path, 90);

                if ($icc_prof != null)
                    $icc_prof->SaveToJPEG($dest_path);

                break;

            case IMAGETYPE_PNG:
                imagepng($desired_gdim, $dest_path);
                break;
        }

        // Add clean-up code here
        imagedestroy($desired_gdim);
        return true;
    }

}

