<?php namespace Image;

use Illuminate\Support\ServiceProvider;
use Image\Repositories\Image\ImageEloquentRepository;
use Image\Repositories\Image\ImageRepositoryInterface;
use Image\Repositories\User\UserEloquentRepository;
use Image\Repositories\User\UserRepositoryInterface;


class RepositoryServiceProvider extends ServiceProvider {

	public function register () {

		$bindings = [
			[ UserRepositoryInterface::class, UserEloquentRepository::class ],
			[ ImageRepositoryInterface::class, ImageEloquentRepository::class ],

		];
		$this->bindInterfacesWithTheirImplementations( $bindings );
	}

	public function bindInterfacesWithTheirImplementations ( $bindings ) {
		foreach ( $bindings as $binding ) {

		    $this->app->bind( $binding[ 0 ], $binding[ 1 ] );
		}

	}
}
