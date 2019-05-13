<?php

interface IHandler {
	public function http_get($request, $input);
	public function http_post($request, $input);
	public function http_delete($request, $input);
	public function http_put($request, $input);
}
