<?php

namespace App\Http\Controllers\Main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Commodity;
use App\Registry;
use App\Article;
use Illuminate\Support\Carbon;
use Artesaos\SEOTools\Traits\SEOTools as SEOToolsTrait;

// use Laracasts\Utilities\JavaScript\JavaScriptFacade as Javascript;
// use Illuminate\Support\Arr;

class CommodityController extends Controller
{
    use SEOToolsTrait;

    public function router(Request $request, Registry $entry, Commodity $commodity)
    {
        if (!$commodity->status) {
            app()->abort(404);
        }

        $commodity = Commodity::query()
            ->whereId($commodity->id)
            ->select('commodities.*')
            ->with('registry')
            ->with(['articles' => function ($query) {
                $query
                    ->select('articles.*')
                    ->orderBy('ranking', 'asc')
                    ->take(150);               
                }
            ])            
            ->first();

        $carbon = new Carbon();

        $this->seo()
            ->setTitle($entry->meta_title)
            ->setDescription($entry->meta_description)
            ->setCanonical(url()->current());
        $this->seo()
            ->metatags()
            ->setKeywords($entry->meta_keywords)
            ->addMeta('robots', $entry->meta_robots);
        $this->seo()
            ->opengraph()
            ->setUrl(url($entry->url));

        return view($entry->view)
            ->with('entry', $entry)
            ->with('commodity', $commodity)
            ->with('carbon', $carbon);
    }

    public function index()
    {
        $commodities = Commodity::all();
        $articles = Article::query()
            ->where('item_type', '=', 'App\Commodity')
            ->orderBy('ranking', 'asc')
            ->take(150);

            dd($articles);

        $carbon = new Carbon();


        return view($entry->view)
            ->with('entry', $entry)
            ->with('Commodity', $Commodity)
            ->with('carbon', $carbon);
    }
}
