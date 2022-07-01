<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


use App\Model\Image;
use App\Model\ImageExtension;
use App\Form\ImageType;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\UserEntity;
use App\Entity\ImageEntity;
use App\Entity\RatingEntity;

use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\RatingRepository;
// TODO: all objects returned by Repositories are

class ImageController {
	public function genVal() : ?string {
		// 1<->3  function;
		// 1. Get random bytes. 
		// 2. Base64.
		// 3. Trim to 255 chars.
		$retVal = substr(
			base64_encode(random_bytes(255)),
			0 ,255);
		return $retVal;
	}
	public function authFailureResp(UserRepository $UserRep) :Response {
		// Handle return:
		// 4. Check if unique.
		// 4.1. False: Go to 1.
		$unique = false;
		while(!($unique)) {
			$authKey = genVal();
			$unique = $UserRep->isUnique($authKey);
		}

		$cookie = new Cookie('auth', $authKey, strtotime('now + 36500 days'));
		
		$user = new UserEntity();
		$user->setAuthKey($authKey);
		$entMan = $doctrine->getManager();
		$entMan->persist($user);
		$entMan->flush();
		
		$ret = new Response();
		$ret->headers->setCookie($cookie);
		$ret->setStatusCode(Response::HTTP_BAD_REQUEST);
		return $ret;
	}

	#[Route('image', name: "app_image_new", methods: ['POST'])] // content-type
	public function image(Request $request, SluggerInterface $slugger, UserRepository $UserRep): Response {
		// access the model
		$image = new Image();
		$form = $this->createForm(ImageType::class, $image);
		$form->handleRequest($request);

		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			$imageFile->move($this->getParameter('image_dir'), "/dev/null");
			return authFailureResp();
		}

		// check against the database
		// sendback a cookie if this one doesn't exist
		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) { // what if it doesn't return NULL?
			$imageFile->move($this->getParameter('image_dir'), "/dev/null");
			return authFailureResp();
		}





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

		// We don't need to get the file info
		$originalFilename = pathinfo($imageFile->getClientOriginalName());
		$safeFilename = $slugger->slug($originalFilename);

		// TODO: broken .uniqid() can't generate trully unique values[the function doesn't take any parameter that would allow it to]
		// Find suitable implementation;
		// Bodge 1:
		// Database id auto increments, add an entry with string .guessExtension();
		// 


		$entMan = $doctrine->getManager();
		// add a new entry
		$extension = $imageFile->guessExtension();

		$imgEnt = new ImageEntity();
		$imgEnt->setUid($user->getId());
		$imgEnt->setExtension($extension);
		$entMan->persist($imgEnt);
		$entMan->flush();

		// Encja nadal nie posiada id?

		$fileId = strval($imgEnt->getId());
		$filename = $fileId . $extension;

		try {
			$imageFile->move(
				$this->getParameter('image_dir'),
				$filename
			);
			return $this->redirect('http://localhost:8000/uploads/'+$fileId);
		} catch(FileException $e) {
				$ret = new Response();
				$ret->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR); // ERROR 500
				return $ret;
		}
	}

	#[Route('image/{imgId}', methods: ['GET'])]
	public function imageRetrive(int $imgId, ImageRepository $ImgRep): Response {
		$image = $ImgRep->findById($imgId);
		if($image == NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}
		$imgExt = new imageExtension();
		$imgExt->setExtension(strVal($image->getId) + $image->getExtension());

		// access the model
		$encoder = [new JsonEncoder()];
		$normalizer = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizer, $encoder);
		$jsonContent = $serializer->serialize($imgExt, 'json');

		$ret = new Response($jsonContent);
		$ret->headers->set('Content-Type', 'text/json');
		return $ret;
		
	}
	#[Route('image/{imgId}', methods: ['DELETE'])]
	public function imageRemove(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep): Response {
		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			return authFailureResp();
		}

		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) {
			return authFailureResp();
		}

		$image = $ImgRep->findByAuthKeyId($imgId, $authKey);
		if($image == NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}
		
		$ImgRep->removeByAuthKeyId($imgId, $authKey);
		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_OK);
		return $ret;
	}
	
	#[Route('uploads/{id}', methods: ['GET'])]
	public function uploads(int $id): Response {
		// send back twig template or something
	}

	#[Route('rating/{imgId}', methods: ['GET'])]
	public function getRatings(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep): Response {
		// Authless
		// $imageEnt = $ImgRep->getRatingCount($imgId);

	}

	#[Route('rating/{imgId}', methods: ['POST'])]
	public function addRating(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, RatingRepository $RateRep)): Response {
		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			return authFailureResp();
		}

		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) {
			return authFailureResp();
		}

	}
	
	#[Route('rating/{imgId}', methods: ['DELETE'])]
	public function removeRating(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, RatingRepository $RateRep)): Response {
		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			return authFailureResp();
		}

		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) {
			return authFailureResp();
		}
	}

	#[Route('list', methods: ['GET'])]
	public function listLogin(int $id, UserRepository $UserRep, ImageRepository $ImgRep): Response {
		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			return authFailureResp();
		}

		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) {
			return authFailureResp();
		}
	}
	
	#[Route('list/{id}', methods: ['GET'])]
	public function listUser(int $id, UserRepository $UserRep, ImageRepository $ImgRep): Response {
		// Authless
		// GetAll images made by user (will be slow, limit responce count[TODO: How to limit and offset SQL response])
		/*
		** Form: offset=<int>; 
		**
		** Use SQL Offset && Limit(Discord uses 50 by default)
		** 
		*/ 

		// Turn into json response: Array[ Int, Int ] // Int == imgID
		// Send back the json
	}


}