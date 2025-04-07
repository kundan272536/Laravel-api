<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //------------------------Listing of all products------------------------//
    public function index(Request $request){
        $user=Auth::user();
        if(!$user){
            return response()->json(['status'=>'unauthorized','message'=>'User unauthenticated']);
        }
        $userId=$user->id;
        $products = Product::where('user_id', $userId)->get();
        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Product not found',
                'data' => []
            ]);
        }
        $products->transform(function ($product) {
            if ($product->product_image) {
                $product->product_image = asset('storage/' . $product->product_image);
            }
            return $product;
        });
        return response()->json([
            'status' => 'success',
            'message' => 'Product data fetched successfully!',
            'data' => $products
        ]);
    }
    //-----------------------------Storing the products------------------------//
    public function store(Request $request){
        $user=Auth::user();
        if(!$user){
            return response()->json(['status'=>'unauthorized','message'=>'Unauthenticated']);
        }
        $userId=$user->id;
        $validator=Validator::make($request->all(),[
            'product_name'   => ['required', 'string', 'max:50'],
            'product_image'  => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp'],
            'brand'          => ['nullable', 'string', 'max:50'],
            'warranty'       => ['nullable', 'string', 'max:50'],
            'price'          => ['required', 'numeric'],
            'capacity'       => ['nullable', 'numeric'],
            'quantity'       => ['required', 'numeric'],
            'descriptions'   => ['nullable', 'string', 'max:100'],
        ],
        [
            'product_name.required'  => 'Product name is required',
            'product_name.string'    => 'Product name must be a valid string',
            'product_name.max'       => 'Product name allows a maximum of 50 characters',
            'product_image.mimes'    => 'Product image must be a jpeg, jpg, png, or webp file',
            'brand.string'           => 'Brand name must be a valid string',
            'brand.max'              => 'Brand name allows a maximum of 50 characters',
            'warranty.string'        => 'Warranty must be a valid string',
            'warranty.max'           => 'Warranty allows a maximum of 50 characters',
            'price.required'         => 'Price field is required',
            'price.numeric'          => 'Price must be a number',
            'capacity.numeric'       => 'Capacity must be a number',
            'quantity.required'      => 'Quantity field is required',
            'quantity.numeric'       => 'Quantity must be numeric',
            'descriptions.string'    => 'Description must be a valid string',
            'descriptions.max'       => 'Description allows a maximum of 100 characters',
        ]);
        if($validator->fails()){
            return response()->json(
                [
                    'status'=>'error',
                    'errors'=>$validator->errors()
                ]
            ,422);
        }
        $validatedData=$validator->validated();
        if($request->hasFile('product_image')){
            $file=$request->file('product_image');
            $filename=time().'.'.$file->getClientOriginalExtension();
            $filePath=$file->storeAs('product_image',$filename,'public');
            $validatedData['product_image']=$filePath;
        }
        try {
            $validatedData['user_id']=$userId;
            $product=Product::create($validatedData);
            if(!$product){
             return response()->json(['status'=>'failed','message'=>'Failed to save data']);
            }
            if ($product->product_image) {
                $product->product_image = asset('storage/' . $product->product_image);
            }
            return response()->json(['status'=>'success','message'=>'Product save successfully!','data'=>$product]);
        } catch (\Exception $e) {
            return response()->json(['status'=>'errors',  'message' => 'An error occurred: ' . $e->getMessage(),],500);
        }  
    }

    //--------------------------------------Updating the product-----------------------------//
    public function update(Request $request){
        $user=Auth::user();
        if(!$user){
            return response()->json(['status'=>'Unautherized','message'=>'User unauthenticated']);
        }
        $userId=$user->id;
        $productId=$request->id;
        if(!$productId){
            return response()->json(['status'=>'failed','message'=>'Product id required']);
        }
        $validator=Validator::make($request->all(),[
            'product_name'   => ['required', 'string', 'max:50'],
            'product_image'  => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp'],
            'brand'          => ['nullable', 'string', 'max:50'],
            'warranty'       => ['nullable', 'string', 'max:50'],
            'price'          => ['required', 'numeric'],
            'capacity'       => ['nullable', 'numeric'],
            'quantity'       => ['required', 'numeric'],
            'descriptions'   => ['nullable', 'string', 'max:100'],
        ],
        [
            'product_name.required'  => 'Product name is required',
            'product_name.string'    => 'Product name must be a valid string',
            'product_name.max'       => 'Product name allows a maximum of 50 characters',
            'product_image.mimes'    => 'Product image must be a jpeg, jpg, png, or webp file',
            'brand.string'           => 'Brand name must be a valid string',
            'brand.max'              => 'Brand name allows a maximum of 50 characters',
            'warranty.string'        => 'Warranty must be a valid string',
            'warranty.max'           => 'Warranty allows a maximum of 50 characters',
            'price.required'         => 'Price field is required',
            'price.numeric'          => 'Price must be a number',
            'capacity.numeric'       => 'Capacity must be a number',
            'quantity.required'      => 'Quantity field is required',
            'quantity.numeric'       => 'Quantity must be numeric',
            'descriptions.string'    => 'Description must be a valid string',
            'descriptions.max'       => 'Description allows a maximum of 100 characters',
        ]);
        if($validator->fails()){
            return response()->json(
                [
                    'status'=>'error',
                    'errors'=>$validator->errors()
                ]
            ,422);
        }
        $validatedData=$validator->validated();
        $productDetails=Product::where('id',$productId)->where('user_id',$userId)->first();
        if(!$productDetails){
            return response()->json(['status'=>'failed','message'=>'Product not found!']);
        }
        if ($request->hasFile('product_image')) {
            if ($productDetails->product_image && Storage::disk('public')->exists($productDetails->product_image)) {
                Storage::disk('public')->delete($productDetails->product_image);
            }
            $file = $request->file('product_image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('product_image', $filename, 'public');
            $validatedData['product_image'] = $filePath;
        }
        try {
            $productDetails->update($validatedData);
            if ($productDetails->product_image) {
                $productDetails->product_image = asset('storage/' . $productDetails->product_image);
            }
            return response()->json(['status'=>'success','message'=>'Product details update suceesfully!','data'=>$productDetails]);
            
        } catch (\Exception $e) {
            return response()->json(['status'=>'errors',  'message' => 'An error occurred: ' . $e->getMessage(),],500);
        }
    }
    //---------------------------------------Deleting the products---------------------------//
    public function destroy(Request $request){
        $user=Auth::user();
        if(!$user){
            return response()->json(['status'=>'unauthentiated','message'=>'User unauthenticated']);
        }
        $userId=$user->id;
        $productId=$request->id;
        if(!$productId){
         return response()->json(['status'=>'failed','message'=>'Product id required']);
        }
        $productDetails=Product::where('id',$productId)->where('user_id',$userId)->first();
        if(!$productDetails){
            return response()->json(['status'=>'failed','message'=>'Product not found!']);
        }
        if ($productDetails->product_image && Storage::disk('public')->exists($productDetails->product_image)) {
            Storage::disk('public')->delete($productDetails->product_image);
        }
        $productDetails->delete();
        return response()->json(['status'=>'success','message'=>'Product deleted successfully!']);
    }
}
