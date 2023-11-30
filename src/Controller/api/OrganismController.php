<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Need;
use App\Entity\Organism;
use App\Entity\User;
use App\Form\OrganismType;
use App\Repository\NeedRepository;
use App\Repository\OrganismRepository;
use App\Repository\UserRepository;
use App\Service\SignupUser;
use App\Service\UploadFile;
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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/organism')]
class OrganismController extends AbstractController
{
    private Serializer $serializer;
    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/signup', name: 'app_organism_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        UploadFile $uploadFile,
        SluggerInterface $slugger
    ): JsonResponse
    {
        $data = $request->request->all();

        //New student
        $organism = new Organism();

        $form = $this->createForm(OrganismType::class, $organism);
        $form->submit($data);

        $logoFile = $request->files->get('logo');
        if ($logoFile) {
            $logoFileName = $uploadFile->uploadedFilename($logoFile, $slugger);
            $organism->setLogo($logoFileName);
        }

        //certificate File
        $certificateFile = $request->files->get('certificate');
        if ($certificateFile) {
            $certificateFileName = $uploadFile->uploadedFilename($certificateFile, $slugger);
            $organism->setCertificate($certificateFileName);
        }
        //Set student data
        $organism->setName($data['name']);
        $organism->setDescription($data['description']);
        $organism->setName($data['name']);


        //add all services
        foreach ($data['services'] as $service) {
            //find need with id
            $need = $entityManager->getRepository(Need::class)->find($service['id']);
            if($need !== null){
                //add need to organism
                $need->addOrganism($organism);
                $organism->addService($need);
            }else {
                return new JsonResponse(['message' => 'Need not found'], Response::HTTP_NOT_FOUND);
            }
        }
        //check when need already exist in $organism
        $existingOrganism = $entityManager->getRepository(Organism::class)->findOneBy(['name' => $organism->getName()]);
        if ($existingOrganism) {
            return new JsonResponse(['message' => 'Organism already exist'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $address = new Address();

        return (new SignupUser())->signupUser($data, $organism, 'ROLE_ORGANISM', $user, $address, $entityManager, $userPasswordHasher);
    }

    //get Organism by email
    #[Route('/{email}', name: 'app_organism_get', requirements: ['email' => '\S+@\S+\.\S+'], methods: ['GET'])]
    public function getOrganism(string $email, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Organism not found'], Response::HTTP_NOT_FOUND);
        }

        //Serialize $user
        $jsonContent = $this->serializer->serialize($user, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['student', 'organismAdmins', 'students', 'organisms', 'userIdentifier', 'user']
        ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }



    //Get All Organisms
    #[Route('/all', name:'app_organism_get_all', methods:['GET'])]
    public function getAllOrganisms(OrganismRepository $organismRepository):JsonResponse
    {
        $organisms =  $organismRepository->findAll();
        $jsonContent = $this->serializer->serialize($organisms, 'json',
        [AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
            return $object->getId();
        },
        AbstractNormalizer::IGNORED_ATTRIBUTES => ['certificate','password', 'roles', 'organism', 'organisms', 'organismAdmins', 'student', 'students']
        ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }
}
