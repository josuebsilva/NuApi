<?php

namespace App\Model;
/*
 * NuAPI
 *
 * Copyright 2023 Josué Barbosa
 * https://jedibit.com.br
 * https://stimper.com.br
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class BucketStorage
{
    private $folder;
    private $user;

    public function __construct($user = null, $folder = null)
    {
        $this->user = $user;
        $this->folder = $folder;
    }

    public function setFolder($folder) {
        $this->folder = $folder;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getUser() {
        return $this->user;
    }

    public function getFolder() {
        return $this->folder;
    }

    public function storage($file, $role, $folder = null, $crop = null) {
        if($folder)
            $this->folder = $folder;

        $fileExtension = $file->getClientOriginalExtension();
        $fileName      = $file->getClientOriginalName();
        $fileName      = str_replace('.'.$fileExtension, '', $fileName);
        $fileData      = $file->get();
        $fileSize      = $file->getSize();
        $path          = "/bucket/{$this->user->uuid}/{$role}/{$folder}/";
        $fileName      = "{$fileName}.{$fileExtension}";

        if(Storage::put($path.$fileName, $fileData)){

            if($crop) {
                $cropPath = storage_path() .'/app'.$path.$fileName;
                $dst_image = imagecreatetruecolor($crop['w'], $crop['h']);

                switch($fileExtension){
                    case 'jpg':
                        $src_image = imagecreatefromjpeg($cropPath);
                        break;
                    case 'jpeg':
                        $src_image = imagecreatefromjpeg($cropPath);
                        break;
                    case 'png':
                        $src_image = imagecreatefrompng($cropPath);
                        break;
                    case 'gif':
                        $src_image = imagecreatefromgif($cropPath);
                        break;
                }

                imagecopyresampled($dst_image, $src_image, 0, 0, $crop['x'], $crop['y'], $crop['w'], $crop['h'], $crop['w'], $crop['h']);
                switch($fileExtension){
                    case 'jpg':
                        imagejpeg($dst_image, $cropPath);
                        break;
                    case 'jpeg':
                        imagejpeg($dst_image, $cropPath);
                        break;
                    case 'png':
                        imagepng($dst_image, $cropPath);
                        break;
                    case 'gif':
                        imagegif($dst_image, $cropPath);
                        break;
                }
            }

            $file = FileDBHelper::save([
                'name'   => $fileName,
                'size'   => $fileSize,
                'role'   => $role,
                'folder' => $folder,
                'path'   => $path,
                'extension'  => $fileExtension,
                'user_id' => $this->user->id,
                'url'    => env('APP_URL', '').$path,
            ]);

            return $file;
        }else{
            return false;
        }
    }


    public function delete($filePath, $id) {
        try {
            if(unlink($filePath)){
                return FileDBHelper::delete($id, $this->user->id);
            }else{
                return false;
            }
        } catch(\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
