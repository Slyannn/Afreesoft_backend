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
        $user->setEmail($data['user']['email']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['user']['password']));
        $user->setRoles([$role]);
        $address->setStreet($data['user']['address']['street']);
        $address->setCity($data['user']['address']['city']);
        $address->setZipCode($data['user']['address']['zipCode']);
        $address->setCountry($data['user']['address']['country']);

        $existingUser= $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email is already registered'], JsonResponse::HTTP_CONFLICT);
        }

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