<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SignupUser extends AbstractController
{

    public function signupUser(mixed $data, mixed $entity , mixed $role , mixed $user, mixed $address, mixed $entityManager, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {


        $entityManager->persist($address);
        $entityManager->flush();

        $user->setAddress($address);
        $entityManager->persist($user);

        $entity->setUser($user);
        //scheck if entity is student or organism
        if ($role === 'ROLE_STUDENT') {
            $user->setStudent($entity);
        } else {
            $user->setOrganism($entity);
        }

        $entityManager->persist($entity);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User created'], JsonResponse::HTTP_CREATED);
    }

}