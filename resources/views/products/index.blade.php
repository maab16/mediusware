@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="/product" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" value="{{ request()->title ?? '' }}" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">Select variant</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}" {{ request()->variant == $variant->id ? 'selected' : '' }}>{{ $variant->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" value="{{ request()->price_from ?? '' }}" aria-label="First name" placeholder="From" class="form-control">
                        <input type="text" name="price_to" value="{{ request()->price_to ?? '' }}" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" value="{{ request()->date ?? '' }}" placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($products as $key => $product)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td> {{ $product->title }} <br> Created at : {{ $product->created_at }}</td>
                        <td>{{ $product->description }}</td>
                        <td>
                            <dl class="row mb-0" style="height: 80px; overflow: hidden" id="{{ 'variant_' . $key }}">
                            @foreach($product->prices as $variant_price)
                                <dt class="col-sm-3 pb-0">
                                    @php
                                        $variant1 = $variant_price->variant1->variant ?? [];
                                        $variant2 = $variant_price->variant2->variant ?? [];
                                        $variant3 = $variant_price->variant3->variant ?? [];
                                    @endphp
                                    @php $tags = array_merge($variant1, $variant2, $variant3); @endphp
                                    {{ implode(" / ", array_unique($tags)) }}
                                </dt>
                                <dd class="col-sm-9">
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 pb-0">Price : {{ number_format($variant_price->price,2) }}</dt>
                                        <dd class="col-sm-8 pb-0">InStock : {{ number_format($variant_price->stock,2) }}</dd>


                                    </dl>
                                </dd>
                                @endforeach
                            </dl>
                            <button onclick="$('{{"#variant_" . $key}}').toggleClass('h-auto')" class="btn btn-sm btn-link">Show more</button>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <p>There is no product.</p>
                    @endforelse


                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>Showing 1 to 10 out of 100</p>
                </div>
                <div class="col-md-2">
                {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
