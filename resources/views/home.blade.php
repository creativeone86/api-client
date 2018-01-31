@extends('layouts.app')

@section('content')

    <div class="container padded-horizontal search-wrapper">
        <div class="row">
            <div class="col-md-8 col-xs-8 main-content">
                <form>
                    <div class="row search-holder">
                    <div class="col-lg-5">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-search" aria-hidden="true"></i>
                            </span>
                            <span class="select-wrapper">
                                <select class="form-control no-radius" name="filters[category]">
                                    @foreach($categories as $category)
                                    @if($category['attributes']['path'] === $selectedCategory)
                                    <option
                                            value="{{$category['attributes']['path']}}"
                                            selected>
                                        {{$category['attributes']['name']}}
                                    </option>
                                        @else
                                            <option value="{{$category['attributes']['path']}}">
                                            {{$category['attributes']['name']}}
                                            </option>
                                        @endif
                                    @endforeach
                             </select>
                            </span>

                        </div>

                    </div><!-- /.col-lg-6 -->
                    <div class="col-lg-5">
                        <div class="input-group {{isset($err['detail']) && $err['detail'] == 'Location must be provided.' ? 'has-error' : ''}}">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </span>
                            <input
                                    type="text"
                                    class="form-control"
                                    name="filters[location]"
                                    placeholder="Type location here..."
                                    value="{{$selectedLocation ?? ''}}">
                            <div class="input-group-btn">
                                <button
                                        type="button"
                                        id="distance-button"
                                        class="btn btn-default dropdown-toggle border-left"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">+ 5 miles<span class="caret"></span>
                                </button>
                                <ul id="distance-select" class="dropdown-menu dropdown-menu-right" data-default="{{$selectedDistance}}">
                                    @foreach($distanceFilters as $filter)
                                    <li>
                                        <a
                                            data-value="{{$filter['value']}}"
                                            href="#"
                                            class="apply-distance">+ {{$filter['label']}}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                <input
                                    type="hidden"
                                    name="filters[distance]"
                                    id="distance-value"
                                    value="{{$selectedDistance}}" />
                            </div><!-- /btn-group -->
                        </div><!-- /input-group -->
                    </div><!-- /.col-lg-6 -->
                    <div class="col-lg-2">
                        <div class="input-group" id="adv-search">
                            <div class="input-group-btn">
                                <div class="btn-group" role="group">
                                    <div class="dropdown dropdown-lg">
                                        <button
                                                type="button"
                                                class="btn btn-default dropdown-toggle btn-dark"
                                                data-toggle="dropdown"
                                                aria-expanded="false">
                                            Filter
                                            <span class="caret"></span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" role="menu">
                                            <form class="form-horizontal" role="form">
                                                <div class="form-group">
                                                    <label for="filter">Filter by</label>
                                                    <select class="form-control">
                                                        <option value="0" selected>All Snippets</option>
                                                        <option value="1">Featured</option>
                                                        <option value="2">Most popular</option>
                                                        <option value="3">Top rated</option>
                                                        <option value="4">Most commented</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="contain">Author</label>
                                                    <input class="form-control" type="text"/>
                                                </div>
                                                <div class="form-group">
                                                    <label for="contain">Contains the words</label>
                                                    <input class="form-control" type="text"/>
                                                </div>
                                                <button type="submit" class="btn btn-primary"><span
                                                            class="glyphicon glyphicon-search"
                                                            aria-hidden="true"></span></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
                <!-- search container end -->

                @if(!isset($pagination) and isset($err))
                    <div class="row padded-horizontal" style="padding: 50px 0">
                            <div class="alert alert-danger">
                                <p class="align-center">{{$err['detail']}}</p>
                            </div>
                    </div>

                @endif

                @if(isset($pagination) && $pagination['totalRecords'] > 0)
                <div class="row padded-horizontal results-count">
                    <h4>
                        {{$pagination['totalRecords']}}
                        result{{$pagination['totalRecords'] > 1 ? 's' : ''}}
                    </h4>
                </div>
                <!-- results count end -->

                <div class="row padded-horizontal">
                    <ul class="list-group">
                        @foreach($pagination['data'] as $item)
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-md-3">
                                    <img class="cover-photo" src="{{$item['attributes']['image']}}">
                                </div>
                                <div class="col-md-9 listing-content">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <i class="fa fa-heart-o pull-right bookmark" aria-hidden="true"></i>
                                            {{--<i class="fa fa-heart pull-right bookmark" aria-hidden="true"></i>--}}
                                        </div>
                                    </div>
                                    <h4>{{$item['attributes']['title']}}</h4>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>

                    @if(count($pagination['pages']) > 1)
                        <div class="row padded-horizontal align-center">
                            <ul class="pagination">
                                {{-- Previous Page Link --}}
                                @if ($pagination['onFirstPage'])
                                    <li class="disabled"><span>&laquo;</span></li>
                                @else
                                    <li><a href="{{ $pagination['previousPageUrl'] }}" rel="prev">&laquo;</a></li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($pagination['pages'] as $element)
                                    {{-- "Three Dots" Separator --}}
                                    @if (isset($element['text']))
                                        <li class="disabled"><span>{{ $element['text'] }}</span></li>
                                    @else
                                        @if ($element['page'] == $pagination['currentPage'])
                                            <li class="active"><span>{{ $element['page'] }}</span></li>
                                        @else
                                            <li><a href="{{ $element['url'] }}">{{ $element['page'] }}</a></li>
                                        @endif
                                    @endif

                                @endforeach

                                {{-- Next Page Link --}}
                                @if ($pagination['hasMorePages'])
                                    <li><a href="{{ $pagination['nextPageUrl'] }}" rel="next">&raquo;</a></li>
                                @else
                                    <li class="disabled"><span>&raquo;</span></li>
                                @endif
                            </ul>
                        </div>
                    @endif
                @endif

            </div>
            <div class="col-md-4 col-xs-4">
                <h3>Google maps here!</h3>
            </div>
        </div>
    </div>
@endsection
