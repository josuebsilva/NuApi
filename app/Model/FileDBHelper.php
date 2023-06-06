<?php
/*
 * NuAPI
 *
 * Copyright 2023 Josué Barbosa
 * https://jedibit.com.br
 * https://stimper.com.br
 *
 * Licensed under the Apache License, Version 2.0 (the "License")
 */

namespace App\Model;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class FileDBHelper
{
    public static function save($fileData) {
        $file = new BucketFile();
        $file->name = $fileData['name'];
        $file->user_id = $fileData['user_id'];
        $file->path = $fileData['path'];
        $file->folder = $fileData['folder'];
        $file->size = $fileData['size'];
        $file->role = $fileData['role'];
        $file->extension = $fileData['extension'];
        $file->uuid = Uuid::uuid6();
        $file->save();

        $file->url = $fileData['url'].$file->uuid;
        $file->update();

        return $file;
    }

    public static function delete($id, $user_id){
        $file = BucketFile::where([
            'user_id' => $user_id,
            'id' => $id,
        ])->first();

        if(!$file)
            return false;

        $file->delete();

        return true;
    }
}
