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


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

use App\Model\BucketFile;
use App\Model\BucketStorage;
use App\Traits\UsesJsonResponses;
use App\Traits\UsesFileResponses;
use Illuminate\Support\Facades\Auth;

class BucketController extends Controller
{
    use UsesJsonResponses, UsesFileResponses;

    public function store(Request $request)
    {
        $this->validate($request, [
            'role' => ['required', 'string'],
            'folder' => ['required', 'string'],
        ]);

        try{
            $files  = $request->file('files');
            $role   = $request->role;
            $folder = $request->folder;
            $filesOut  = [];
            $count = 0;
            $user = Auth::user();
            foreach($files as $fse) {
                $crop   = null;
                if($request->crop) {
                    $cropData = json_decode($request->crop, true);
                    if($cropData[$count]) {
                        $crop = $cropData[$count];
                    }
                }

                if ($role !== 'public' && $role !== 'private') {
                    return $this->errorResponse('Regra de acesso inválida ', Response::HTTP_BAD_REQUEST);
                }

                $bucket = new BucketStorage($user, $folder);
                $filesOut[] = $bucket->storage($fse, $role, $folder, $crop);

                $count++;
            }

            return $this->successResponse($filesOut);
        }catch(Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex->getTraceAsString());
            return $this->errorResponse('Erro interno ', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $file)
    {
        try{
            $user = Auth::user();
            $fileId = $file;
            $file = BucketFile::where([
                'id' => $fileId,
                'user_id' => $user->id,
            ])->first();

            if (!$file) {
                return $this->errorResponse('Arquivo não encontrado', Response::HTTP_NOT_FOUND);
            }

            $file_path = storage_path() .'/app'.$file->path.$file->name;
            $bucket = new BucketStorage($user);

            $file   = $bucket->delete($file_path, $fileId);

            if(!$file) {
                return $this->errorResponse('Erro interno ao tentar remover o arquivo', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            return $this->successResponse('Success');
        }catch(Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex->getTrace());
            return $this->errorResponse('Erro interno ', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function deleteAll(Request $request)
    {
        try{
            $user = Auth::user();
            $files = BucketFile::where([
                'user_id' => $user->id,
            ])->get();

            foreach ($files as $fileRemove) {
                $file_path = storage_path() .'/app'.$fileRemove->path.$fileRemove->name;
                $bucket = new BucketStorage($user);

                $file = $bucket->delete($file_path, $fileRemove->id);

                if(!$file) {
                    //return $this->errorResponse('Erro interno ao tentar remover o arquivo', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }

            return $this->successResponse('Success');
        }catch(Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex->getTrace());
            return $this->errorResponse('Erro interno ', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get(Request $request, $u, $role = null, $folder, $file) {
        try {
            switch ($role) {
                case 'public':
                    $file = BucketFile::where([
                        'uuid' => $file,
                    ])->first();

                    if (!$file) {
                        return $this->errorResponse('Arquivo não encontrado', Response::HTTP_NOT_FOUND);
                    }
                    break;
                case 'private':
                    //Private check
                    $token = $request->bearerToken();
                    if (!$token) {
                        // Unauthorized response if token not there
                        return response()->json([
                            'error' => 'Token not provided.'
                        ], Response::HTTP_UNAUTHORIZED);
                    }
                    try {
                        $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
                        //$user = User::find($credentials->sub);

                    } catch (ExpiredException $e) {
                        return response()->json([
                            'error' => 'Provided token is expired.'
                        ], Response::HTTP_BAD_REQUEST);
                    } catch (\Exception $e) {
                        return response()->json([
                            'error' => 'An error while decoding token.'
                        ], Response::HTTP_BAD_REQUEST);
                    }

                    $request->user = \App\Models\User::find($credentials->sub->id);

                    if($request->user == null) {
                        return $this->errorResponse('Acesso não permitido', Response::HTTP_FORBIDDEN);
                    }

                    $file = BucketFile::where([
                        'id' => $file,
                        'account_id' => $request->user->current_account,
                    ])->first();

                    if(!$file) {
                        return $this->errorResponse('Acesso não permitido', Response::HTTP_FORBIDDEN);
                    }
                    break;
            }

            $filePath = 'bucket/' . $u . '/' . $role . '/' . $folder . '/' . $file->name;

            if (Storage::exists($filePath))
            {
                $fileExt = $file->extension;
                $path =  storage_path() .'/app/'.$filePath;
                if($fileExt == 'jpg' || $fileExt == 'jpeg'){
                    $im = imagecreatefromjpeg($path);
                    if ($im !== false) {
                        header('Content-Type: image/jpeg');
                        imagejpeg($im);
                    }
                }
                if($fileExt == 'png'){
                    $im = imagecreatefrompng($path);
                    if ($im !== false) {
                        header('Content-Type: image/png');
                        imagepng($im);
                    }
                }
            } else {
                // Error
//                exit('Requested file does not exist on our server!');
                return $this->errorResponse('Requested file does not exists', Response::HTTP_NOT_FOUND);
            }
        } catch(Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex->getTraceAsString());
            return $this->errorResponse('Erro interno ', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function all(Request $request) {

        try{
            $user = Auth::user();
            $alls = BucketFile::where([
                'user_id' => $user->id,
            ])->get();

            return $this->successResponse($alls, 200);
        }catch(Exception $ex) {
            Log::error($ex->getMessage());
            Log::error($ex->getTraceAsString());
            return $this->errorResponse('Erro interno ', 500);
        }
    }
}
