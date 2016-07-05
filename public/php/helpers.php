<?php

/**
 * Generates the links for the pagination of the tables that show
 * the subreddits and publications.
 *
 * @param int $elements The total number of elements
 * @param int $perPage Total number of elements per page
 * @param int $currentPage The current page the user is viewing
 * @param int $totalNumberedLinks Total number of numbered links in the pagination bar
 * @param String $prefix to append to the top level domain
 * @return String The generated <li> elements that compose all the links in the pagination bar
 */

function paginationSetup ($elements, $perPage, $currentPage, $totalNumberedLinks, $prefix='') {
    //lastPage, takes into consideration the possibility of having less than $perPage elements
    //on the last page and adds +1 to $lastPage if that's the case.
    $lastPage = ( $elements % $perPage ) ? intval ( $elements / $perPage + 1 ) : ( $elements / $perPage );
    //Middle position in the numbered links
    $middleNumber = intval ($totalNumberedLinks / 2 + 1);

    //If the total number of $elements is smaller than the elements per page times the total of links asked for
    // then the total numbers of links, must be changed
    if ( ( ( $totalNumberedLinks - 1 ) * $perPage ) > $elements ){
        $totalNumberedLinks = intval ($elements / $perPage ) + 1;
    }

    // previous and next page setup
    if ( $currentPage > 1 && $currentPage < $lastPage ) {
        $previousPage = $currentPage - 1;
        $nextPage =  $currentPage + 1;
    }
    //is it the last page?
    elseif ( $currentPage == $lastPage ) {
        $previousPage = $lastPage - 1;
        $nextPage = false;
    }
    //1 or below and non numeric values
    else {
        $previousPage = false;
        $nextPage = 2;
    }

    /*
        This code builds the HTML links
    */

    //First and previous links
    $pagination = '<ul class="list-unstyled list-inline paging">';
    $pagination .= linkOrNot($prefix, $currentPage, 1, 'First');
    $pagination .= linkOrNot($prefix, $currentPage, $previousPage, 'Previous');

    //Numbered links (emulates eBay pagination)
    for( $i = 1; $i <= $totalNumberedLinks; $i++ ) {
        //Pages from 1 to 6 for a 10 links pagination
        if ( $currentPage <= $middleNumber ) {
            $pageNumber = $i;
            $pagination .= linkOrNot($prefix, $currentPage, $pageNumber);
        }

        //Pages from 7 to ( lastPage - middleNumber + 1 ) for a 10 links pagination
        elseif ( $currentPage < ( $lastPage - $middleNumber + 1) ) {
            $pageNumber = $currentPage - $middleNumber + $i;
            $pagination .= linkOrNot($prefix, $currentPage, $pageNumber);
        }
        //Pages from ( lastPage - middleNumber + 1 ) to the last page
        else {
            $pageNumber = $lastPage - $totalNumberedLinks + $i;
            $pagination .= linkOrNot($prefix, $currentPage, $pageNumber);
        }
    }

    //Next and last links
    $pagination .= linkOrNot($prefix, $currentPage, $nextPage, 'Next');
    $pagination .= linkOrNot($prefix, $currentPage, $lastPage, 'Last');
    $pagination .= '</ul>';

    return $pagination;
}

/**
 * Creates a page link if the current page number is not equal to the
 * current page number being generated for the pagination.
 *
 * @param String $prefix to append to the top level domain
 * @param int $currentPage The current page the user is viewing
 * @param int $pageNum The current page number being generated for the pagination bar
 * @param String $text Optional, text to show instead of a page number
 * @return String The generated <li> element, with or without a link
 */
function linkOrNot($prefix='', $currentPage, $pageNum, $text=false) {
    //Capture order criteria from the URL, if not set, the API will assign default values
    $orderBy = getParam("orderBy", '');
    $order = getParam("order", '');
    $subredditId = getParam("id", '');

    /*
        If a text is passed, it'll be shown instead of a page number
        Used for the First, Previous, Next and Last links
    */
    if ( $text ) {
        $pageNumText = $text;
        /*
            If $pageNumber evaluates to false, it means we're either on the first
            or the last page, so $pageNumber must be set equal to $currentPage
            to represent the actual page the user is viewing. This way no link will
            be created in the next if..else block.
        */
        $pageNum = $pageNum ? $pageNum : $currentPage;
    }
    //No text is passed, so a number will be shown in the <li> element
    else {
        $pageNumText = $pageNum;
    }

    //Don't create a link
    if ( $currentPage == $pageNum ) {
        return '<li class="deactivated-page">' . $pageNumText . '</li>';
    } //Create a link
    else {
        return '<li><a href="'  . '?'
                                . $orderBy
                                . $order
                                . $subredditId
                                . 'page=' . $pageNum
                .'">' . $pageNumText . '</a></li>';
    }
}

/**
 * Get a value parameter from the URL, removes any non alphanumeric character, except the underscore
 *
 * @param $param parameter to capture from the URL
 * @return bool|string
 */
function getParamValue($param){
    if (isset($_GET[$param])) {
        $value = preg_replace('/[^\da-zA-Z_]/i', '', $_GET[$param]);
        return $value;
    }

    return false;
}

/**
 * Gets the value of a parameter from the URL, formats it so it can be attached to a query string
 *
 * @param $param parameter to capture from the URL
 * @param $default default value in case $param isn't set
 * @return bool|string
 */
function getParam($param, $default){
    if ($value = getParamValue($param)) {
        return $param . '=' . $value . '&';
    }

    return $default;
}

/**
 * Sets the orientation and column of the order icon
 *
 * @param $column which column is evaluated
 * @return string
 */

function setOrderIcon($column){
    $issetSubredditId = getParamValue('id');
    $issetOrderBy = getParamValue('orderBy');
    $orderParam = 'desc';

    //Stablishing defaults in case orderBy is not set
    //Is it a subreddits submissions listing?
    if ($issetSubredditId) {
        if ($issetOrderBy) {
            $orderByParam = $issetOrderBy;
            $orderParam = getParamValue('order');
        }
        else {
            $orderByParam = 'score';
        }
    }
    //If not, it's the main subreddits listing
    else {
        if ($issetOrderBy) {
            $orderByParam = $issetOrderBy;
            $orderParam = getParamValue('order');
        }
        else {
            $orderByParam = 'subscribers';
        }
    }

    //Setting the order icon
    if ($orderByParam == $column) {
        if ($orderParam == 'desc') {
            $class = "arrow-down";
        }
        else {
            $class = "arrow-up";
        }

        $string = '<div><div class="' . $class . '"></div></div>';
    }
    else {
        $string = '';
    }

    return $string;
}


/**
 * Composes a query string that allows to reorder the columns of the
 *
 * @param $column which column is the link being created for
 * @return string
 */

function composeHeadersQueryString($column){
    $string = '?';

    $issetOrder = getParamValue('order');
    $orderByParam = getParamValue('orderBy');

    if($orderByParam == $column) {
        if ($issetOrder == 'desc'){
            $orderParam = 'asc';
        }
        else{
            $orderParam = 'desc';
        }
    }
    else {
        $orderParam = 'asc';
    }

    $string .= 'orderBy=' . $column . '&'
            . 'order=' . $orderParam . '&'
            . getParam('id', '')
            . getParam('page', 'page=1');

    return $string;
}















/********************************************
 * BACKUPS



 */