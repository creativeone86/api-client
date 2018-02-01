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
                                {{$categoryName = ''}}
                                <span class="select-wrapper">
                                <select class="form-control no-radius" name="filters[category]">
                                    @foreach($categories as $category)
                                        @if($category['attributes']['path'] === $selectedCategory)
                                            {{$categoryName = $category['attributes']['name']}}
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
                                    <ul id="distance-select" class="dropdown-menu dropdown-menu-right"
                                        data-default="{{$selectedDistance}}">
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
                                            value="{{$selectedDistance}}"/>
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
                                            <div class="dropdown-menu expanded-filters dropdown-menu-right" role="menu">
                                                <div class="form-horizontal">
                                                    <div class="form-group border-bottom">
                                                        <label class="display-block padded-vertically">
                                                            <span>Type of practitioner <i
                                                                        class="fa fa-question-circle"></i></span>
                                                        </label>
                                                        <div class="row">
                                                            @foreach($practitionerFilters as $filter)
                                                                <div class="col-lg-6">
                                                                <div class="button-checkbox button-checkbox">
                                                                <button
                                                                        type="button"
                                                                        class="btn btn-lg button-checkbox-element"
                                                                        data-color="primary">{{$filter['label']}}</button>
                                                                <input
                                                                        {{in_array($filter['value'], $selectedPractitioners) ? 'checked' : ''}}
                                                                        value="{{$filter['value']}}"
                                                                        type="checkbox"
                                                                        class="hidden"/>
                                                            </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <input
                                                                id="practitioner-data"
                                                                name="filters[practitioner]"
                                                                type="hidden"/>
                                                    </div>
                                                    <div class="form-group border-bottom">
                                                        <label class="display-block padded-vertically">
                                                            <span>Professional Body <i
                                                                        class="fa fa-question-circle"></i></span>
                                                        </label>
                                                        <span class="button-checkbox">
                                                        <input
                                                                name="filters[professional_body]"
                                                                value="true"
                                                                {{$selectedMemberProfessionalBody != false ? 'checked' : ''}}
                                                                id="professional-body"
                                                                type="checkbox"
                                                                class="faChkRnd custom-checkbox">
                                                        <label for="professional-body" class="custom-label">Member of professional body</label>
                                                    </span>
                                                    </div>
                                                    <div class="form-group border-bottom">
                                                        <label class="display-block padded-vertically">
                                                            <span>Keyword search</span>
                                                        </label>
                                                        <div id="custom-input" class="input-group with-border">
                                                            <span class="input-group-addon">
                                                                <i class="fa fa-search" aria-hidden="true"></i>
                                                            </span>
                                                            <input
                                                                    placeholder="Search by keyword separated by comma."
                                                                    type="text"
                                                                    value="{{$selectedKeywords}}"
                                                                    name="filters[keywords]"
                                                                    class="form-control no-radius">

                                                        </div>
                                                    </div>

                                                    <div class="row padded-horizontal">
                                                        <div class="pull-right">
                                                            <a
                                                                    style="background-color: rgb(235,235,235);color: #000"
                                                                    href="/"
                                                                    class="btn btn-primary btn-lg">
                                                                <span>Reset</span>
                                                            </a>
                                                            <button
                                                                    style="background-color: rgb(59,59,59);color: #fff"
                                                                    type="submit"
                                                                    class="btn btn-primary btn-lg">
                                                                <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                                                <span>Apply</span>
                                                            </button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- search container end -->

                @if(isset($err))
                    <div class="row padded-horizontal" style="padding: 50px 0">
                            <div class="alert alert-danger">
                                <p class="align-center">{{$err['detail']}}</p>
                            </div>
                    </div>
                @elseif(isset($pagination) and count($pagination['data']) === 0)
                    <div class="row padded-horizontal" style="padding: 50px 0">
                        <div class="alert alert-info">
                            <p class="align-center">I'm here for you but nothing found. Please change your
                                criteria...</p>
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

                    <div class="row padded-horizontal" style="padding-top: 30px;padding-bottom: 30px;">
                        <h4>{{$categoryName}} in and {{$selectedDistance == '5mi' ? 'near' : 'far from'}} <strong>{{$selectedLocation}}</strong></h4>
                    </div>

                    <div class="row padded-horizontal">
                        <ul class="list-group">
                            @foreach($pagination['data'] as $item)
                                <li class="list-group-item item-card" data-geolocations='@json($item['attributes']['geolocations'])'>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <img class="cover-photo" src="{{$item['attributes']['author']['avatar']}}">
                                        </div>
                                        <div class="col-md-9 listing-content">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a class="toggle-bookmark" data-action="{{$item['meta']['bookmarked'] == 1 ? 'remove-bookmark' : 'add-bookmark'}}" uuid="{{$item['id']}}" href="#">
                                                        <i class="fa fa-heart{{$item['meta']['bookmarked'] == 1 ? '' : '-o'}} pull-right bookmark"
                                                                  aria-hidden="true"></i>
                                                    </a>
                                                </div>
                                            </div>

                                            <h4>{{$item['attributes']['author']['name']}}</h4>
                                            <h5>
                                                <i class="fa fa-map-marker"></i>
                                                <strong>{{$item['attributes']['location']}}</strong>
                                            </h5>

                                            {!! str_limit(strip_tags($item['attributes']['about']), $limit = 150, $end = '...') !!}

                                            <div style="margin-top: 25px;">
                                                <a
                                                        target="_blank"
                                                        href="{{$item['attributes']['author']['links']['contact']}}"
                                                        style="border: 1px solid lightgrey!important;"
                                                        class="btn btn-default">
                                                    View Profile
                                                </a>
                                            </div>


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
            <div class="col-md-4 col-xs-4 map-container">
                <p>&nbsp;</p>
                <div class="map-inner" style="position: fixed; top: 20px; right: 0; height: 400px;width: 300px;">
                    <div id="map" style="height: 400px;"></div>
                </div>

            </div>
        </div>
    </div>
    <script>

    </script>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD8Y5W36tpCMfGShrl1mmUm8bVhORwgWkE">
    </script>
@endsection
