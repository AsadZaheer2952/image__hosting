<?php namespace Image\Utils;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Image\Enums\Strategy;

class Converter {

	public static function date ( $date, $format ) {
		Log::info("convert date format Date: $date format: $format");
		$carbonDate = Carbon::parse($date);
		return $carbonDate->format('Y-m-d');
	}
}
