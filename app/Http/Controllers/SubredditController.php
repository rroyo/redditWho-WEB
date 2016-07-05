<?php
# redditWho WEB
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
//use Illuminate\Support\Facades\Input;
//use Mockery\CountValidator\Exception;
use Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use App\Http\Controllers\ControllerHelpers;

class SubredditController extends ControllerHelpers
{
    public function index(Request $request)
    {
        $topSubreddits = 1500;      //Max number of subreddits to be shown
        $perPage = 25;
        
        //Capture query string values
        $page = intval($this->getParam($request, 'page', '1'));
        $orderBy = $this->getParam($request, 'orderBy', 'subscribers');
        $order = $this->getParam($request, 'order', 'desc');        

        //Valid queries
        $validOrderByCriteria = ['display_name', 'created_utc', 'subscribers', 'submissions', 'over18'];
        $validOrderCriteria = ['desc', 'asc'];

        //Checks if the path is correctly composed, if it's not, shows a 404 page
        if ( $this->badPath($topSubreddits, $perPage, $page, $orderBy, $validOrderByCriteria, $order, $validOrderCriteria ) ) {
            return view('errors/404');
        }

        //New Client for querying the API
        $client = new Client([
            'base_uri' => 'http://localhost:8000/api/v1/subreddits/',
            'timeout' => 60.0,
        ]);

        // Composes the path to append to the base uri
        $path = $this->composePath($page, $orderBy, $order);

        //Query the API
        $query = $client->request('GET', $path)->getBody();

        //Json response to array
        $subreddits = json_decode($query, true);

        //Vars added to facilitate the composition of urls in the paging bars
        $subreddits['path'] = $path;
        $subreddits['topSubreddits'] = $topSubreddits;

        return view('subreddits', $subreddits);
    }

    public function subredditPosts(Request $request){

        $totalTopPosts = 0;     //Mentre no apliqui la correcció P3, un cop aplicada aquest valor vindrà de la pròpia BBDD
        $perPage = 25;

        //Capture query string values
        $subId = intval($this->getParam($request, 'id', 0));
        $page = intval($this->getParam($request, 'page', '1'));
        $orderBy = $this->getParam($request, 'orderBy', 'score');
        $order = $this->getParam($request, 'order', 'desc');

        //Valid queries
        $validOrderByCriteria = ['created_utc', 'author', 'score', 'domain', 'num_comments'];
        $validOrderCriteria = ['asc', 'desc'];

        //Checks if the path is correctly composed, if it's not, shows a 404 page
        if ( $this->badPath($totalTopPosts, $perPage, $page, $orderBy, $validOrderByCriteria, $order, $validOrderCriteria, $isSubreddit=true ) ) {
            return view('errors/404');
        }

        $client = new Client([
            'base_uri' => 'http://localhost:8000/api/v1/posts/',
            'timeout' => 60.0,
        ]);

        // Composes the path to append to the base uri
        $path = $this->composePath($page, $orderBy, $order, $subId);

        //Query the API
        try {
            $query = $client->request('GET', $path)->getBody();
        } catch (ServerException $e) {
            return view('errors/404');
        }

        //Json response to array
        $posts = json_decode($query, true);

        return view('posts', $posts);
    }
}
