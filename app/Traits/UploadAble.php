<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Trait UploadAble
 * @package App\Traits
 */
trait UploadAble
{
    /**
     * @param UploadedFile $file
     * @param null $folder
     * @param string $disk
     * @param null $filename
     * @return false|string
     */
    public function uploadOne(UploadedFile $file, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);
        /*return $file->storeAs(
            $folder,
            $name . "." . $file->getClientOriginalExtension(),
            $disk
        );*/
        $file_path = null;
        \File::makeDirectory(public_path($folder),0777, true, true);
        $extension = $file->getClientOriginalExtension() != '' ? $file->getClientOriginalExtension() : $file->guessExtension();
        $name = $name . "." . $extension;

        $file_path = str_replace('storage/','',$folder).'/'.$name;

        if(!$file->move(public_path($folder),$name))
            $file_path = null;

        return $file_path;
    }

    /**
     * @param null $path
     * @param string $disk
     */
    public function deleteOne($path = null, $disk = 'public')
    {
        Storage::disk($disk)->delete($path);
    }
}
