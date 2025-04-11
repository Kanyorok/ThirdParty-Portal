<?php

namespace App\Services\BR;

use App\Models\BR\ImageAccount;
use Exception;

class ImageService
{
    public function __construct(public ImageAccount $image)
    {
    }

    public function get_image(string $attributes = '', bool $placeholder = true): string
    {
        $str = $this->get_url_string();
        if (!empty($str)) {
            return '<img src="data:image/png;base64,' . $str . '" ' . $attributes . '>';
        }

        //API
        $str = (new CBSService())->getClientImage($this->image->ClientID, $this->image->ImageTypeID);
        if (!empty($str)) {
            return '<img src="data:image/png;base64,' . str_replace('"', '', $str) . '" ' . $attributes . '>';
        }

        return ($placeholder)
            ? '<img src="https://placehold.co/200x200?font=roboto&text=Not+Found" ' . $attributes . '/>'
            : '';
    }

    public function get_url_string(): string
    {
        try {
            return base64_encode($this->_deserializeDotNetString(gzinflate(base64_decode($this->image->sImage))));
        } catch (Exception $e) {
            //Log::critical($e);
        }
        return '';
    }

    /**
     * This is a magic function do not touch it.
     * @throws Exception
     */
    private function _deserializeDotNetString(string $ser): string
    {
        $pos = 154;
        if ($ser[$pos] != "\xA0") {
            throw new Exception('Bytes16Text record not found');
        }
        $a = unpack('vlen', substr($ser, $pos + 1, 2));
        $len = $a['len'];
        $data = substr($ser, $pos + 3, $len);

        $pos += 3 + $len;
        if ($ser[$pos] != "\x9F") {
            throw new Exception('Bytes8TextWithEndElement record not found');
        }
        $a = unpack('Clen', $ser[$pos + 1]);
        $len = $a['len'];
        $data .= substr($ser, $pos + 2, $len);

        return $data;
    }
}
