<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image\Enums\ResponseMessage;
use Image\Repositories\Image\ImageRepositoryInterface;
use Image\Resources\ImageResource;
use Image\Traits\ApiResponseTrait;
use Image\ValidationRequests\ImageRequest;
use Image\ValidationRequests\ImageSearchRequest;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    use ApiResponseTrait;
    protected $repo;
    public function __construct()
    {
        $this->repo = app(ImageRepositoryInterface::class);
    }

    public function index(){
        $images = $this->repo->all();
        return $this->successResponse(ImageResource::collection($images) , ResponseMessage::FOUND , Response::HTTP_OK);
    }

    public function store(ImageRequest $request){
        $userId = auth()->user()->id;
        $image = $this->repo->create($request->prepareRequest($userId));
        if($image){
            return $this->successResponse(new ImageResource($image),ResponseMessage::CREATE,Response::HTTP_CREATED);
        }
        return $this->failureResponse(ResponseMessage::MESSAGE_500);
    }

    public function update(ImageRequest $request){
        $userId = auth()->user()->id;
        $image = $this->repo->update($request->prepareRequest($userId));
        if($image){
            return $this->successResponse(new ImageResource($image),ResponseMessage::UPDATED,Response::HTTP_OK);
        }
        return $this->failureResponse(ResponseMessage::MESSAGE_500);
    }

    public function show($id){
        $image = $this->repo->getById($id);
        return $this->successResponse(new ImageResource($image),ResponseMessage::OK,Response::HTTP_OK);
    }

    public function destroy($id){
        $this->repo->delete($id);
        return $this->successResponse([],ResponseMessage::DELETED,Response::HTTP_OK);
    }

    public function updateStatus(Request $request,$id){
        $request->validate([
            'status' => 'required|int',
        ]);
        $data = [
            'status' => $request->status,
        ];
        $update = $this->repo->update($data,$id);
        if($update){
            return $this->successResponse(new ImageResource($update),ResponseMessage::UPDATED,Response::HTTP_OK);
        }
        return $this->failureResponse(ResponseMessage::MESSAGE_500);
    }

    public function search(ImageSearchRequest $request){
        $userId = auth()->user()->id;
        $data = $request->prepareRequest($userId);
        $images = $this->repo->search($data);
        if(count($images)>0){
            return $this->successResponse(ImageResource::collection($images),ResponseMessage::FOUND , Response::HTTP_OK);
        }
        return $this->failureResponse("No record found");

    }
}
