<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


use App\Model\Image;
use App\Model\Offset;
use App\Model\ImageExtension;
use App\Form\ImageType;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\UserEntity;
use App\Entity\ImageEntity;
use App\Entity\RatingEntity;

use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\RatingRepository;

// TODO: Check what exactly are all the objects returned by query;

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
		// access the imageRequest model
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
		$extension = "." . $imageFile->guessExtension();

		$imgEnt = new ImageEntity();
		$imgEnt->setUid($user->getId());
		$imgEnt->setExtension($extension);
		$entMan->persist($imgEnt);
		$entMan->flush();

		// Encja nadal posiada id? Posiada, ponieważ obiekty idą po referencji.
		// W kodzie źródłowym symfony widać, że trackowany jest oryginalny obiekt, i updajtowane id/key obiektu encji.

		$fileId = strval($imgEnt->getId());
		$filename = $fileId . $extension;

		try {
			$imageFile->move(
				$this->getParameter('image_dir'),
				$filename
			);
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_CREATED);

			return $this->redirect('http://localhost:8000/uploads/'+$fileId);
		} catch(FileException $e) {
				$ret = new Response();
				$ret->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
				return $ret;
		}
	}



	#[Route('image/{imgId}', methods: ['GET'])]
	public function imageRetrive(int $imgId, ImageRepository $ImgRep): Response {
		$image = $ImgRep->findById($imgId);
		if($image == NULL) { // TODO: Verify the object returned by findById
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
		if($image->count() == 0) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}
		
		$filename = $this->getParameter('image_dir') . strval($ImgRep->getId()) . $ImgRep->getExtension();
		new Filesystem->remove($filename);
		$ImgRep->removeByAuthKeyId($imgId, $authKey);

		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_OK);
		return $ret;
	}
	


	#[Route('uploads/{id}', methods: ['GET'])]
	public function uploads(int $id): Response {
		// send back an html file;
		// javascript will load metadata from APIs;
		// Reading the URL should be possible;

	}



	#[Route('rating/{imgId}', methods: ['GET'])]
	public function getRatings(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep): Response {
		// Authless
		$imageEnt = $ImgRep->getRatingCount($imgId);

		$encoder = [new JsonEncoder()];
		$normalizer = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizer, $encoder);
		$jsonContent = $serializer->serialize($imageEnt, 'json');

		if($jsonContent == NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}

		$ret = new Response($jsonContent);
		$ret->headers->set('Content-Type', 'text/json');
		return $ret;

	}
	#[Route('rating/{imgId}', methods: ['POST'])]
	public function addRating(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, RatingRepository $RateRep)): Response {

		$cookies = $request->headers->getCookies();

		if(!($cookies->has('auth'))) {
			return authFailureResp();
		}

		$authKey = $cookies->get('auth');
		$user = $UserRep->findBy(array(),array('$authKey'=>$authKey));
		if($user == NULL) {filesystem
			return authFailureResp();
		}

		$uid = $user->getId();
		$rating = $RateRep->findByUid($uid);
		if($rating != NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_FORBIDDEN);
			return $ret;
		}
		// add new rating entity;
		$ratEnt = new RatingEntity();
		$ratEnt->setImgId($imgId);
		$ratEnt->setUid($uid);
		$entMan = $doctrine->getManager();
		$entMan->persist($ratEnt);
		$entMan->flush();

		$imgEnt->incrementVoteCount($imgId);

		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_OK);
		return $ret;
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

		$uid = $user->getId();
		$rating = $RateRep->findByUid($uid);
		if($rating != NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_FORBIDDEN);
			return $ret;
		}
		// add new rating entity;
		$ratEnt = new RatingEntity();
		$ratEnt->setImgId($imgId);
		$ratEnt->setUid($uid);
		$entMan = $doctrine->getManager();
		$entMan->persist($ratEnt);
		$entMan->flush();

		$imgEnt->decrementVoteCount($imgId);

		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_OK);
		return $ret;
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
		$offset = new Offset;
		$form = $this->createForm(OffsetType::class, $offset);
		$form->handleRequest($request);

		if(!($form->isSubmitted() && $form->isValid())) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_BAD_REQUEST);
			return $ret;
		}

		// Returns ImageEntities
		$imageEnt = $ImgRep->getAllRatingsCount($imgId, $offset->getOffset());

		$encoder = [new JsonEncoder()];
		$normalizer = [new ObjectNormalizer()];
		$serializer = new Serializer($normalizer, $encoder);
		$jsonContent = $serializer->serialize($imageEnt, 'json');

		if($jsonContent == NULL) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}

		$ret = new Response($jsonContent);
		$ret->headers->set('Content-Type', 'text/json');
		return $ret;
	}

}