<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Upload
use App\Model\Image;
use App\Form\ImageType;
// Database entity
//use App\Entity\Image;

class ImageController {
	#[Route('image', name: "app_image_new", methods: ['POST'])] // content-type
	public function image(Request $request, SluggerInterface $slugger): Response
	{
		// access the model
		$image = new Image();
		$form = $this->createForm(ImageType::class, $image);//createForm?
		$form->handleRequest($request);

		$cookies = $request->headers->getCookies();

		if(!($cookies->has('uid'))) {
			// Generate uid cookie
			$imageFile->move($this->getParameter('image_dir'), "/dev/null");
			$cookie = new Cookie('uid', '', strtotime('now + 36500 days'));

			$ret = new Response();
			$ret->headers->setCookie($cookie);
			$ret->setStatusCode(Response::HTTP_BAD_REQUEST);
			return $ret;
		}

		$uid = $cookies->has('uid');
		// check against the database
		// sendback a cookie if this one doesn't exist


		if(!($form->isSubmitted() && $form->isValid())) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_BAD_REQUEST);
			return $ret;
		}

		$imageFile = $form->get('image')->getData();
		if(!($imageFile)) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_BAD_REQUEST);
			return $ret;
		}


		$originalFilename = pathinfo($imageFile->getClientOriginalName());
		$safeFilename = $slugger->slug($originalFilename);

		// TODO: broken .uniqid() can't generate trully unique values[the function doesn't take any parameter that would allow it to]
		// Find suitable implementation;
		$newFilename = ''.uniqid().'.'.$imageFile->guessExtension();
		// Bodge 1:
		// Database id auto increments, add an entry with string .guessExtension();
		// 
		//$id = ; // database access

		try {
			$imageFile->move(
				$this->getParameter('image_dir'),
				$newFilename
			);
			return $this->redirect('http://localhost:8000/image/'+strval($id));
		} catch(FileException $e) {
				$ret = new Response();
				$ret->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR); // ERROR 500
				return $ret;
		}
	}

	#[Route('image/{id}', methods: ['GET'])]
	public function imageRetrive(int $id): Response {

	}
	#[Route('image/{id}', methods: ['DELETE'])]
	public function imageRemove(int $id): Response {

	}

}