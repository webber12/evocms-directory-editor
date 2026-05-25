<?php

namespace EvolutionCMS\EvoDirectoryEditor\Traits;


trait Response
{
    protected function response($arr, $code = 200)
    {
        return request()->ajax() ? response()->json($arr, $code) : $arr;
    }

}
