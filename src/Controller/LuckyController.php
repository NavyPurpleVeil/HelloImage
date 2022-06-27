<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use App\Model\Number;

class LuckyController
{
	#[Route('lucky/number', methods: ['GET'])] // content-type
	public function number(): Response
	{
		// access the model
		$number = new Number();
		$encoder = [new JsonEncoder()];
		$normalizer = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizer, $encoder);
		$jsonContent = $serializer->serialize($number, 'json');

		$response = new Response($jsonContent);
		$response->headers->set('Content-Type', 'text/json');
		return $response;
	}
}
