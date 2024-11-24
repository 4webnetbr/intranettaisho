<?php namespace App\Filters;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Firebase\JWT\JWT;

class AuthFilter implements FilterInterface
{
	use ResponseTrait;

    public function before(RequestInterface $request, $arguments = null)
	{
		$key        = Services::getSecretKey();
		$authHeader = $request->getServer('HTTP_AUTHORIZATION');
		$arr        = explode(' ', $authHeader);
		$token      = $arr[1];

		try
		{
			JWT::decode($token, $key);
		}
		catch (\Exception $e)
		{
			return Services::response()
				->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
		}
	}

	//--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// Do something here
	}
}