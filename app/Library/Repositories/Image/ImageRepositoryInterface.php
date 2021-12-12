<?php namespace Image\Repositories\Image;


use Image\Abstracts\RepositoryInterface;

interface ImageRepositoryInterface extends RepositoryInterface
{
    public function create($data);

    public function update($data,$id);

    public function search($data);
}
