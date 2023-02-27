<?php

use Illuminate\Support\Facades\Cache;

/**
 * @param message which return with of response json
 * @param  data  $object
 * @param response_status like 200,500,400
 * @param  pagination  $pagination have default value null
 * @return response json
 */
function responseJson($response_status, $massage, $object = null, $pagination = null)
{
    return response()->json([
        'message' => $massage,
        'data' => $object,
        'pagination' => $pagination,
    ], $response_status);
}

/**
 * @param collection of data resource
 * @return array of properties for pagination
 */
function getPaginates($collection)
{
    return [
        'per_page' => $collection->perPage(),
        'path' => $collection->path(),
        'total' => $collection->total(),
        'current_page' => $collection->currentPage(),
        'next_page_url' => $collection->nextPageUrl(),
        'previous_page_url' => $collection->previousPageUrl(),
        'last_page' => $collection->lastPage(),
        'has_more_pages' => $collection->hasMorePages(),
        'from' => $collection->firstItem(),
        'to' => $collection->lastItem(),
    ];
}

// Cache methods
function cachePut($key, $value, $minutes = null)
{
    if ($minutes) {
        return Cache::put($key, $value, $minutes);
    } else {
        return Cache::put($key, $value);
    }
}

function cacheGet($key)
{
    return Cache::get($key);
}

function cacheForget($key)
{
    return Cache::forget($key);
}


/**
 * upload file from request
 * @param $file File -> file from request
 * @param $path String -> the path of folder of the file
 */
function uploadFile($file, $path)
{
    $realName = $file->getClientOriginalName();
    $filename = $file->hashName();
    $file->move($path, $filename);
    $fullpath =  $path . $filename;
    return $fullpath;
}



function uploadImage($id, array $value)
{
    \Spatie\MediaLibrary\MediaCollections\Models\Media::where('id', $id)->update($value);
}
