<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControllerHelpers extends Controller
{
    /**
     * Composes the path of the URI for querying the API
     *
     * @param int $page page number
     * @param String $orderBy column to order by the data, defaults to subscribers (subreddits list) or created_utc (posts)
     * @param String $order asc or desc, defaults to desc
     * @param int $idint, optional, when requesting posts the id of the subreddits in base10
     * @return String the composed path
     */
    public function composePath($page, $orderBy, $order, $id=null){
        $path = '';

        //Only useful when requesting the posts of a subreddit
        if (isset($id)) {
            $path .= $id . '/';
        }

        //Set the orderBy criteria
        if (isset($orderBy)) {
            $path .= $orderBy . '/';
        }
        //Default when posts are requested
        elseif (isset($id))  {
            $path .= 'created_utc/';
        }
        //Default when the list of subreddits is requested
        else {
            $path .= 'subscribers/';
        }

        //Set the order criteria
        if (isset($order)) {
            $path .= $order . '/';
        }
        else {
            $path .= 'desc/';
        }

        if (isset($page)) {
            $path .= '?page=' . htmlentities($page);
        }
        else {
            $path .= '?page=1';
        }

        return $path;
    }

    /**
     * Checks whether the path is well composed
     *
     * @param String $page page number,
     * @param String $perPage Elements per page.
     * @param String $page Page number to check.
     * @param String $orderBy Column to order by the data.
     * @param String $order asc or desc.
     * @return bool false if path is bad
     */
    public function badPath($totalElements, $perPage, $page, $orderBy, $validOrderByCriteria, $order, $validOrderCriteria, $isSubreddit=false) {
        //Validates page number
        $page = intval($page);
        
        //Parche, mentre no apliqui la modificació P3
        //Un cop aplicada, es pot eliminar el if( !isSubreddit )
        if ( !$isSubreddit ) {
            $lastPage = ($totalElements % $perPage) ? intval($totalElements / $perPage + 1) : ($totalElements / $perPage);

            if ($page > $lastPage || $page < 1) {
                return true;
            }
        }

        //Validates ORDER BY criteria
        if ( ! in_array($orderBy, $validOrderByCriteria) ) {
            return true;
        }

        //Validates ORDER BY criteria
        if ( ! in_array($order, $validOrderCriteria) ) {
            return true;
        }

        //Everything is fine
        return false;
    }

    /**
     * Gets the value of a parameter from the URL
     *
     * @param Request $request the URI requested
     * @param string $param parameter to capture
     * @param int $min smallest value when querying for elements per page
     * @param int $max biggest value when querying for elements per page
     * @return int || string || null
     **/
    public function getParam(Request $request, $param, $default=null, $max=null)
    {
        if ( $request->has($param) )
        {
            $getVar = $request->input($param);

            //Is the param a 'page' or 'elements per page' value?
            if ( gettype($getVar) == 'integer' ) {
                if (isset($max) && ($getVar > $max)) {
                    $getVar = $max;
                } elseif (isset($default) && ($getVar < $default)) {
                    $getVar = $default;
                }
            }

            return $getVar;
        }

        return $default;
    }
}