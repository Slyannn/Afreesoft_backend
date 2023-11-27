<?php

namespace App\Controller\api;

use App\Entity\Organism;
use App\Entity\Need;
use App\Form\OrganismType;
use App\Repository\NeedRepository;
use App\Repository\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/auth/organism')]
class OrganismAuthController extends AbstractController
{

    public function __construct(){}

    #[Route('/signup', name: 'app_organism_signup', methods: ['POST'])]
    public function signup(
        Request $request,
        NeedRepository $needRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager): JsonResponse
    {
        //Extract data from the request
        $data = json_decode($request->getContent(), true);

        //New organism
        $organism = new Organism();

        //Set organism data
        $organism->setLogo($data['logo']);
        $organism->setName($data['name']);
        $organism->setEmail($data['email']);
        $organism->setPassword($userPasswordHasher->hashPassword($organism, $data['password']));
        //$organism->setPassword($data['password']);
        $organism->setDescription($data['description']);
        $organism->setCertificat($data['certificat']);

        $existingOrganism = $entityManager->getRepository(Organism::class)->findOneBy(['email' => $organism->getEmail()]);
        if ($existingOrganism) {
            return new JsonResponse(['message' => 'Email is already registered'], JsonResponse::HTTP_CONFLICT);
        }

        //dd($data['services']);

        // Ajouter chaque service un par un
        foreach ($data['services'] as $serviceData) {
            /** @var Need $need */
            $need = $needRepository->find($serviceData['id']);
            $organism->addService($need);
        }
        
        //Save organism
        $entityManager->persist($organism);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Organism registered successfully'], JsonResponse::HTTP_CREATED);
    }
    #[Route('/login', name: 'app_organism-login', methods: ['OPTIONS', 'GET', 'POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        //Extract data from the request
        $data = json_decode($request->getContent(), true);
        //New organism
        $organism = new Organism();
        //Set organism data
        $organism->setEmail($data['email']);
        $organism->setPassword($userPasswordHasher->hashPassword($organism, $data['password']));
        $existingorganism = $entityManager->getRepository(Organism::class)->findOneBy(['email' => $organism->getEmail()]);
        if (!$existingorganism) {
            return new JsonResponse(['message' => 'Email is not registered'], JsonResponse::HTTP_CONFLICT);
        }
        if ($userPasswordHasher->isPasswordValid($existingorganism, $data['password'])) {
            //serialize and give the organism data

            $responseData = [
                'id' => $existingorganism->getId(),
                'email' => $existingorganism->getEmail(),
                'logo' => $existingorganism->getLogo(),
                'name' => $existingorganism->getName(),
                'password' => $existingorganism->getPassword(),
                'description' => $existingorganism->getDescription(),
                'certificat' => $existingorganism->getCertificat(),
                /*'address' => array(
                    'street' => $existingorganism->getAddress()->getStreet(),
                    'city' => $existingorganism->getAddress()->getCity(),
                    'zipCode' => $existingorganism->getAddress()->getZipCode(),
                    'country' => $existingorganism->getAddress()->getCountry(),
                ),
                //afficher chaque besoin
                'needs' => array_map(function ($need) {
                    return [
                        'id' => $need->getId(),
                        'name' => $need->getName(),
                    ];
                }, $existingorganism->getNeeds()->toArray()),*/
            ];


            //return message and user data json_code
            return new JsonResponse(['message' => 'Login successful', 'organism' => $responseData], JsonResponse::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Invalid credentials'], JsonResponse::HTTP_CONFLICT);
    }

    #[Route('/logout', name: 'app_organism-logout', methods: ['GET'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(['message' => 'Logout successfully'], JsonResponse::HTTP_OK);
    }

}
