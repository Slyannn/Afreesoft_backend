<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Need;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\NeedRepository;
use App\Repository\OrganismAdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api/student')]
class StudentController extends AbstractController
{

    /**
     * @throws \JsonException
     */
    #[Route('/signup', name: 'app_student_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager): JsonResponse
    {
        //Extract data from the request
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        //New student
        $student = new Student();
        //Set student data
        $student->setFirstname($data['firstname']);
        $student->setLastname($data['lastname']);
        $student->setUniversity($data['university']);

        $address = new Address();
        $address->setStreet($data['address']['street']);
        $address->setCity($data['address']['city']);
        $address->setZipCode($data['address']['zipCode']);
        $address->setCountry($data['address']['country']);

        $entityManager->persist($address);
        $student->setAddress($address);

        $user = new User();
        $user->setEmail($data['user']['email']);
        $existingUser= $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email is already registered'], Response::HTTP_CONFLICT);
        }
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['user']['password']));
        $user->setRoles(['ROLE_STUDENT']);
        $entityManager->persist($user);

        $student->setUser($user);
        $entityManager->persist($student);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }


    /**
     * @throws \JsonException
     */
    #[Route('/{id}/need', name: 'app_student_need_add', methods: ['GET', 'POST'])]
    public function addNeed(
        Request $request,
        Student $student,
        EntityManagerInterface $entityManager,
        NeedRepository $needRepository
    ): JsonResponse
    {
        //Student with $id add some need
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var Need $need */
        $need = $needRepository->find($data['need']['id']);

        $student->addNeed($need);
        $need->addStudent($student);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Need added'], Response::HTTP_CREATED);
    }

    //remove need
    #[Route('/{id}/need/{need}', name: 'app_student_need_remove', methods: ['DELETE'])]
    public function removeNeed(
        Student $student,
        Need $need,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $student->removeNeed($need);
        $need->removeStudent($student);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Need removed'], Response::HTTP_NO_CONTENT);
    }

    //shows all organism by needs
    #[Route('/{id}/organisms', name: 'app_student_show_organism', methods: ['GET'])]
    public function showOrganismByNeed(
        Student $student,
        OrganismAdminRepository $organismAdminRepository
    ): JsonResponse
    {
        $organismList = [];

       foreach ($student->getNeeds() as $need) {
              $results = $organismAdminRepository->createQueryBuilder('oa')
                ->where(':need MEMBER OF oa.services')
                ->setParameter('need', $need)
                ->getQuery()
                ->getResult();

           foreach ($results as $result) {
               $organismData = [
                   'id' => $result->getId(),
                   'logo' => $result->getLogo(),
                   'name' => $result->getName(),
                   'organismEmail' => $result->getOrganismEmail(),
                   'phone' => $result->getPhone(),
                   'description' => $result->getDescription(),
                     'address' => [
                          'street' => $result->getAddress()->getStreet(),
                          'city' => $result->getAddress()->getCity(),
                          'zipCode' => $result->getAddress()->getZipCode(),
                          'country' => $result->getAddress()->getCountry()
                     ],
                   //services list
                   'services' => array_map(static function ($service) {
                       return [
                           'id' => $service->getId(),
                           'name' => $service->getName(),
                       ];
                   }, $result->getServices()->toArray()),

                   // Add other fields as needed
               ];
               $organismList[] = $organismData;
           }

       }

        return new JsonResponse($organismList, Response::HTTP_OK);
    }

}
