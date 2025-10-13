<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

//use Redis;
//use Illuminate\Support\Facades\Redis;


class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request['perPage'];
            $data = Product::orderBy('index')->orderByDesc('id')->where('title', 'Like', '%' . $request['search'] . '%')->paginate($perPage);
            $pages_count = ceil($data->total()/$perPage);
            $labels = [];
            for ($i=1; $i <= $pages_count; $i++){
                (array_push($labels,$i));
            }
            return response([
                "data"=>ProductResource::collection($data),
                "pages"=>$pages_count,
                "total"=> $data->total(),
                "labels"=> $labels,
                "title"=> 'محصولات',
                "tooltip_new"=> 'ثبت محصول جدید',
            ], 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }

    public function latest()
    {

        try {
            $data = Product::all()->sortByDesc('id')->take(4);
            return response(ProductResource::collection($data), 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }

    public function indexSite(Request $request)
    {
        // dd($request->all());
        try {
            $data = Product::orderBy('index')->orderByDesc('id')->whereHas('activeCategory')->with('category')->where('active', 1);
            if ($request['cat'] != '') {
                $data = $data->where('product_category_id', $request['cat']);
            }
            if ($request['stock'] == 'true') {
                $data = $data->where('stock', '>', 0);
            }elseif ($request['stock'== 'limited']){
                $data = $data->where('stock', '>', 0)->where('stock','<',5);
            }
//            if ($request['cat_ids'] != '') {
//                $ids = explode(',', $request['cat_ids']);
//                $data = $data->whereIn('product_category_id', $ids);
//
//            }
            if ($request['off'] == 'true') {
                $data = $data->where('off', '>', 0)->where('stock', '>', 0);

            }
            if ($request['search'] != '') {
                $data = $data->where('title', 'Like', '%' . $request['search'] . '%');

            }
            if ($request['sort'] != '') {
                switch ($request['sort']) {

                    case ('sale'):
                    {
                        $data = $data->orderByDesc('sale');
                        break;
                    }
                    case ('score'):
                    {
                        $data = $data->orderByDesc('score');
                        break;
                    }
                    case ('cheap'):
                    {
                        $data = $data->orderBy('price');
                        break;
                    }
                    case ('expensive'):
                    {
                        $data = $data->orderByDesc('price');
                        break;
                    }
                    case ('view'):
                    {
                        $data = $data->orderByDesc('view');
                        break;
                    }
                    default:
                    {
                        $data = $data->orderByDesc('id');
                        break;
                    }
                }
            }
            if ($request['sale'] == 'true') {
                $data = $data->orderByDesc('sale');

            }
            if ($request['limit'] != '') {
                $data = $data->skip(0)->take($request['limit']);
            }
            $data = $data->get();

            return response(ProductResource::collection($data), 200);
//            return response(new ProductResource($data), 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }

    public function stockSite()
    {
        try {
//
            $data = Product::whereHas('activeCategory')->with('category')->where('active', 1)->where('stock', '>', 0)->latest()->get();

            return response(ProductResource::collection($data), 200);
//            return response(new ProductResource($data), 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }

    public function latestSite()
    {
        try {
            $data = Product::whereHas('activeCategory')->with('category')->where('active', 1)->take(4)->latest()->get();
            return response(ProductResource::collection($data), 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }

    public function show(Product $product)
    {
        try {
            return response(new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }


    public function saveImages($requestImages, $productId)
    {
        try {
            $images = '';
            for ($i = 0; $i < count($requestImages); $i++) {
                if ($requestImages[$i][1]) {
                    $name = 'product_' . $productId . '_' . uniqid() . '.png';
                    $image_path = (new ImageController)->uploadImage($requestImages[$i][1], $name, 'images/');
//                    (new ImageController)->resizeImage('images/', $name);
                    $images = $images . '/' . $image_path . ',';
                } else if ($requestImages[$i][0]) {
                    $images = $images . $requestImages[$i][0] . ',';
                }
            }

            return $images;
        }catch (Exception $exception){
            return $exception;
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all('title'),
            [
                'title' => 'required|unique:products,title',
            ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'title.unique' => 'این عنوان قبلا ثبت شده است',
            ]
        );
        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }
        try {
            $product = Product::create($request->except('image1','image2'));
            if ($request['image1']) {
                $name = 'product_' . $product['id'] . '_' . uniqid() . '.png';
                $image_path = (new ImageController)->uploadImage($request['image1'], $name, 'images/products/');
                $product->update(['image1' => '/' . $image_path]);

                (new ImageController)->resizeImage('images/products/',$name);
            }
            if ($request['image2']) {
                $name = 'product_' . $product['id'] . '_' . uniqid() . '.png';
                $image_path = (new ImageController)->uploadImage($request['image2'], $name, 'images/products/');
                $product->update(['image2' => '/' . $image_path]);

                (new ImageController)->resizeImage('images/products/',$name);
            }

            return response(new ProductResource($product), 201);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all('title'),
            [
                'title' => 'required|unique:products,title,' . $product['id'],
            ],
            [
                'title.required' => 'لطفا عنوان را وارد کنید',
                'title.unique' => 'این عنوان قبلا ثبت شده است',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        }
        try {
            $product->update($request->except('image1','image2'));
            if ($request['image1']) {
                $name = 'product_' . $product['id'] . '_' . uniqid() . '.png';
                $image_path = (new ImageController)->uploadImage($request['image1'], $name, 'images/products/');
                if ($product['image1']){
                    $file_to_delete = ltrim($product['image1'], $product['image1'][0]); //remove '/' from file name start
                    $file_to_delete_thumb = ltrim(str_replace('.png','_thumb.png',$file_to_delete));
                    if (file_exists($file_to_delete)){  unlink($file_to_delete);}
                    if (file_exists($file_to_delete_thumb)){  unlink($file_to_delete_thumb);}
                }
                $product->update(['image1' => '/' . $image_path]);
                (new ImageController)->resizeImage('images/products/',$name);
            }
            if ($request['image2']) {
                $name = 'product_' . $product['id'] . '_' . uniqid() . '.png';
                $image_path = (new ImageController)->uploadImage($request['image2'], $name, 'images/products/');
                if ($product['image2']){
                    $file_to_delete = ltrim($product['image2'], $product['image2'][0]); //remove '/' from file name start
                    $file_to_delete_thumb = ltrim(str_replace('.png','_thumb.png',$file_to_delete));
                    if (file_exists($file_to_delete)){  unlink($file_to_delete);}
                    if (file_exists($file_to_delete_thumb)){  unlink($file_to_delete_thumb);}
                }
                $product->update(['image2' => '/' . $image_path]);
                (new ImageController)->resizeImage('images/products/',$name);
            }

            return response(new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }
    public function sort(Request $request, Product $product)
    {
//        $validator = Validator::make($request->all('title'),
//            [
//                'title' => 'required|unique:products,title,' . $product['id'],
//            ],
//            [
//                'title.required' => 'لطفا عنوان را وارد کنید',
//                'title.unique' => 'این عنوان قبلا ثبت شده است',
//            ]
//        );
//
//        if ($validator->fails()) {
//            return response()->json($validator->messages(), 422);
//        }
        try {
            $product->update($request->all('index'));
            return response(new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }

    public function destroy(Product $product)
    {

        try {
            if ($product['image']){
                $file_to_delete = ltrim($product['image'], $product['image'][0]); //remove '/' from file name start
                $file_to_delete_thumb = ltrim(str_replace('.png','_thumb.png',$file_to_delete));
                if (file_exists($file_to_delete)){  unlink($file_to_delete);}
                if (file_exists($file_to_delete_thumb)){  unlink($file_to_delete_thumb);}
            }
            $product->delete();
            return response('product deleted', 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }

    public function activeToggle(Product $product)
    {
        try {
            $product->update(['active' => !$product['active']]);
            return response(new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }
    public function updateOrder(Request $request, Product $product )
    {
        try {

            $product->update(['images' => substr($request['images'], 0, -1)]);
            return response(new ProductResource($product), 200);
        } catch (\Exception $exception) {
            return response($exception);
        }
    }
    public function byCatPanel($id)
    {
        try {
            $data = Product::orderBy('title')->where('product_category_id', $id)->where('active',1)->get();


            return response(["data"=>ProductResource::collection($data)], 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }
    public function byCat($id)
    {
        try {
            $data = Product::orderBy('id')->where('product_category_id', $id)->where('active',1)->get();

            foreach ($data as $item){
                $thumb2 = $item->image ? str_replace('.png','_thumb.png', $item->image) : '';
                $item->thumb = $thumb2;
            }
            return response([
                "data"=>$data,

            ], 200);
        } catch (\Exception $exception) {
            return response($exception);

        }
    }

    public function fix(Request $request)
    {
        try {
            $dir= "images/".$request['dir'];
            $dirlist = scandir($dir);
            for ($i=2; $i<count($dirlist); $i++){
                (new ImageController)->resizeImage($dir.'/',$dirlist[$i]);
            }
            echo "<pre>",print_r(scandir($dir)),"</pre>";
        }catch (\Exception $exception){
            return $exception;
        }
    }
}
