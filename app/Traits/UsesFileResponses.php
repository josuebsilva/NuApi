<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait UsesFileResponses
{
    /**
     * Success Response
     * @param $data
     * @param int $code
     * @return $this
     */
    public function fileResponse($content, $name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'csv': $contentType = 'text/csv'; break;
            case 'zip': $contentType = 'application/zip'; break;
            case "pdf": $contentType = "application/pdf"; break;
            case "exe": $contentType = "application/octet-stream"; break;
            case "doc": $contentType = "application/msword"; break;
            case "xls": $contentType = "application/vnd.ms-excel"; break;
            case "ppt": $contentType = "application/vnd.ms-powerpoint"; break;
            case "gif": $contentType = "image/gif"; break;
            case "png": $contentType =" image/png"; break;
            case "jpeg":
            case "jpg": $contentType = "image/jpeg"; break;
            default: $contentType = 'plain/text'; break;
        }

        return response($content)->withHeaders([
            'Cache-Control'       => 'max-age=0',
            'Content-type'        => $contentType,
            'Content-Disposition' => 'attachment; filename=' . $name,
        ]);
    }
}
