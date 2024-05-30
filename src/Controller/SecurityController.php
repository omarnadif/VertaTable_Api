<?php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Allergene;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api')]
class SecurityController extends AbstractController
{
    private $em;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntrepriseRepository $entrepriseRepository): JsonResponse
    {
    $data = json_decode($request->getContent(), true);
    $entreprise = $entrepriseRepository->findOneBy(['codeEntreprise' => $data['codeEntreprise']]);

    if (!$entreprise) {
        return new JsonResponse(['error' => 'Entreprise non trouvée'], Response::HTTP_NOT_FOUND);
    }

    $user = new Utilisateur();
    $user->setPrenom($data['prenom']);
    $user->setNom($data['nom']);
    $user->setEmail($data['email']);
    $user->setDateDeNaissance(new \DateTime($data['date_de_naissance']));
    $user->setTelephone($data['telephone']);
    $user->setEntreprise($entreprise);
    $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

    $this->em->persist($user);
    $this->em->flush();

    return new JsonResponse(['message' => 'User registration data stored. Proceed to select allergies.', 'userId' => $user->getId()]);
    }

    

    #[Route('/finalize-registration', name: 'app_finalize_registration', methods: ['POST'])]
    public function finalizeRegistration(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'];
        $allergies = $data['allergies'];

        $user = $this->em->getRepository(Utilisateur::class)->find($userId);

        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        foreach ($allergies as $allergyId) {
            $allergy = $this->em->getRepository(Allergene::class)->find($allergyId);
            if ($allergy) {
                $user->addAllergene($allergy);
            }
        }

        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse(['message' => 'User registered successfully with allergies!', 'user' => $user->getId()], Response::HTTP_CREATED);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(['message' => 'Logged out successfully']);
    }
}
