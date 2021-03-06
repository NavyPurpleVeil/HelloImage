<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use App\Model\Offset;
use App\Model\ImageExtension;
use App\Form\ImageType;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\User;
use App\Entity\Image;
use App\Model\ImageFormRequest;
use App\Entity\Rating;

use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\RatingRepository;

// TODO: Check what exactly are all the objects returned by query;

class ImageController extends AbstractController {
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
	public function authFailureResp(UserRepository $UserRep, ManagerRegistry $doctrine) :Response {
		// Handle return:
		// 4. Check if unique.
		// 4.1. False: Go to 1.
		$unique = false;
		while(!($unique)) {
			$authKey = $this->genVal();
			$unique = $UserRep->isUnique($authKey);
		}

		$cookie = new Cookie('auth', $authKey, strtotime('now + 36500 days'));
		
		$user = new User();
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
	public function image(Request $request, UserRepository $UserRep, ManagerRegistry $doctrine): Response {
		$image = new ImageFormRequest();
		$form = $this->createForm(ImageType::class, $image);
		$form->handleRequest($request);

		$cookies = $request->cookies->all();

		if(!(array_key_exists("auth", $cookies))) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$authKey = $cookies["auth"];
		$user = $UserRep->findByParameter(array('auth_key'=>$authKey));
		if($user == NULL) { // what if it doesn't return NULL?
			return $this->authFailureResp($UserRep, $doctrine);
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

		$originalFilename = pathinfo($imageFile->getClientOriginalName());
		$safeFilename = $slugger->slug($originalFilename);


		$entMan = $doctrine->getManager();
		$extension = "." . $imageFile->guessExtension();

		$imgEnt = new Image();
		$imgEnt->setUid($user->getId());
		$imgEnt->setExtension($extension);
		$entMan->persist($imgEnt);
		$entMan->flush();


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
	public function imageRemove(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, ManagerRegistry $doctrine): Response {
		$cookies = $request->cookies->all();

		if(!(array_key_exists("auth", $cookies))) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$authKey = $cookies["auth"];
		$user = $UserRep->findByParameter(array('auth_key'=>$authKey));
		if($user == NULL) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$image = $ImgRep->findByAuthKeyId($imgId, $authKey);
		if($image->count() == 0) {
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_NOT_FOUND);
			return $ret;
		}
		
		$filename = $this->getParameter('image_dir') . strval($ImgRep->getId()) . $ImgRep->getExtension();
		new Filesystem().remove($filename);
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
	public function addRating(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, RatingRepository $RateRep, ManagerRegistry $doctrine): Response {

		$cookies = $request->cookies->all();

		if(!(array_key_exists("auth", $cookies))) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$authKey = $cookies["auth"];
		$user = $UserRep->findByParameter(array('auth_key'=>$authKey));
		if($user == NULL) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$uid = $user->getId();
		$rating = $RateRep->findByUid($uid);
		// Finds ratings by user id, doesn't match imageid
		if($rating != NULL) {
			$ratEnt = new Rating();
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

		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_FORBIDDEN);
		return $ret;
	}
	#[Route('rating/{imgId}', methods: ['DELETE'])]
	public function removeRating(int $imgId, UserRepository $UserRep, ImageRepository $ImgRep, RatingRepository $RateRep, ManagerRegistry $doctrine): Response {
		$cookies = $request->cookies->all();

		if(!(array_key_exists("auth", $cookies))) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$authKey = $cookies["auth"];
		$user = $UserRep->findByParameter(array('auth_key'=>$authKey));
		if($user == NULL) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$uid = $user->getId();
		$rating = $RateRep->findByUid($uid, $imgId);
		if($rating != NULL) {
			$RateRep->removeByUid($uid, $imgId);
			$imgEnt->decrementVoteCount($imgId);
			$ret = new Response();
			$ret->setStatusCode(Response::HTTP_OK);
			return $ret;
		}

		$ret = new Response();
		$ret->setStatusCode(Response::HTTP_FORBIDDEN);
		return $ret;
	}



	#[Route('list', methods: ['GET'])]
	public function listLogin(int $id, UserRepository $UserRep, ImageRepository $ImgRep, ManagerRegistry $doctrine): Response {
		$cookies = $request->cookies->all();

		if(!(array_key_exists("auth", $cookies))) {
			return $this->authFailureResp($UserRep, $doctrine);
		}

		$authKey = $cookies["auth"];
		$user = $UserRep->findByParameter(array('auth_key'=>$authKey));
		if($user == NULL) {
			return $this->authFailureResp($UserRep, $doctrine);
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
