<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Faker\Provider\Image;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        $query = new Product;
        if(!empty($request->title)) {
            $query = Product::where('title', 'like', '%'.$request->title.'%');
        }

        if(!empty($request->date)) {
            $date = $request->date;
            $query = $query->whereDate('created_at', '=', $date);
        }

        if(!empty($request->variant)) {
            $variant =  $request->variant;
            $query = $query->whereHas('variant', function($q) use($variant) {
                return $q->where('variant_id', $variant);
            });
        }

        if(!empty($request->price_from) && !empty($request->price_to)) {
            $minPrice = $request->price_from > 0 ? $request->price_from : 0;
            $maxPrice = $request->price_to > 0 ? $request->price_to : 0;
            $query = $query->whereHas('prices', function($q) use($minPrice, $maxPrice) {
                return $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }


        $products = $query == '' ? Product::paginate(3) : $query->paginate(3);

        $products->appends([
            'title' => $request->title,
            'variant' => $request->variant,
            'price_from' => $request->price_from,
            'price_to' => $request->price_to,
            'date' => $request->date
        ]);
        $variants = Variant::all();

        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $product = new Product;
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        // Product Image
        if(count($request->product_image) > 1) {
            $file = $request->product_image[0];
            $url = $file['dataURL'];
            $filenamePartials = explode('.', $file['upload']['filename']);
            $basename = $filenamePartials[0];
            $extension = $filenamePartials[1];
            $uniqe_id =  uniqid();
            $filename = $uniqe_id . '.'.$extension;
            $file = "/public/images/{$filename}";
            \Storage::put($file, file_get_contents($url));

            // Store
            $productImage = new ProductImage;
            $productImage->product_id = $product->id;
            $productImage->file_path = public_path() . "/images/{$filename}";
            $productImage->save();
        }

        // Product variant
        foreach($request->product_variant as $variant) {
            // $variantModel = Variant::find($variant['option']);

            $productVariant = new ProductVariant;
            $productVariant->variant = implode(',', $variant['tags']);
            $productVariant->variant_id = $variant['option'];
            $productVariant->product_id = $product->id;
            $productVariant->save();
        }
        // Product variant prices
        foreach($request->product_variant_prices as $variant_price) {
            $variants = $product->variant;

            $productVariant = new ProductVariantPrice;
            $productVariant->product_variant_one = isset($variants[0]) ? $variants[0]->id : null;
            $productVariant->product_variant_two = isset($variants[1]) ? $variants[1]->id : null;
            $productVariant->product_variant_three = isset($variants[2]) ? $variants[2]->id : null;
            $productVariant->price = $variant_price['price'];
            $productVariant->stock = $variant_price['stock'];
            $productVariant->product_id = $product->id;
            $productVariant->save();
        }
        return response()->json(['product' => $product]);
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product'));
    }

    public function getProduct($id)
    {
        $product = Product::with(['image', 'variant', 'prices'])->whereId($id)->first();

        return response()->json(['product' => $product]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->save();
        // Product Image
        if(count($request->product_image) > 1) {
            $file = $request->product_image[0];
            $url = $file['dataURL'];
            $filenamePartials = explode('.', $file['upload']['filename']);
            $basename = $filenamePartials[0];
            $extension = $filenamePartials[1];
            $uniqe_id =  uniqid();
            $filename = $uniqe_id . '.'.$extension;
            $file = "/public/images/{$filename}";
            \Storage::put($file, file_get_contents($url));

            // Store
            $productImage = new ProductImage;
            $productImage->product_id = $product->id;
            $productImage->file_path = public_path() . "/images/{$filename}";
            $productImage->save();
        }

        $product->variant()->delete();

        // Product variant
        foreach($request->product_variant as $variant) {
            $variantModel = Variant::find($variant['option']);

            $productVariant = new ProductVariant;
            $productVariant->variant = implode(',', $variant['tags']);
            $productVariant->variant_id = $variant['option'];
            $productVariant->product_id = $product->id;
            $productVariant->save();
        }
        // Product variant prices
        $product->prices()->delete();
        foreach($request->product_variant_prices as $variant_price) {
            $variants = $product->variant;

            $productVariant = new ProductVariantPrice;
            $productVariant->product_variant_one = isset($variants[0]) ? $variants[0]->id : null;
            $productVariant->product_variant_two = isset($variants[1]) ? $variants[1]->id : null;
            $productVariant->product_variant_three = isset($variants[2]) ? $variants[2]->id : null;
            $productVariant->price = $variant_price['price'];
            $productVariant->stock = $variant_price['stock'];
            $productVariant->product_id = $product->id;
            $productVariant->save();
        }
        return response()->json(['product' => $request->all()]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    public function search(Request $request)
    {

        var_dump("search");
        die();

        if(!empty($request->title)) {
            $query = Product::where('title', 'like', '%'.$request->title.'%');
        }

        $products = $query->paginate(3);

        return view('products.index', compact('products'));
    }

    public function upload(Request $request)
    {
        $imageName = time().'.'.$request->file->getClientOriginalExtension();
        // $request->file->move(public_path('iamges/products'), $imageName);
    }
}
