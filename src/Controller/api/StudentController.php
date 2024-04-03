<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Message;
use App\Entity\Need;
use App\Entity\OrganismAdmin;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\NeedRepository;
use App\Repository\OrganismAdminRepository;
use App\Repository\StudentRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


#[Route('/api/student')]
class StudentController extends AbstractController
{
    private Serializer $serializer;

    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }


    #[Route('/signup', name: 'app_student_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        SendMailService             $sendMailService,
        JwtService                  $jwt
    ): JsonResponse
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

        //genereted the jwt
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload =[
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header,$payload, $this->getParameter('app.jwtsecret'));

        //send email
        $sendMailService->send(
            'no-reply@educare.fr',
            $user->getEmail(),
            'Activation de votre compte sur le site EduCare',
            'register',
            compact('user', 'token')
        );

        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }

    //Get All Organisms
    #[Route('/all', name: 'app_student_get_all', methods: ['GET'])]
    public function getAllOrganisms(StudentRepository $studentRepository): JsonResponse
    {
        $students = $studentRepository->findAll();
        $jsonContent = $this->serializer->serialize($students, 'json',
            [AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['certificate', 'password', 'roles', 'organism', 'organisms', 'organismAdmins', 'organismAdmin']
            ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    //update student
    #[Route('/update/{id}', name: 'app_student_update', methods: ['PUT'])]
    public function update(
        Student $student,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        SendMailService             $sendMailService,
        JwtService                  $jwt
    ): JsonResponse {
        // Extract data from the request
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        // Update student data
        $student->setFirstname($data['firstname']);
        $student->setLastname($data['lastname']);
        $student->setUniversity($data['university']);

        // Update address if provided
        if (isset($data['address'])) {
            $address = $student->getAddress();
            $address?->setStreet($data['address']['street'] );
            $address?->setCity($data['address']['city'] );
            $address?->setZipCode($data['address']['zipCode'] );
            $address?->setCountry($data['address']['country'] );
        }

        // Update user password if provided
        $user = $student->getUser();
        if (isset($data['user'])) {
            $user?->setPassword($userPasswordHasher->hashPassword($user, $data['user']['password']));
            $user?->setEmail($data['user']['email']);
        }

        // Flush changes to database
        $entityManager->flush();

        $jsonContent = $this->serializer->serialize($user, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['organism', 'createAt', 'organismAdmins', 'students', 'organisms', 'user']
        ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
        //return new JsonResponse(['message' => 'Student updated'], Response::HTTP_OK);
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
